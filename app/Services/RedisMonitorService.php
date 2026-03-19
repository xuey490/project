<?php

declare(strict_types=1);

/**
 * Redis监控服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use Framework\Basic\BaseService;
use Predis\Client;

/**
 * RedisMonitorService Redis监控服务
 */
class RedisMonitorService extends BaseService
{
    /**
     * Redis客户端
     * @var Client|null
     */
    protected ?Client $redis = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->initRedis();
    }

    /**
     * 初始化Redis连接
     *
     * @return void
     */
    protected function initRedis(): void
    {
        try {
            $config = [
                'scheme' => env('REDIS_SCHEME', 'tcp'),
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'port' => (int)env('REDIS_PORT', 6379),
                'database' => (int)env('REDIS_DB', 0),
            ];

            $password = env('REDIS_PASSWORD');
            if ($password) {
                $config['password'] = $password;
            }

            $this->redis = new Client($config);
        } catch (\Exception $e) {
                $this->redis = null;
            }
    }

    /**
     * 检查Redis连接状态
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        if (!$this->redis) {
            return false;
        }

        try {
            $this->redis->ping();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取Redis服务器信息
     *
     * @return array
     */
    public function getServerInfo(): array
    {
        if (!$this->isConnected()) {
            return ['error' => 'Redis连接失败'];
        }

        $info = [];

        try {
            // 获取服务器信息
            $serverInfo = $this->redis->info();

            // 解析服务器信息
            foreach (explode("\n", $serverInfo) as $line) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $info[trim($parts[0])] = trim($parts[1]);
                }
            }
        } catch (\Exception $e) {
            $info['error'] = $e->getMessage();
        }

        return $info;
    }

    /**
     * 获取Redis版本
     *
     * @return string
     */
    public function getVersion(): string
    {
        if (!$this->isConnected()) {
            return 'N/A';
        }

        try {
            return $this->redis->info('server')['redis_version'] ?? 'Unknown';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * 获取运行时间(秒)
     *
     * @return int
     */
    public function getUptime(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            return (int)$this->redis->info('server')['uptime_in_seconds'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取已连接客户端数
     *
     * @return int
     */
    public function getConnectedClients(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            return (int)$this->redis->info('clients')['connected_clients'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取内存信息
     *
     * @return array
     */
    public function getMemoryInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $memory = $this->redis->info('memory');

            return [
                'used_memory' => $this->formatBytes($memory['used_memory'] ?? 0),
                'used_memory_peak' => $this->formatBytes($memory['used_memory_peak'] ?? 0),
                'used_memory_rss' => $this->formatBytes($memory['used_memory_rss'] ?? 0),
                'used_memory_dataset' => $this->formatBytes($memory['used_memory_dataset'] ?? 0),
                'total_system_memory' => $this->formatBytes($memory['total_system_memory'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取持久化信息
     *
     * @return array
     */
    public function getPersistenceInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $persistence = $this->redis->info('persistence');

            return [
                'loading' => $persistence['loading'] ?? 0,
                'rdb_changes_since_last_save' => $persistence['rdb_changes_since_last_save'] ?? 0,
                'rdb_last_save_time' => $persistence['rdb_last_save_time'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取统计信息
     *
     * @return array
     */
    public function getStatsInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $stats = $this->redis->info('stats');

            return [
                'total_connections_received' => $stats['total_connections_received'] ?? 0,
                'total_commands_processed' => $stats['total_commands_processed'] ?? 0,
                'instantaneous_ops_per_sec' => $stats['instantaneous_ops_per_sec'] ?? 0,
                'total_net_input_bytes' => $this->formatBytes($stats['total_net_input_bytes'] ?? 0),
                'total_net_output_bytes' => $this->formatBytes($stats['total_net_output_bytes'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取CPU信息
     *
     * @return array
     */
    public function getCpuInfo(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $cpu = $this->redis->info('cpu');

            return [
                'used_cpu_sys' => $cpu['used_cpu_sys'] ?? 0,
                'used_cpu_user' => $cpu['used_cpu_user'] ?? 0,
                'used_cpu_avg' => $cpu['used_cpu_avg'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取命令统计
     *
     * @return array
     */
    public function getCommandStats(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $commandStats = $this->redis->info('commandstats');
            return $commandStats;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取数据库大小
     *
     * @return array
     */
    public function getDbSize(): array
    {
        if (!$this->isConnected()) {
            return [];
        }

        try {
            $size = [];
            $config = $this->redis->config('get', 'databases');
            $databases = (int)$config[0] ?? 16;

            for ($i = 0; $i < $databases; $i++) {
                $dbSize = $this->redis->info('memory', 'db' . $i);
                if (isset($dbSize['db' . $i])) {
                    $size['db' . $i] = $this->formatBytes($dbSize['db' . $i]);
                }
            }

            return $size;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取完整监控信息
     *
     * @return array
     */
    public function getFullInfo(): array
    {
        return [
            'status' => $this->isConnected() ? 'connected' : 'disconnected',
            'version' => $this->getVersion(),
            'uptime' => $this->getUptime(),
            'connected_clients' => $this->getConnectedClients(),
            'memory' => $this->getMemoryInfo(),
            'persistence' => $this->getPersistenceInfo(),
            'stats' => $this->getStatsInfo(),
            'cpu' => $this->getCpuInfo(),
            'db_size' => $this->getDbSize(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 格式化字节
     *
     * @param int $bytes 字节数
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 清空所有缓存
     *
     * @return bool
     */
    public function flushAll(): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        try {
            $this->redis->flushall();
            return true;
        } catch (\Exception $e) {
                return false;
            }
    }

    /**
     * 获取所有键的数量
     *
     * @return int
     */
    public function getDbSizeInfo(): int
    {
        if (!$this->isConnected()) {
            return 0;
        }

        try {
            return $this->redis->dbsize();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
