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
                'version' => phpversion($ext) ?: '未知',
            ];
        }

        // 修复字节格式化：处理非数字的配置值（如 '128M'）
        $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
        $uploadMax = $this->convertToBytes(ini_get('upload_max_filesize'));
        $postMax = $this->convertToBytes(ini_get('post_max_size'));

        return [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'extensions' => $extensionsList,
            'memory_limit' => $this->formatBytes($memoryLimit),
            'upload_max_filesize' => $this->formatBytes($uploadMax),
            'post_max_size' => $this->formatBytes($postMax),
            'max_execution_time' => ini_get('max_execution_time') ?: '0',
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
        $cpuCores = 1;
        $cpuModel = 'Unknown';

        // 修复：sys_getcpuinfo 不是PHP内置函数，改用系统命令获取
        if (PHP_OS_FAMILY === 'Linux') {
            // 获取CPU核心数
            $cpuCores = (int)shell_exec('nproc 2>/dev/null') ?: 1;
            // 获取CPU型号
            $cpuModel = trim(shell_exec("grep 'model name' /proc/cpuinfo | head -n1 | cut -d: -f2 2>/dev/null")) ?: 'Unknown';
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec('wmic cpu get NumberOfCores /value', $output);
            preg_match('/NumberOfCores=(\d+)/', implode('', $output), $matches);
            $cpuCores = $matches[1] ?? 1;
            
            exec('wmic cpu get Name /value', $output);
            preg_match('/Name=(.+)/', implode('', $output), $matches);
            $cpuModel = trim($matches[1] ?? 'Unknown');
        }

        // 修复负载计算：loadavg返回数组 [1分钟, 5分钟, 15分钟]
        $load1min = $loadAvg ? $loadAvg[0] : 0;
        $cpuUsage = $loadAvg ? round(($load1min / $cpuCores) * 100, 2) . '%' : '0%';

        return [
            'cpu_cores' => $cpuCores,
            'cpu_model' => $cpuModel,
            'cpu_usage' => $cpuUsage,
            'load_average' => [
                '1min' => round($load1min, 2),
                '5min' => $loadAvg ? round($loadAvg[1], 2) : 0,
                '15min' => $loadAvg ? round($loadAvg[2], 2) : 0,
            ],
        ];
    }

    /**
     * 获取内存信息
     *
     * @return array
     */
    public function getMemoryInfo(): array
    {
        $total = 0;
        $free = 0;
        $available = 0;

        // 修复：转义字符串中的特殊字符，修正awk命令语法
        if (PHP_OS_FAMILY === 'Linux') {
            // 正确的awk命令：转义单引号，修正分隔符
            $freeOutput = shell_exec('free -m | awk \'/Mem:/ {print $4}\' 2>/dev/null'); // 空闲内存(MB)
            $totalOutput = shell_exec('grep MemTotal /proc/meminfo | awk -F: \'{print $2}\' | awk \'{print $1}\' 2>/dev/null'); // 总内存(KB)
            $availableOutput = shell_exec('grep MemAvailable /proc/meminfo | awk -F: \'{print $2}\' | awk \'{print $1}\' 2>/dev/null'); // 可用内存(KB)

            if ($totalOutput) {
                $total = (int)$totalOutput / 1024; // 转成MB
                $free = (int)$freeOutput; // 已经是MB
                $available = $availableOutput ? (int)$availableOutput / 1024 : $free;
            }
        }

        // 如果无法从系统获取，使用PHP内置方法
        if (empty($total)) {
            $total = $this->getTotalMemory() / 1024 / 1024; // 转成MB
            $free = $this->getFreeMemory() / 1024 / 1024;
            $available = $free;
        }

        $used = $total - $available;
        $usagePercent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

        return [
            'total' => $this->formatBytes((int)($total * 1024 * 1024)),
            'free' => $this->formatBytes((int)($free * 1024 * 1024)),
            'available' => $this->formatBytes((int)($available * 1024 * 1024)),
            'used' => $this->formatBytes((int)($used * 1024 * 1024)),
            'usage_percent' => $usagePercent . '%',
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
        if (PHP_OS_FAMILY === 'Linux') {
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
        } elseif (PHP_OS_FAMILY === 'Windows') {
            // 适配Windows系统
            $output = [];
            exec('wmic logicaldisk get caption,size,freespace,drivetype /format:list', $output);
            $diskInfo = [];
            foreach ($output as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                list($key, $value) = explode('=', $line, 2);
                $diskInfo[$key] = $value;
                
                if ($key === 'DriveType' && $value == 3) { // 本地磁盘
                    $caption = $diskInfo['Caption'] ?? '';
                    $size = $diskInfo['Size'] ?? 0;
                    $free = $diskInfo['FreeSpace'] ?? 0;
                    $used = $size - $free;
                    $usedPercent = $size > 0 ? round(($used / $size) * 100, 2) . '%' : '0%';
                    
                    $disks[] = [
                        'filesystem' => $caption,
                        'size' => $this->formatBytes((int)$size),
                        'used' => $this->formatBytes((int)$used),
                        'available' => $this->formatBytes((int)$free),
                        'use_percent' => $usedPercent,
                        'mounted_on' => $caption,
                    ];
                    $diskInfo = [];
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
     * 格式化字节为易读单位
     *
     * @param int $bytes 字节数
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $exp = (int)(log($bytes, 1024));
        $size = $bytes / pow(1024, $exp);

        return round($size, 2) . ' ' . $units[$exp];
    }

    /**
     * 将PHP配置值（如128M）转换为字节数
     *
     * @param string $value 配置值
     * @return int
     */
    protected function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int)$value;

        switch ($last) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }

        return $value;
    }

    /**
     * 获取服务器IP
     *
     * @return string
     */
    protected function getServerIp(): string
    {
        // 修复：gethostbyname返回字符串，不是数组
        $ip = $_SERVER['SERVER_ADDR'] ?? '';

        if (empty($ip)) {
            $hostname = gethostname();
            $ip = gethostbyname($hostname);
            
            // 如果获取到的是本地回环地址，尝试其他方式
            if ($ip === '127.0.0.1' || $ip === '::1') {
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            }
        }

        return $ip;
    }

    /**
     * 获取总内存（字节）
     *
     * @return int
     */
    protected function getTotalMemory(): int
    {
        // Windows 系统
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            exec('wmic ComputerSystem get TotalPhysicalMemory /value', $output);
            foreach ($output as $line) {
                if (strpos($line, 'TotalPhysicalMemory=') !== false) {
                    return (int)str_replace('TotalPhysicalMemory=', '', trim($line));
                }
            }
        }
        // Linux 系统
        $meminfo = @file_get_contents('/proc/meminfo');
        if ($meminfo) {
            preg_match('/MemTotal:\s+(\d+)\s*kB/', $meminfo, $matches);
            if (isset($matches[1])) {
                return (int)$matches[1] * 1024; // 转成字节
            }
        }
        return 0;
    }

    /**
     * 获取已用内存（字节）
     *
     * @return int
     */
    protected function getUsedMemory(): int
    {
        return $this->getTotalMemory() - $this->getFreeMemory();
    }

    /**
     * 获取可用内存（字节）
     *
     * @return int
     */
    protected function getFreeMemory(): int
    {
        // Windows 系统
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            exec('wmic OS get FreePhysicalMemory /value', $output);
            foreach ($output as $line) {
                if (strpos($line, 'FreePhysicalMemory=') !== false) {
                    return (int)str_replace('FreePhysicalMemory=', '', trim($line)) * 1024;
                }
            }
        }
        // Linux 系统
        $meminfo = @file_get_contents('/proc/meminfo');
        if ($meminfo) {
            preg_match('/MemAvailable:\s+(\d+)\s*kB/', $meminfo, $matches);
            if (isset($matches[1])) {
                return (int)$matches[1] * 1024; // 转成字节
            }
        }
        return 0;
    }
}