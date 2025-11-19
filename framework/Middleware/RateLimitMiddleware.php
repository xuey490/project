<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    private int $maxRequests = 10;

    private int $period = 60; // seconds

    // private string $cacheDir;

    private array $except =[];

    public function __construct(private array $config, private string $cacheDir)
    {
        $this->maxRequests = $config['maxRequests'] ?? $this->maxRequests;
        $this->period      = $config['period']      ?? $this->period;
        $this->except      = $config['except']      ?? $this->except;
        $this->cacheDir    = rtrim($cacheDir, '/') . '/';
        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * 处理请求并应用限流
     *
     * @param callable $next 接收 Request 并返回 Response 的下一个处理器
     */
    public function handle(Request $request, callable $next): Response
    {
        foreach ($this->except as $pattern) {
            if ($this->matchPath($request->getPathInfo(), $pattern)) {
                return $next($request);
            }
        }

        $ip  = $request->getClientIp() ?: 'unknown';
        $key = $this->cacheDir . 'rate_limit_' . md5($ip);
        $now = time();

        $data = ['count' => 1, 'time' => $now];

        if (file_exists($key)) {
            $content = @file_get_contents($key);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if ($decoded && $decoded['time'] > $now - $this->period) {
                    if ($decoded['count'] >= $this->maxRequests) {
                        // === 限流触发 ===
                        $retryAfter = $this->period - ($now - $decoded['time']);
                        return $this->buildRateLimitResponse($request, $retryAfter);
                    }
                    $data['count'] = $decoded['count'] + 1;
                }
            }
        }

        file_put_contents($key, json_encode($data));
        return $next($request);
    }

    private function buildRateLimitResponse(Request $request, int $retryAfter): Response
    {
        $message = "请求过于频繁，请 {$retryAfter} 秒后再试。";

        // 判断是否为 API 请求
        if ($request->isXmlHttpRequest()
            || strpos($request->headers->get('Accept', ''), 'application/json') !== false) {
            $response = new JsonResponse([
                'success'     => false,
                'error'       => 'rate_limit_exceeded',
                'message'     => $message,
                'retry_after' => $retryAfter,
                'limit'       => $this->maxRequests,
                'period'      => $this->period,
            ], 429);
        } else {
            // Web 页面
            $html     = "<h2>⚠️ {$message}</h2><p>系统限制：每 {$this->period} 秒最多 {$this->maxRequests} 次请求。</p>";
            $response = new Response($html, 429, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        // 将整数转换为字符串
        $response->headers->set('Retry-After', (string) $retryAfter);
        $response->headers->set('X-RateLimit-Limit', (string) $this->maxRequests);
        $response->headers->set('X-RateLimit-Remaining', '0'); // 直接使用字符串
        $response->headers->set('X-RateLimit-Reset', (string) (time() + $retryAfter));

        return $response;
    }

    private function matchPath(string $path, string $pattern): bool
    {
        $regex = str_replace('\*', '.*', preg_quote($pattern, '#'));
        return (bool) preg_match('#^' . $regex . '$#', $path);
    }
}
