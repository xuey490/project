<?php
declare(strict_types=1);

/**
 * ============================================================
 * Workerman + Eloquent + SchemaWarmup 启动入口
 * ============================================================
 * 设计目标：
 * 1. 正确初始化 Eloquent ConnectionResolver
 * 2. 支持 BaseModel 动态别名
 * 3. 启动期自动扫描并预热 Schema
 * 4. 冻结 SchemaRegistry，避免运行期 DB 反射
 * 5. Worker 进程安全复用
 * 6. 新增：内存阈值检测&自动重启、文件变化检测&自动重启、定时内存输出
 * ============================================================
 */

use Workerman\Worker;
use Illuminate\Database\Capsule\Manager as Capsule;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;
use Workerman\Protocols\Http\Response as WorkermanResponse;
use Workerman\Timer;
use Workerman\Protocols\Http;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Framework\Core\Framework;
use Framework\Schema\SchemaWarmup;
use Framework\Schema\SchemaRegistry;
use Framework\Utils\BaseModel;

if (php_sapi_name() !== 'cli') {
    return;
}

define('WORKERMAN_ENV' , true);
define('BASE_PATH', __DIR__);
define('APP_ROOT', __DIR__);

require __DIR__ . '/vendor/autoload.php';

// -------------------------- 新增配置项 --------------------------
// 内存阈值配置（单位：MB）
define('MEMORY_LIMIT_MB', 512); // 超过此值自动重启
// 监控的文件目录（可根据需要调整）
define('MONITOR_DIRS', implode(PATH_SEPARATOR, [
    __DIR__ . '/app',
    __DIR__ . '/config',
    __DIR__ . '/Framework',
]));
// 内存输出间隔（单位：秒）
define('MEMORY_LOG_INTERVAL', 20);
// 文件检测间隔（单位：秒）
define('FILE_MONITOR_INTERVAL', 2);

// 存储文件最后修改时间的数组
$fileLastModify = [];
// 标记是否需要重启
$needRestart = false;

// ----------------------------------------------------------------------
// Symfony Request / Response 转换
function convert_to_workerman_response(SymfonyResponse $res): WorkermanResponse {
    $headers = [];

    foreach ($res->headers->allPreserveCase() as $name => $values) {
        if (strtolower($name) === 'set-cookie') {
            $headers[$name] = $values;
        } else {
            $headers[$name] = is_array($values) ? implode(', ', $values) : $values;
        }
    }

    $content = $res->getContent();
    if (!isset($headers['Content-Length']) && $content !== '') {
        $headers['Content-Length'] = strlen($content);
    }

    return new WorkermanResponse($res->getStatusCode(), $headers, $content);
}

function convert_to_symfony_request(WorkermanRequest $request): SymfonyRequest
{
    $method = strtoupper($request->method());
    $uri = $request->uri();
    $rawBody = $request->rawBody();
    $remoteIp = $request->connection?->getRemoteIp() ?? '127.0.0.1';
    $remotePort = $request->connection?->getRemotePort() ?? 0;

    $uriParts = parse_url($uri);
    $pathInfo = $uriParts['path'] ?? '/';
    $queryString = $uriParts['query'] ?? '';

    $get = $request->get() ?? [];
    if (!empty($queryString)) {
        parse_str($queryString, $queryParams);
        $get = array_merge($queryParams, $get);
    }

    $post = $request->post() ?? [];
    $cookies = $request->cookie() ?? [];
    $files = $request->file() ?? [];
    $headers = $request->header() ?? [];

    $parameters = array_merge($get, $post);

    $server = [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI' => $uri,
        'PATH_INFO' => $pathInfo,
        'QUERY_STRING' => $queryString,
        'REMOTE_ADDR' => $remoteIp,
        'REMOTE_PORT' => $remotePort,
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'HTTP_HOST' => $headers['host'] ?? 'localhost',
        'CONTENT_LENGTH' => $headers['content-length'] ?? strlen($rawBody),
        'CONTENT_TYPE' => $headers['content-type'] ?? '',
        'PHP_SELF' => $pathInfo,
        'SCRIPT_NAME' => $pathInfo,
        'SCRIPT_FILENAME' => '',
    ];

    foreach ($headers as $name => $value) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        $server[$key] = is_array($value) ? implode(', ', $value) : $value;
    }

    if (!isset($server['HTTP_X_FORWARDED_FOR'])) {
        $server['HTTP_X_FORWARDED_FOR'] = $remoteIp;
    }

    if (in_array($method, ['PUT', 'DELETE', 'PATCH']) && empty($post) && !empty($rawBody)) {
        parse_str($rawBody, $parsedPost);
        $post = array_merge($post, $parsedPost);
    }

    $symfonyRequest = new SymfonyRequest(
        $get,
        $post,
        [],
        $cookies,
        $files,
        $server,
        $rawBody
    );
    #$session = app('session');
    #$symfonyRequest->setSession($session);

    return $symfonyRequest;
}

// -------------------------- 新增工具函数 --------------------------
/**
 * 获取当前进程内存占用（MB）
 */
function getMemoryUsage(): float
{
    // memory_get_usage(true) 获取实际分配的内存（包含碎片），更贴近系统实际占用
    return round(memory_get_usage(true) / 1024 / 1024, 2);
}

/**
 * 扫描指定目录的文件最后修改时间
 */
function scanFilesLastModify(array $dirs): array
{
    $fileTimes = [];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'ini', 'yml', 'yaml'])) {
                $fileTimes[$file->getRealPath()] = $file->getMTime();
            }
        }
    }
    return $fileTimes;
}



/**
 * 跨平台安全重启
 */
function restartWorkerman(Worker $worker): void
{
    global $needRestart;
    if ($needRestart) {
        return;
    }
    $needRestart = true;

    // === 1. Windows 环境处理 ===
    if (DIRECTORY_SEPARATOR === '\\') {
        echo "[System] Windows下检测到变更/内存溢出，正在停止服务(等待bat重启)...\n";
        // Windows下直接停止，依靠外部 .bat 文件的无限循环来重启
        Worker::stopAll();
        return;
    }

    // === 2. Linux 环境处理 ===
    // Linux 下 Workerman 有 Master 进程守护，不需要外部脚本
    
    // 如果是因为"内存溢出"，只需要杀掉当前这一个进程，让 Master 重新拉起一个新的即可
    // 这样其他正常的进程不会受影响，服务更平滑
    if (getMemoryUsage() > MEMORY_LIMIT_MB) {
        echo "[System] 内存溢出，重置当前 Worker 进程...\n";
        Worker::stopAll(); // 停止当前进程，Master会自动重启它
        return;
    }

    // 如果是因为"文件变更"，通常需要重载所有进程以确保代码一致性
    echo "[System] 文件变更，平滑重载所有 Worker 进程...\n";
    if (function_exists('posix_kill')) {
        // 发送 SIGUSR1 信号给主进程，Workerman 会平滑重启所有 Worker
        posix_kill(posix_getppid(), SIGUSR1);
    } else {
        Worker::stopAll();
    }
}

/* ------------------------------------------------------------
 | 启动 Workerman Worker
 |------------------------------------------------------------
 */
$httpWorker = new Worker('http://0.0.0.0:8080');
$httpWorker->count = 4;
$framework = null;

/**
 * Worker 启动回调
 */
$httpWorker->onWorkerStart = function (Worker $worker) use (&$framework) {
    global $fileLastModify;
    
    $framework = Framework::getInstance();

    SchemaWarmup::setScanPath(
        __DIR__ . '/app/Models',
        'App\Models'
    );
    SchemaWarmup::warmupAll();
    SchemaRegistry::freeze();
	
	$pid = getmypid();

    // -------------------------- 初始化监控任务 --------------------------
    // 1. 初始化文件修改时间（仅在进程启动时执行一次）
    $fileLastModify = scanFilesLastModify(explode(PATH_SEPARATOR, MONITOR_DIRS));
    
    // 2. 定时输出内存占用（每30秒）
    Timer::add(MEMORY_LOG_INTERVAL, function () use ($worker) {
        $memory = getMemoryUsage();
        Worker::log( "[".date('Y-m-d H:i:s')."] 内存占用 -> 进程ID: {$worker->id}, 占用: {$memory}MB, 阈值: " . MEMORY_LIMIT_MB . "MB\n");
    });
    
    // 3. 内存阈值检测（每1秒检测一次，高频检测但低开销）
    Timer::add(1, function () use ($worker) {
        $memory = getMemoryUsage();
        if ($memory > MEMORY_LIMIT_MB) {
            Worker::log( "[".date('Y-m-d H:i:s')."] 内存超限 -> 进程ID: {$worker->id}, 当前: {$memory}MB, 阈值: " . MEMORY_LIMIT_MB . "MB\n");
            restartWorkerman($worker);
        }
    });
    
    // 4. 文件变化检测（每2秒检测一次）
    Timer::add(FILE_MONITOR_INTERVAL, function () use ($worker) {
        global $fileLastModify;
        $newFileTimes = scanFilesLastModify(explode(PATH_SEPARATOR, MONITOR_DIRS));
        
        // 检测文件新增/删除/修改
        $diff = false;
        // 检查原有文件是否修改
        foreach ($fileLastModify as $file => $time) {
            if (!isset($newFileTimes[$file]) || $newFileTimes[$file] > $time) {
                $diff = true;
                Worker::log( "[".date('Y-m-d H:i:s')."] 文件变化 -> {$file}\n");
                break;
            }
        }
        // 检查新增文件
        if (!$diff) {
            foreach ($newFileTimes as $file => $time) {
                if (!isset($fileLastModify[$file])) {
                    $diff = true;
                    Worker::log( "[".date('Y-m-d H:i:s')."] 文件新增 -> {$file}\n");
                    break;
                }
            }
        }
        
        if ($diff) {
            $fileLastModify = $newFileTimes; // 更新文件时间
            restartWorkerman($worker);
        }
    });
};

/**
 * HTTP 请求处理
 */
$httpWorker->onMessage = function(TcpConnection $connection, WorkermanRequest $req) use (&$framework) {
    global $needRestart;
    if ($needRestart) {
        // 重启过程中拒绝新请求
        $connection->send(new WorkermanResponse(503, [], "Server is restarting..."));
        return;
    }

    try {
        $symReq = convert_to_symfony_request($req);
        $symRes = $framework->handleRequest($symReq);
        
        if ($symReq->hasSession()) {
            $symReq->getSession()->save();
        }
        
        app('cookie')->sendQueuedCookies($symRes);
       
        $connection->send(convert_to_workerman_response($symRes));

    } catch (Throwable $e) {
        $error = "[Error] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";
        Worker::log($error); // 记录到错误日志
        $connection->send(new WorkermanResponse(500, [], "Internal Error: {$e->getMessage()}"));
    } finally {
        if (isset($symReq) && $symReq->hasSession()) {
        //    unset($symReq->getSession());
        }
        unset($symReq , $symRes);
        gc_collect_cycles();
    }
};

// -------------------------- 主进程重启处理 --------------------------
// 监听主进程的 USR1 信号，触发重启
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGUSR1, function () {
        echo "[".date('Y-m-d H:i:s')."] 主进程接收到重启信号，开始重启所有 Worker 进程\n";
        Worker::stopAll();
        // 重启所有 Worker（Workerman 内置的重启机制）
        exec('php ' . __FILE__ . ' start -d > /dev/null 2>&1 &');
    });
}

/* ------------------------------------------------------------
 | 运行 Workerman
 |------------------------------------------------------------
 */
Worker::runAll();