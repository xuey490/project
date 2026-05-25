#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Workerman 内存 soak 监控脚本
 *
 * 用于对比 server2 / server3 等入口在低流量下的内存曲线是否平台化。
 *
 * Usage:
 *   php scripts/memory-soak-monitor.php --url=http://127.0.0.1:8000 --label=server2 --interval=60 --duration=3600
 *   php scripts/memory-soak-monitor.php --log=storage/workerman/workerman.log --label=server3 --interval=30
 *
 * 输出 CSV: storage/workerman/soak-{label}-{date}.csv
 */

if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$options = getopt('', [
    'url:',
    'log:',
    'label::',
    'interval::',
    'duration::',
    'output::',
    'help',
]);

if (isset($options['help']) || (empty($options['url']) && empty($options['log']))) {
    echo <<<HELP
Workerman memory soak monitor

Options:
  --url=URL         Poll /_health endpoint (e.g. http://127.0.0.1:8000)
  --log=PATH        Tail workerman.log for [Memory] lines instead of HTTP
  --label=NAME      Run label for CSV column (default: default)
  --interval=SEC    Poll interval in seconds (default: 60)
  --duration=SEC    Stop after N seconds, 0 = run until Ctrl+C (default: 0)
  --output=PATH     CSV output path (default: storage/workerman/soak-{label}-{date}.csv)

Examples:
  php scripts/memory-soak-monitor.php --url=http://127.0.0.1:8000 --label=server2 --interval=60
  php scripts/memory-soak-monitor.php --url=http://127.0.0.1:8000 --label=server3 --interval=60 --duration=86400

HELP;
    exit(0);
}

$basePath   = dirname(__DIR__);
$label      = $options['label'] ?? 'default';
$interval   = max(5, (int) ($options['interval'] ?? 60));
$duration   = (int) ($options['duration'] ?? 0);
$url        = rtrim($options['url'] ?? '', '/');
$logFile    = $options['log'] ?? '';
$output     = $options['output'] ?? sprintf(
    '%s/storage/workerman/soak-%s-%s.csv',
    $basePath,
    preg_replace('/[^a-zA-Z0-9_-]/', '_', $label),
    date('Ymd')
);

$logDir = dirname($output);
if (! is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

$fp = fopen($output, 'a');
if ($fp === false) {
    fwrite(STDERR, "Cannot open output: {$output}\n");
    exit(1);
}

if (filesize($output) === 0) {
    fputcsv($fp, ['timestamp', 'label', 'source', 'memory_mb', 'peak_memory_mb', 'worker_id', 'pid', 'raw']);
}

$startTime = time();
$logOffset = $logFile !== '' && is_file($logFile) ? filesize($logFile) : 0;

echo "[soak] label={$label} interval={$interval}s output={$output}\n";
if ($url !== '') {
    echo "[soak] polling {$url}/_health\n";
}
if ($logFile !== '') {
    echo "[soak] tailing {$logFile}\n";
}

while (true) {
    $now = date('Y-m-d H:i:s');

    if ($url !== '') {
        $health = fetchHealth($url);
        if ($health !== null) {
            fputcsv($fp, [
                $now,
                $label,
                'health',
                $health['memory_mb'] ?? parseMemoryString($health['memory'] ?? ''),
                $health['peak_memory_mb'] ?? '',
                $health['worker_id'] ?? '',
                $health['pid'] ?? '',
                json_encode($health, JSON_UNESCAPED_UNICODE),
            ]);
            fflush($fp);
            $mem = $health['memory_mb'] ?? parseMemoryString($health['memory'] ?? '');
            echo "[{$now}] memory={$mem} MB peak=" . ($health['peak_memory_mb'] ?? 'n/a') . "\n";
        } else {
            echo "[{$now}] health fetch failed\n";
        }
    }

    if ($logFile !== '' && is_file($logFile)) {
        $lines = readNewLogLines($logFile, $logOffset);
        foreach ($lines as $line) {
            if (! preg_match('/\[Memory\].*uses\s+([\d.]+)\s+MB/i', $line, $m)) {
                continue;
            }
            $workerId = '';
            if (preg_match('/Worker\s+#(\d+)/', $line, $wm)) {
                $workerId = $wm[1];
            }
            fputcsv($fp, [$now, $label, 'log', $m[1], '', $workerId, '', $line]);
            fflush($fp);
            echo "[{$now}] log memory={$m[1]} MB worker={$workerId}\n";
        }
    }

    if ($duration > 0 && (time() - $startTime) >= $duration) {
        echo "[soak] duration reached, stopping\n";
        break;
    }

    sleep($interval);
}

fclose($fp);
echo "[soak] CSV saved to {$output}\n";

function fetchHealth(string $baseUrl): ?array
{
    $ctx = stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true],
    ]);
    $json = @file_get_contents($baseUrl . '/_health', false, $ctx);
    if ($json === false) {
        return null;
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : null;
}

function parseMemoryString(string $value): string
{
    if (preg_match('/([\d.]+)/', $value, $m)) {
        return $m[1];
    }
    return '';
}

/** @return list<string> */
function readNewLogLines(string $path, int &$offset): array
{
    clearstatcache(true, $path);
    $size = filesize($path);
    if ($size === false || $size <= $offset) {
        if ($size !== false && $size < $offset) {
            $offset = 0;
        }
        return [];
    }

    $fh = fopen($path, 'rb');
    if ($fh === false) {
        return [];
    }
    fseek($fh, $offset);
    $chunk = stream_get_contents($fh);
    fclose($fh);
    $offset = $size;

    if ($chunk === false || $chunk === '') {
        return [];
    }

    return array_filter(explode("\n", trim($chunk)));
}
