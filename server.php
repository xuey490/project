#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * server.php
 * Workerman wrapper for NovaFrame
 *
 * Usage:
 *  - CLI/Workerman mode: php server.php start|stop|restart|reload -d
 *  - Traditional FPM: don't run this script; your existing index.php / front controller remains unchanged.
 *
 * Notes:
 *  - This script initializes Framework per worker process on demand and uses Reflection
 *    to invoke framework internal flow for each request, avoiding modifying Framework.php / Kernel.php.
 */

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;
use Workerman\Protocols\Http\Response as WorkermanResponse;
use Workerman\Timer;
use Workerman\Protocols\Http;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Framework\Core\Framework;
#use Symfony\Component\HttpFoundation\Session\Session;
#use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Framework\Schema\SchemaWarmup;
use Framework\Schema\SchemaRegistry;


if (php_sapi_name() !== 'cli') {
    // 如果不是 CLI（即通过 FPM 被包含），什么也不做 -- 以保证 FPM 传统模式兼容
    return;
}

define('WORKERMAN_ENV' , true);


require_once __DIR__ . '/vendor/autoload.php';

define('BASE_PATH', __DIR__);
define('APP_ROOT', __DIR__);
define('LOG_DIR', APP_ROOT . '/storage/workerman');
define('HEALTH_FILE', LOG_DIR . '/health.json');

$watchDirs = [
    __DIR__ . '/app',
    __DIR__ . '/framework',
    __DIR__ . '/config',
    __DIR__ . '/routes'
];

if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}

const MEMORY_LIMIT_MB = 256;   // 允许的最大内存（单位 MB）
const MEMORY_CHECK_INTERVAL = 10; // 检查周期（秒）

Worker::$logFile = LOG_DIR .'/workerman.log';



#ini_set('session.save_handler', 'redis');
#ini_set('session.save_path', 'tcp://127.0.0.1:6379');
#session_name('PHPSESSID_');
#session_start();


// ----------------------------------------------------------------------
// 日志工具
// ----------------------------------------------------------------------
function log_info(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents(LOG_DIR . '/server.log', $line, FILE_APPEND);
	//Worker::log($line);
}

// ----------------------------------------------------------------------
// Symfony Request / Response 转换
// ----------------------------------------------------------------------


function convert_to_workerman_response(SymfonyResponse $res): WorkermanResponse {
    $headers = [];

    // 遍历所有 headers，保留原始结构
    foreach ($res->headers->allPreserveCase() as $name => $values) {
        // Symfony 返回的 $values 总是 array
        if (strtolower($name) === 'set-cookie') {
            // Set-Cookie 必须保持为数组，每个 cookie 一项
            $headers[$name] = $values; // 直接赋值数组
        } else {
            // 其他 header 合并为字符串（如果多值）
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
    // 基础请求信息
    $method = strtoupper($request->method());
    $uri = $request->uri();
    $rawBody = $request->rawBody();
    $remoteIp = $request->connection?->getRemoteIp() ?? '127.0.0.1';
    $remotePort = $request->connection?->getRemotePort() ?? 0;
	
	//$session =  $request->session();

    // 解析 URI 中的查询字符串（兼容 Workerman 可能未自动解析的场景）
    $uriParts = parse_url($uri);
    $pathInfo = $uriParts['path'] ?? '/';
    $queryString = $uriParts['query'] ?? '';

    // 请求参数
    $get = $request->get() ?? [];
    // 合并 URI 中的查询参数（避免 Workerman 解析不全）
    if (!empty($queryString)) {
        parse_str($queryString, $queryParams);
        $get = array_merge($queryParams, $get);
    }

    $post = $request->post() ?? [];
    $cookies = $request->cookie() ?? [];
    $files = $request->file() ?? [];
    $headers = $request->header() ?? [];
	
	$parameters = array_merge($get, $post);
	
	#$session->set('workerman_key', 'workerman_value');
	#$session->save();

    // 构建服务器环境变量（Symfony 依赖大量SERVER参数）
    $server = [
        // 基础 HTTP 信息
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
        // PHP 环境标识（避免 Symfony 认为是 CLI 环境）
        'PHP_SELF' => $pathInfo,
        'SCRIPT_NAME' => $pathInfo,
        'SCRIPT_FILENAME' => '', // 可根据实际项目路径填写
    ];

    // 转换请求头为 SERVER 变量（HTTP_* 格式）
    foreach ($headers as $name => $value) {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        // 处理多值头（如 Set-Cookie 可能是数组）
        $server[$key] = is_array($value) ? implode(', ', $value) : $value;
    }

    // 处理转发 IP（优先使用 X-Forwarded-For，不存在则用 remoteIp）
    if (!isset($server['HTTP_X_FORWARDED_FOR'])) {
        $server['HTTP_X_FORWARDED_FOR'] = $remoteIp;
    }

    // 处理特殊方法（如 PUT、DELETE 等可能携带的表单数据）
    if (in_array($method, ['PUT', 'DELETE', 'PATCH']) && empty($post) && !empty($rawBody)) {
        parse_str($rawBody, $parsedPost);
        $post = array_merge($post, $parsedPost);
    }

    // 构建并返回 Symfony Request

    $symfonyRequest = new SymfonyRequest(
        $get,
        $post,
        [], // attributes（通常由 Symfony 路由填充）
        $cookies,
        $files,
        $server,
        $rawBody
    );
    #$session = new Session();
	$session =app('session');
    $symfonyRequest->setSession($session);
	
	return $symfonyRequest;
	
}


// ----------------------------------------------------------------------
// 健康信息与日志轮转
// ----------------------------------------------------------------------
function update_health(): void {
    $health = [
        'pid'     => getmypid(),
        'memory'  => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'time'    => date('Y-m-d H:i:s'),
        'uptime'  => round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2) . ' s',
        'php'     => PHP_VERSION,
        'os'      => PHP_OS,
    ];
    file_put_contents(HEALTH_FILE, json_encode($health, JSON_PRETTY_PRINT));
}

function rotate_logs(): void {
    $file = LOG_DIR . '/server.log';
    if (file_exists($file) && filesize($file) > 2 * 1024 * 1024) {
        $new = LOG_DIR . '/server-' . date('Ymd_His') . '.log';
        rename($file, $new);
        log_info("[LogRotate] Rotated to $new");
    }
}

// ----------------------------------------------------------------------
// 检查文件是否变更（带过滤）
// ----------------------------------------------------------------------
function checkFilesChange(array $watchDirs, array &$lastMtimes): bool
{
    $excludeSuffixes = ['.tmp', '.swp', '.bak', '.~', '.part', '.log', '.lock'];
    $excludeDirs = ['\\.git\\', '\\.idea\\', '\\vendor\\', '\\runtime\\', '\\storage\\', '\\node_modules\\'];

    foreach ($watchDirs as $dir) {
        $dir = str_replace('/', '\\', $dir);
        if (!is_dir($dir)) continue;

        try {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $dir,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
                )
            );
        } catch (Exception $e) {
            continue; // 跳过无法读取的目录
        }

        foreach ($it as $file) {
            if (!$file->isFile()) continue;

            $path = str_replace('/', '\\', $file->getRealPath());
            if (!$path) continue;

            $filename = $file->getFilename();
            $filepath = str_replace('/', '\\', $file->getPath());

            // 跳过临时文件
            foreach ($excludeSuffixes as $suffix) {
                if (str_ends_with($filename, $suffix)) {
                    continue 2;
                }
            }

            // 跳过排除目录
            foreach ($excludeDirs as $exDir) {
                if (stripos($filepath, $exDir) !== false) {
                    continue 2;
                }
            }

            $mtime = $file->getMTime();
            if (!isset($lastMtimes[$path])) {
                $lastMtimes[$path] = $mtime;
                continue;
            }

            if ($mtime !== $lastMtimes[$path]) {
                $lastMtimes[$path] = $mtime;
                echo "[HotReload] File changed: {$path}\r\n";
                return true;
            }
        }
    }

    return false;
}

// ----------------------------------------------------------------------
// 启动工作进程
// ----------------------------------------------------------------------
function startWorkerProcess() {
    $script = $_SERVER['argv'][0] ?? __FILE__;
    $cmd = '"' . PHP_BINARY . '" "' . $script . '" --worker';
    $descriptorspec = [STDIN, STDOUT, STDOUT];
    $resource = proc_open($cmd, $descriptorspec, $pipes, null, null, ['bypass_shell' => true]);
    
    if (!$resource) {
        exit("Can not execute $cmd\r\n");
    }
    
    return $resource;
}

// ----------------------------------------------------------------------
// 主监控循环
// ----------------------------------------------------------------------
function startMonitorLoop($watchDirs): void {
    echo "[Monitor] Starting server with hot reload...\n";
    echo "Watching directories:\n";
    foreach ($watchDirs as $dir) {
        echo "  - {$dir}\n";
    }
    echo "Access: http://localhost:8000\n";
    echo "Health: http://localhost:8000/_health\n";
    echo "Press Ctrl+C to stop\n\n";
    
    $lastMtimes = [];
    $resource = startWorkerProcess();

    // 初始化文件时间戳
    checkFilesChange($watchDirs, $lastMtimes);
    
    while (true) {
        sleep(1);
        
        // 检查文件变化
        if (checkFilesChange($watchDirs, $lastMtimes)) {
            echo "[Monitor] File changes detected, restarting worker...\n";
            log_info("[Monitor] File changes detected, restarting worker...");
            
            // 杀死旧进程
            $status = proc_get_status($resource);
            $pid = $status['pid'];
            
            if ($pid && $status['running']) {
                if (stripos(PHP_OS, 'WIN') !== false) {
                    shell_exec("taskkill /F /T /PID $pid >nul 2>&1");
                } else {
                    posix_kill($pid, SIGKILL);
                }
				log_info("[Monitor] Killed worker process: $pid\n");
                echo "[Monitor] Killed worker process: $pid\n";
            }
            log_info("[Monitor] Worker Restart\n");
            proc_close($resource);
            
            // 启动新进程
            $resource = startWorkerProcess();
            echo "[Monitor] Started new worker process\n";
            log_info("[Monitor] Started new worker process");
        }
        
        // 检查工作进程是否意外退出
        $status = proc_get_status($resource);
        if (!$status['running']) {
            echo "[Monitor] Worker process died, restarting...\n";
            log_info("[Monitor] Worker process died, restarting...");
            proc_close($resource);
            $resource = startWorkerProcess();
            echo "[Monitor] Restarted worker process\n";
        }
        
        update_health();
    }
}

// ----------------------------------------------------------------------
// 判断是否工作进程
// ----------------------------------------------------------------------
function isWorkerProcess(): bool {
    global $argv;
    return isset($argv[1]) && $argv[1] === '--worker';
}

// ----------------------------------------------------------------------
// 主程序入口
// ----------------------------------------------------------------------

// 如果是工作进程模式，启动 Worker
if (isWorkerProcess()) {
    $worker = new Worker('http://0.0.0.0:8000');
    $worker->name = 'NovaFrame-Worker';
    $worker->count = 1;
    $framework = null;

    $worker->onWorkerStart = function(Worker $worker) use (&$framework) {
		

		
        log_info("[Worker] PID " . getmypid() . " started");
		Worker::log("[Worker] PID " . getmypid() . " started");
        update_health();

        $framework = Framework::getInstance();

		if (defined('WORKERMAN_ENV')) {
			// 设置扫描目录和命名空间
			SchemaWarmup::setScanPath(base_path('app/Models'), 'App\Models');

			// 可选：忽略某些模型
			SchemaWarmup::ignore([
				\App\Models\TempView::class,
			]);

			// 启动时自动扫描 warmup
			SchemaWarmup::warmupAll();

			// 冻结 schema，防止 runtime 注册新表
			SchemaRegistry::freeze();

			// 调试：打印已注册表
			//dump(array_keys(SchemaRegistry::all()));
		}
			
        // 定时任务：监控内存 + 日志轮转 + 健康记录
	
		Timer::add(MEMORY_CHECK_INTERVAL, function() use ($worker) {
			#print_r($worker);
			update_health();
            rotate_logs();
			
			$pid = getmypid();
			$memory = memory_get_usage(true) / 1024 / 1024; // MB
			$time = date('Y-m-d H:i:s');
			#Worker::log(base_path());
			Worker::log("[{$time}] [Memory] Worker #{$worker->id} PID {$pid} uses {$memory} MB\n" );

			// 如果超出阈值，则安全重启当前 worker
			if ($memory > MEMORY_LIMIT_MB) {
				Worker::log( "[{$time}] [Warning] Worker #{$worker->id} PID {$pid} memory exceeded limit ({$memory} MB > " . MEMORY_LIMIT_MB . " MB), reloading...\n" );

				// 使用 stopAll 仅关闭当前进程
				$worker->stop();

				// 给操作系统一点时间释放资源
				usleep(500000); // 0.5 秒
				
				exit(1); // 退出让监控进程重启

				// 自动拉起新 worker（Workerman 会自动 fork 新进程）
				posix_kill(posix_getppid(), SIGUSR1);
			}
		});

    };

    $worker->onMessage = function(TcpConnection $connection, WorkermanRequest $req) use (&$framework) {
        try {
            if ($req->path() === '/_health') {
                update_health();
                $data = file_get_contents(HEALTH_FILE);
                $response = new SymfonyResponse($data, 200, ['Content-Type' => 'application/json']);
                $connection->send(convert_to_workerman_response($response));
                return;
            }

            $symReq = convert_to_symfony_request($req);
            $symRes = $framework->handleRequest($symReq);
			
			// ✅ 关键：在 send 之前关闭 session，触发写入 Redis
			if ($symReq->hasSession()) {
				$symReq->getSession()->save(); // 或 ->save() / ->close()
			}
			
			// ✅ 如果在业务逻辑里 queueCookie() 了 Cookie，统一发送
			app('cookie')->sendQueuedCookies($symRes);
           
			$connection->send(convert_to_workerman_response($symRes));

        } catch (Throwable $e) {
            $error = "[Error] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";
            log_info($error);
            $connection->send(new WorkermanResponse(500, [], "Internal Error: {$e->getMessage()}"));
		} finally {
			// 可选：清理
			if (isset($symReq) && $symReq->hasSession()) {
				//$symReq->getSession()->clear(); // 避免内存泄漏
			}
			unset($symReq , $symRes);
			gc_collect_cycles();
		}
    };


    Worker::runAll();
    exit(0);
}

// 主进程：启动监控循环
startMonitorLoop($watchDirs);

