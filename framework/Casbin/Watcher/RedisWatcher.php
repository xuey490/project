<?php
/**
 * @desc RedisWatcher.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/1/17 10:02
 */

declare(strict_types=1);

namespace Framework\Casbin\Watcher;

use Casbin\Persist\Watcher;
use Closure;
use Redis;
use RedisException;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Redis Watcher for Casbin
 * 基于 PHP 原生 Redis 扩展实现
 */
class RedisWatcher implements Watcher
{
    private ?Closure $callback = null;
    private Redis $pubRedis;
    private ?Redis $subRedis = null;
    private string $channel;
    private ?LoggerInterface $logger = null;
    private bool $isListening = false;

    /**
     * @param array $config
     * @param string $driver
     * @param LoggerInterface|null $logger
     */
    public function __construct(array $config, string $driver, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 6379;
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? 0;
        $timeout = $config['timeout'] ?? 5.0;

        try {
            // 发布客户端
            $this->pubRedis = new Redis();
            $this->pubRedis->connect($host, $port, $timeout);
            if (!empty($password)) {
                $this->pubRedis->auth($password);
            }
            $this->pubRedis->select($database);

            // 订阅客户端
            $this->subRedis = new Redis();
            $this->subRedis->connect($host, $port, 0); // 订阅需设置超时为0
            if (!empty($password)) {
                $this->subRedis->auth($password);
            }
            $this->subRedis->select($database);

            $this->channel = ($config['channel'] ?? '/casbin') . '/' . $driver;
            $this->logInfo('Redis Watcher 初始化成功', ['channel' => $this->channel]);
        } catch (RedisException $e) {
            $this->logError('Redis 连接失败', ['error' => $e->getMessage()]);
            throw new RuntimeException('Redis Watcher 初始化失败: ' . $e->getMessage(), 0, $e);
        }
    }

    public function setUpdateCallback(Closure $func): void
    {
        $this->callback = $func;
    }

    public function update(): void
    {
        try {
            $this->pubRedis->publish($this->channel, 'casbin rules updated');
        } catch (RedisException $e) {
            $this->logError('发布消息失败', ['error' => $e->getMessage()]);
            throw new RuntimeException('发布策略更新通知失败', 0, $e);
        }
    }

    public function startListening(): void
    {
        if ($this->isListening || is_null($this->subRedis)) {
            return;
        }

        $this->isListening = true;
        $this->subRedis->setOption(Redis::OPT_READ_TIMEOUT, -1); // 永久阻塞

        try {
            $this->subRedis->subscribe([$this->channel], function ($redis, $channel, $message) {
                if ($this->callback instanceof Closure) {
                    call_user_func($this->callback);
                }
            });
        } catch (RedisException $e) {
            $this->isListening = false;
            throw new RuntimeException('订阅监听失败', 0, $e);
        }
    }

    public function stopListening(): void
    {
        if (!$this->isListening || is_null($this->subRedis)) {
            return;
        }

        try {
            $this->subRedis->unsubscribe([$this->channel]);
            $this->isListening = false;
        } catch (RedisException $e) {
            $this->logError('取消订阅失败', ['error' => $e->getMessage()]);
        }
    }

    public function close(): void
    {
        $this->stopListening();
        
        try {
            $this->pubRedis->close();
        } catch (RedisException $e) {}
        
        try {
            if ($this->subRedis) {
                $this->subRedis->close();
            }
        } catch (RedisException $e) {}
    }

    private function logInfo(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info('[Casbin Redis Watcher] ' . $message, $context);
        }
    }

    private function logError(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->error('[Casbin Redis Watcher] ' . $message, $context);
        }
    }

    public function isListening(): bool
    {
        return $this->isListening;
    }
}