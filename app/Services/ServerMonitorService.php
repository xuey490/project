<?php

declare(strict_types=1);

/**
 * 服务器监控服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use Framework\Basic\BaseService;

/**
 * ServerMonitorService 服务器监控服务
 */
class ServerMonitorService extends BaseService
{
    /**
     * 获取服务器信息
     *
     * @return array
     */
    public function getServerInfo(): array
    {
        return [
            'os' => PHP_OS_FAMILY . ' ' . PHP_OS,
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'php_uname' => php_uname('a'),
            'server_time' => date('Y-m-d H:i:s'),
            'server_timezone' => date_default_timezone_get(),
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'server_ip' => $this->getServerIp(),
        ];
    }

    /**
     * 获取PHP信息
     *
     * @return array
     */
    public function getPhpInfo(): array
    {
        $extensions = get_loaded_extensions();
        $extensionsList = [];

        foreach ($extensions as $ext) {
            $extensionsList[] = [
                'name' => $ext,
                'version' => phpversion($ext),
            ];
        }

        return [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'extensions' => $extensionsList,
            'memory_limit' => $this->formatBytes(ini_get('memory_limit')),
            'upload_max_filesize' => $this->formatBytes((int)ini_get('upload_max_filesize')),
            'post_max_size' => $this->formatBytes((int)ini_get('post_max_size')),
            'max_execution_time' => ini_get('max_execution_time'),
        ];
    }

    /**
     * 获取CPU信息
     *
     * @return array
     */
    public function getCpuInfo(): array
    {
        $loadAvg = sys_getloadavg();

        return [
            'cpu_cores' => function_exists('sys_getcpuinfo') ? count(sys_getcpuinfo()) : 1,
            'cpu_model' => function_exists('sys_getcpuinfo') ? sys_getcpuinfo()[0]['model'] ?? 'Unknown' : 'Unknown',
            'cpu_usage' => $loadAvg ? round($loadAvg * 100, 2) . '%' : '0%',
            'load_average' => $loadAvg ? round($loadAvg, 2) : 0,
        ];
    }

    /**
     * 获取内存信息
     *
     * @return array
     */
    public function getMemoryInfo(): array
    {
        $free = shell_exec('free -m | awk '/Mem:/ {print $2}');
        $total = shell_exec('grep MemTotal /proc/meminfo | awk -F2');
        $available = shell_exec('grep MemAvailable /proc/meminfo | awk -F2');

        // 如果无法从系统获取，尝试使用PHP内存
        if (empty($total)) {
            $total = $this->formatBytes($this->getTotalMemory());
            $free = $this->formatBytes($this->getFreeMemory());
        }

        return [
            'total' => $total,
            'free' => $free,
            'used' => $this->formatBytes($this->getUsedMemory()),
            'usage_percent' => round(($this->getUsedMemory() / $this->getTotalMemory()) * 100, 2) . '%',
        ];
    }

    /**
     * 获取磁盘信息
     *
     * @return array
     */
    public function getDiskInfo(): array
    {
        $disks = [];

        // 获取磁盘使用情况
        $output = shell_exec('df -h 2>/dev/null');

        if ($output) {
            $lines = explode("\n", trim($output));
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', $line);
                if (count($parts) >= 6 && $parts[0] !== 'Filesystem') {
                    $disks[] = [
                        'filesystem' => $parts[0],
                        'size' => $parts[1],
                        'used' => $parts[2],
                        'available' => $parts[3],
                        'use_percent' => $parts[4],
                        'mounted_on' => $parts[5],
                    ];
                }
            }
        }

        return $disks;
    }

    /**
     * 获取完整监控信息
     *
     * @return array
     */
    public function getFullInfo(): array
    {
        return [
            'server' => $this->getServerInfo(),
            'php' => $this->getPhpInfo(),
            'cpu' => $this->getCpuInfo(),
            'memory' => $this->getMemoryInfo(),
            'disk' => $this->getDiskInfo(),
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化字节
     *
     * @param int $bytes 字节数
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * 获取服务器IP
     *
     * @return string
     */
    protected function getServerIp(): string
    {
        $ip = $_SERVER['SERVER_ADDR'] ?? '';

        if (empty($ip)) {
            $ip = gethostbyname(gethostname());
            $ip = $ip ? $ip['ip'] : '127.0.0.1';
        }

        return $ip;
    }

    /**
     * 获取总内存
     *
     * @return int
     */
    protected function getTotalMemory(): int
    {
        return (int)(memory_get_usage(true)[0] / 1024 / 1024);
    }

    /**
     * 获取已用内存
     *
     * @return int
     */
    protected function getUsedMemory(): int
    {
        return (int)(memory_get_usage(true)[1] / 1024 / 1024);
    }

    /**
     * 获取可用内存
     *
     * @return int
     */
    protected function getFreeMemory(): int
    {
        return (int)(memory_get_usage(true)[2] / 1024 / 1024);
    }
}
