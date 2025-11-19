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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CircuitBreakerMiddleware
{
    private int $failureThreshold = 3; // 重试次数，如果超过次数，直接调整到 return new Response('服务熔断，暂不可用！', 503); 这行

    private int $timeout = 10; // 秒

    private string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = rtrim(str_replace('\\', '/', $cacheDir), '/') . '/';
        if (! is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * 处理请求，实现熔断逻辑.
     *
     * @param callable $next 下一个中间件或控制器
     */
    public function handle(Request $request, callable $next): Response
    {
        $service = 'default'; // 可扩展为按路由/服务名区分
        $key     = $this->cacheDir . 'breaker_' . md5($service);
        $now     = time();

        // 读取当前熔断器状态
        $state = ['status' => 'closed', 'failures' => 0];
        if (file_exists($key)) {
            $content = @file_get_contents($key);
            if ($content !== false) {
                $state = json_decode($content, true) ?: $state;
            }
        }

        // 检查是否处于 "open" 状态且未超时
        if ($state['status'] === 'open') {
            if (isset($state['opened_at']) && $state['opened_at'] + $this->timeout > $now) {
                // 熔断中，直接返回 503，超过次数，直接不可用
                return new Response('服务熔断，暂不可用！', 503);
                // return $this->buildServiceUnavailableResponse($request);
            }
            // 超时，进入 half-open 状态，允许一次试探
            $state = ['status' => 'half-open', 'attempts' => 1];
            file_put_contents($key, json_encode($state));
        }

        try {
            $response = $next($request);

            // 判断是否为服务端错误（可自定义）
            if (in_array($response->getStatusCode(), [500, 502, 503, 504], true)) {
                throw new \RuntimeException('Upstream service error');
            }

            // 成功：重置为 closed
            file_put_contents($key, json_encode([
                'status'   => 'closed',
                'failures' => 0,
            ]));

            return $response;
        } catch (\Throwable $e) {
            // 记录失败
            $failures = ($state['status'] === 'closed' ? ($state['failures'] ?? 0) : 0) + 1;

            if ($failures >= $this->failureThreshold) {
                // 触发熔断
                file_put_contents($key, json_encode([
                    'status'    => 'open',
                    'opened_at' => $now,
                ]));
            } else {
                // 继续累积失败
                file_put_contents($key, json_encode([
                    'status'   => 'closed',
                    'failures' => $failures,
                ]));
            }

            // 返回 503 响应（不抛出异常，避免中断中间件链）
            return $this->buildServiceUnavailableResponse($request);
        }
    }

    /**
     * 构建友好的 503 响应.
     */
    private function buildServiceUnavailableResponse(Request $request): Response
    {
        $message = '服务暂时不可用，请稍后再试。';

        // 判断是否为 API 请求
        if ($request->isXmlHttpRequest()
            || strpos($request->headers->get('Accept', ''), 'application/json') !== false) {
            return new JsonResponse([
                'success' => false,
                'error'   => 'service_unavailable',
                'message' => $message,
                'details' => '系统正在保护性熔断中，稍后自动恢复。',
            ], 503);
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>服务不可用</title>
    <style>
        body { font-family: system-ui, sans-serif; text-align: center; padding: 50px; background: #f9f9f9; }
        .box { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e67e22; font-size: 1.8em; margin-bottom: 20px; }
        p { color: #555; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔧 服务暂时不可用</h1>
        <p>{$message}</p>
        <p>系统已自动启用熔断机制，预计几秒后恢复。</p>
    </div>
</body>
</html>
HTML;

        return new Response($html, 503, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
