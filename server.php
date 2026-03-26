#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * server.php
 * Workerman wrapper for FSSPHP
 *
 * Usage:
 *   php server.php start          - Start in debug mode (foreground)
 *   php server.php start -d       - Start in daemon mode (background)
 *   php server.php stop           - Stop server
 *   php server.php restart        - Restart server
 *   php server.php reload         - Reload business logic
 *   php server.php status         - Show server status
 *   php server.php connections    - Show connections
 *
 * Traditional FPM: don't run this script; your existing index.php / front controller remains unchanged.
 */

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request as WorkermanRequest;
use Workerman\Protocols\Http\Response as WorkermanResponse;
use Workerman\Timer;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Framework\Core\Framework;
use Framework\Schema\SchemaWarmup;
use Framework\Schema\SchemaRegistry;

// 只允许 CLI 模式运行
if (php_sapi_name() !== 'cli') {
    return;
}

define('WORKERMAN_ENV', true);
define('BASE_PATH', __DIR__);
define('APP_ROOT', __DIR__);
define('LOG_DIR', APP_ROOT . '/storage/workerman');
define('HEALTH_FILE', LOG_DIR . '/health.json');

// 创建日志目录
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}

const MEMORY_LIMIT_MB = 256;
const MEMORY_CHECK_INTERVAL = 10;

require_once __DIR__ . '/vendor/autoload.php';

// 设置日志文件
Worker::$logFile = LOG_DIR . '/workerman.log';

// ----------------------------------------------------------------------
// 日志工具
// ----------------------------------------------------------------------
function log_info(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents(LOG_DIR . '/server.log', $line, FILE_APPEND);
}

// ----------------------------------------------------------------------
// 健康检查与日志轮转
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
// Symfony Request / Response 转换
// ----------------------------------------------------------------------
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

    // 移除可能存在的 Content-Length 头，让 Workerman 自动计算
    if (isset($headers['content-length'])) {
        unset($headers['content-length']);
    }

    return new WorkermanResponse($res->getStatusCode(), $headers, $content);
}

/**
 * 将 Workerman Request 转换为 Symfony Request
 */
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
    
    // 处理上传文件
    $symfonyFiles = [];
    $wmFiles = $request->file() ?? [];
    
    foreach ($wmFiles as $field => $fileInfo) {
        // 单文件
        if (isset($fileInfo['tmp_name'])) {
            if (!empty($fileInfo['tmp_name']) && file_exists($fileInfo['tmp_name'])) {
                $symfonyFiles[$field] = new UploadedFile(
                    $fileInfo['tmp_name'],
                    $fileInfo['name'] ?? '',
                    $fileInfo['type'] ?? null,
                    $fileInfo['error'] ?? UPLOAD_ERR_OK,
                    true
                );
            }
            continue;
        }

        // 多文件
        if (is_array($fileInfo)) {
            $files = [];
            foreach ($fileInfo as $index => $item) {
                if (!isset($item['tmp_name']) || empty($item['tmp_name']) || !file_exists($item['tmp_name'])) {
                    continue;
                }
                $files[$index] = new UploadedFile(
                    $item['tmp_name'],
                    $item['name'] ?? '',
                    $item['type'] ?? null,
                    $item['error'] ?? UPLOAD_ERR_OK,
                    true
                );
            }
            if ($files) {
                $symfonyFiles[$field] = $files;
            }
        }
    }

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

    return new SymfonyRequest(
        $get,
        $post,
        [],
        $cookies,
        $symfonyFiles,
        $server,
        $rawBody
    );
}

/**
 * 获取文件的 MIME 类型
 */
function get_mime_type(string $filePath): string
{
    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'bmp'  => 'image/bmp',
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'ogg'  => 'video/ogg',
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav',
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt'  => 'text/plain',
        'html' => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'zip'  => 'application/zip',
        'rar'  => 'application/vnd.rar',
        '7z'   => 'application/x-7z-compressed',
        'tar'  => 'application/x-tar',
        'gz'   => 'application/gzip',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'  => 'font/ttf',
        'eot'  => 'application/vnd.ms-fontobject',
    ];
    
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    return $mimeTypes[$extension] ?? 'application/octet-stream';
}

// ----------------------------------------------------------------------
// 创建 HTTP Worker
// ----------------------------------------------------------------------
$worker = new Worker('http://0.0.0.0:8000');
$worker->name = 'FSSPHP-Worker';
$worker->count = 4; // 根据 CPU 核心数调整

// 存储 Framework 实例
$framework = null;

// ----------------------------------------------------------------------
// Worker 启动回调
// ----------------------------------------------------------------------
$worker->onWorkerStart = function(Worker $worker) use (&$framework) {
    log_info("[Worker] PID " . getmypid() . " started");
    Worker::log("[Worker] PID " . getmypid() . " started");
    update_health();

    // 初始化框架
    $framework = Framework::getInstance();

    // Schema 预热
    if (defined('WORKERMAN_ENV')) {
        SchemaWarmup::setScanPath(base_path('app/Models'), 'App\Models');
        SchemaWarmup::ignore([
            \App\Models\TempView::class,
        ]);
        SchemaWarmup::warmupAll();
        SchemaRegistry::freeze();
    }
    
    // 定时任务：内存监控、日志轮转、健康检查
    Timer::add(MEMORY_CHECK_INTERVAL, function() use ($worker) {
        update_health();
        rotate_logs();
        
        $pid = getmypid();
        $memory = memory_get_usage(true) / 1024 / 1024;
        $time = date('Y-m-d H:i:s');
        
        Worker::log("[{$time}] [Memory] Worker #{$worker->id} PID {$pid} uses {$memory} MB");

        // 内存超限则重启
        if ($memory > MEMORY_LIMIT_MB) {
            Worker::log("[{$time}] [Warning] Worker #{$worker->id} PID {$pid} memory exceeded limit ({$memory} MB > " . MEMORY_LIMIT_MB . " MB), stopping...");
            $worker->stop();
        }
    });
};

// ----------------------------------------------------------------------
// 请求处理回调
// ----------------------------------------------------------------------
$worker->onMessage = function(TcpConnection $connection, WorkermanRequest $req) use (&$framework) {
    $symReq = null;
    $symRes = null;
    
    try {
        // ==================== 静态文件处理 ====================
        $uri = $req->uri();
        $pathInfo = parse_url($uri, PHP_URL_PATH);
        
        $staticDirs = ['/uploads', '/assets', '/css', '/js', '/images', '/favicon.ico'];
        $isStaticFile = false;
        
        foreach ($staticDirs as $dir) {
            if (strpos($pathInfo, $dir) === 0) {
                $isStaticFile = true;
                break;
            }
        }
        
        if ($isStaticFile) {
            $filePath = __DIR__ . '/public' . $pathInfo;
            $realPath = realpath($filePath);
            $publicDir = realpath(__DIR__ . '/public');
            
            if ($realPath && strpos($realPath, $publicDir) === 0 && is_file($realPath)) {
                $contentType = get_mime_type($realPath);
                $fileContent = file_get_contents($realPath);
                
                $headers = [
                    'Content-Type' => $contentType,
                    'Cache-Control' => 'public, max-age=86400',
                ];
                
                if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $realPath)) {
                    $headers['Cache-Control'] = 'public, max-age=2592000';
                }
                
                $connection->send(new WorkermanResponse(200, $headers, $fileContent));
                return;
            }
            
            $connection->send(new WorkermanResponse(404, ['Content-Type' => 'text/plain'], 'File Not Found'));
            return;
        }
        // ==================== 静态文件处理结束 ====================

        // 健康检查端点
        if ($req->path() === '/_health') {
            update_health();
            $data = file_get_contents(HEALTH_FILE);
            $response = new SymfonyResponse($data, 200, ['Content-Type' => 'application/json']);
            $connection->send(convert_to_workerman_response($response));
            return;
        }

        // 转换请求并处理
        $symReq = convert_to_symfony_request($req);
        $symRes = $framework->handleRequest($symReq);
        
        // 保存 Session
        if ($symReq->hasSession()) {
            $symReq->getSession()->save();
        }
        
        // 发送队列中的 Cookie
        app('cookie')->sendQueuedCookies($symRes);
        
        $connection->send(convert_to_workerman_response($symRes));

    } catch (Throwable $e) {
        $error = "[Error] {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";
        log_info($error);
        Worker::log($error);
        $connection->send(new WorkermanResponse(500, [], "Internal Error: {$e->getMessage()}"));
    } finally {
        // 清理资源
        unset($symReq, $symRes);
        gc_collect_cycles();
    }
};

// ----------------------------------------------------------------------
// 运行 Worker
// ----------------------------------------------------------------------
Worker::runAll();
