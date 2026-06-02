#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * queue.php
 * 队列消费 Worker 独立启动文件（Windows 兼容）
 *
 * Windows 下 Workerman 不支持在同一个 PHP 文件里初始化多个 Worker，
 * 因此将队列消费 Worker 单独拆分到此文件运行。
 *
 * Usage:
 *   php queue.php start     - 启动队列消费（前台调试）
 *   php queue.php stop      - 停止
 *   php queue.php restart   - 重启
 *   php queue.php status    - 查看状态
 *
 * @Filename  queue.php
 * @Date      2026-06-03
 * @Developer blue2004
 * @Email     xuey863toy@gmail.com
 */

use Workerman\Worker;
use Workerman\Timer;
use Framework\Pool\RedisPool;
use Framework\Pool\PoolManager;
use Framework\Queue\RedisConsumerService;
use App\Queue\Handlers\DefaultMessageHandler;
use App\Queue\Handlers\ArticleMessageHandler;

// 只允许 CLI 模式运行
if (php_sapi_name() !== 'cli') {
    exit('只允许在 CLI 模式下运行' . PHP_EOL);
}

define('WORKERMAN_ENV', true);
define('BASE_PATH', __DIR__);
define('APP_ROOT', __DIR__);
define('LOG_DIR', APP_ROOT . '/storage/workerman');

// 创建日志目录
if (!is_dir(LOG_DIR)) {
    mkdir(LOG_DIR, 0777, true);
}

require_once __DIR__ . '/vendor/autoload.php';

// 设置 Workerman 日志文件
Worker::$logFile = LOG_DIR . '/workerman-queue.log';

// ----------------------------------------------------------------------
// 日志工具（与 server.php 日志写到同一文件方便统一查看）
// ----------------------------------------------------------------------
function log_info(string $msg): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    file_put_contents(LOG_DIR . '/server.log', $line, FILE_APPEND);
}

// ----------------------------------------------------------------------
// 加载 Redis 配置 & 创建队列 Worker
// ----------------------------------------------------------------------
$redisConfig = require __DIR__ . '/config/redis.php';
$queueConfig = $redisConfig['queue'] ?? [];

if (empty($queueConfig['enabled'])) {
    exit('队列消费未启用（redis.php queue.enabled = false），退出。' . PHP_EOL);
}

// Windows 下 count 只能为 1（不支持多进程 fork）
$queueWorker        = new Worker();
$queueWorker->name  = 'FSSPHP-Queue';
$queueWorker->count = 1;

// ----------------------------------------------------------------------
// Worker 启动回调
// ----------------------------------------------------------------------
$queueWorker->onWorkerStart = function (Worker $worker) use ($redisConfig, $queueConfig): void {
    log_info(sprintf('[Queue-Worker #%d] PID %d 启动', $worker->id, getmypid()));

    // 1. 初始化 Redis 连接池
    try {
        $primaryNode     = $redisConfig['nodes'][0] ?? [];
        $redisPoolConfig = array_merge($primaryNode, $redisConfig['pool'] ?? []);
        PoolManager::register('redis.default', new RedisPool($redisPoolConfig));
        log_info(sprintf('[Queue-Worker #%d] Redis 连接池已初始化', $worker->id));
    } catch (\Throwable $e) {
        log_info(sprintf('[Queue-Worker #%d] Redis 连接池初始化失败：%s', $worker->id, $e->getMessage()));
        return;
    }

    // 2. 注册处理器并启动各队列消费服务
    foreach ($queueConfig['queues'] ?? [] as $qCfg) {
        try {
            $consumer = new RedisConsumerService($qCfg);

            $consumer->registerHandlers([
                'default'           => new DefaultMessageHandler(),
                'article_published' => new ArticleMessageHandler(),
                'article_view'      => new ArticleMessageHandler(),
                // 扩展：取消注释并实现对应 Handler 类
                // 'send_email'   => new \App\Queue\Handlers\SendEmailHandler(),
                // 'send_sms'     => new \App\Queue\Handlers\SendSmsHandler(),
                // 'export_excel' => new \App\Queue\Handlers\ExportExcelHandler(),
            ]);

            $consumer->start($worker);

            log_info(sprintf(
                '[Queue-Worker #%d] 队列 [%s] 消费服务已启动',
                $worker->id,
                $qCfg['name'] ?? 'unknown'
            ));
        } catch (\Throwable $e) {
            log_info(sprintf(
                '[Queue-Worker #%d] 队列 [%s] 启动失败：%s',
                $worker->id,
                $qCfg['name'] ?? 'unknown',
                $e->getMessage()
            ));
        }
    }
};

// ----------------------------------------------------------------------
// Worker 停止回调
// ----------------------------------------------------------------------
$queueWorker->onWorkerStop = function (Worker $worker): void {
    log_info(sprintf('[Queue-Worker #%d] 正在停止，关闭连接池...', $worker->id));
    PoolManager::closeAll();
    log_info(sprintf('[Queue-Worker #%d] 已停止', $worker->id));
};

// ----------------------------------------------------------------------
// 运行
// ----------------------------------------------------------------------
Worker::runAll();
