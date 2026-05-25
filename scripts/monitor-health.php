#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Workerman 健康监控与告警脚本
 *
 * 读取 /_health 或 storage/workerman/health.json / memory-history.jsonl，
 * 在内存超过阈值或持续上升时输出告警。
 *
 * Usage:
 *   php scripts/monitor-health.php --url=http://127.0.0.1:8000
 *   php scripts/monitor-health.php --file=storage/workerman/health.json --threshold=400
 *   php scripts/monitor-health.php --history=storage/workerman/memory-history.jsonl --slope-window=30 --slope-threshold=0.5
 */

if (php_sapi_name() !== 'cli') {
    exit("CLI only\n");
}

$options = getopt('', [
    'url:',
    'file:',
    'history:',
    'threshold::',
    'slope-window::',
    'slope-threshold::',
    'json',
    'help',
]);

if (isset($options['help'])) {
    echo <<<HELP
Workerman health monitor & alert

Options:
  --url=URL              Fetch live /_health JSON
  --file=PATH            Read local health.json
  --history=PATH         Analyze memory-history.jsonl for rising trend
  --threshold=MB         Alert if memory_mb exceeds (default: 400)
  --slope-window=N       Last N history samples for slope (default: 20)
  --slope-threshold=MB   Alert if avg rise per sample exceeds (default: 0.5 MB)
  --json                 Output JSON alert payload

Exit codes: 0 = OK, 1 = alert triggered, 2 = error

HELP;
    exit(0);
}

$threshold     = (float) ($options['threshold'] ?? 400);
$slopeWindow   = max(3, (int) ($options['slope-window'] ?? 20));
$slopeThreshold = (float) ($options['slope-threshold'] ?? 0.5);
$alerts        = [];

$health = null;
if (! empty($options['url'])) {
    $health = fetchHealth(rtrim($options['url'], '/'));
} elseif (! empty($options['file'])) {
    $raw = @file_get_contents($options['file']);
    $health = $raw !== false ? json_decode($raw, true) : null;
}

if ($health === null && empty($options['history'])) {
    fwrite(STDERR, "No health data. Provide --url, --file, or --history\n");
    exit(2);
}

if (is_array($health)) {
    $memoryMb = (float) ($health['memory_mb'] ?? parseMemoryString((string) ($health['memory'] ?? '0')));
    if ($memoryMb > $threshold) {
        $alerts[] = [
            'type'    => 'memory_threshold',
            'message' => sprintf('Memory %.2f MB exceeds threshold %.2f MB', $memoryMb, $threshold),
            'memory_mb' => $memoryMb,
            'threshold_mb' => $threshold,
            'health'  => $health,
        ];
    }
}

if (! empty($options['history']) && is_file($options['history'])) {
    $samples = readLastMemorySamples($options['history'], $slopeWindow);
    if (count($samples) >= 3) {
        $slope = computeSlope($samples);
        if ($slope > $slopeThreshold) {
            $alerts[] = [
                'type'    => 'memory_slope',
                'message' => sprintf(
                    'Memory rising ~%.3f MB/sample over last %d samples (threshold %.3f)',
                    $slope,
                    count($samples),
                    $slopeThreshold
                ),
                'slope_mb_per_sample' => $slope,
                'samples' => count($samples),
            ];
        }
    }
}

if (isset($options['json'])) {
    echo json_encode(['ok' => empty($alerts), 'alerts' => $alerts], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} elseif (empty($alerts)) {
    echo "OK\n";
} else {
    foreach ($alerts as $alert) {
        echo "[ALERT] {$alert['message']}\n";
    }
}

exit(empty($alerts) ? 0 : 1);

function fetchHealth(string $baseUrl): ?array
{
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $json = @file_get_contents($baseUrl . '/_health', false, $ctx);
    if ($json === false) {
        return null;
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : null;
}

function parseMemoryString(string $value): float
{
    if (preg_match('/([\d.]+)/', $value, $m)) {
        return (float) $m[1];
    }
    return 0.0;
}

/** @return list<float> */
function readLastMemorySamples(string $path, int $limit): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }
    $lines = array_slice($lines, -$limit);
    $values = [];
    foreach ($lines as $line) {
        $row = json_decode($line, true);
        if (! is_array($row)) {
            continue;
        }
        if (isset($row['memory_mb'])) {
            $values[] = (float) $row['memory_mb'];
        }
    }
    return $values;
}

/** @param list<float> $samples */
function computeSlope(array $samples): float
{
    $n = count($samples);
    if ($n < 2) {
        return 0.0;
    }
    return ($samples[$n - 1] - $samples[0]) / ($n - 1);
}
