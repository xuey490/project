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

/**
 * DebugMiddleware.
 *
 * 调试中间件（仅在 debug 模式启用）：
 *  - 打印 Request Headers、Query、Body 参数
 *  - 打印 Response Headers 与状态码
 *  - 自动检测运行环境（FPM / Workerman）
 *  - 支持 PSR-15 风格中间件（handle/next）
 */
class DebugMiddleware
{
    /** @var bool 是否启用调试输出 */
    protected bool $debug;

    public function __construct(bool $debug = true)
    {
        $this->debug = $debug;
    }

    /**
     * 中间件入口.
     */
    public function handle(Request $request, \Closure $next): Response
    {
        // === 请求阶段 ===
        if ($this->debug) {
            $this->dumpRequest($request);
        }

        // === 执行下一个中间件 / 控制器 ===
        $response = $next($request);

        // === 响应阶段 ===
        if ($this->debug) {
            $this->dumpResponse($response);
        }

        return $response;
    }

    /**
     * 打印请求信息.
     */
    protected function dumpRequest(Request $request): void
    {
        echo "\n==================== [REQUEST DEBUG] ====================\n";
        echo 'Method: ' . $request->getMethod() . "\n";
        echo 'Path:   ' . $request->getPathInfo() . "\n";
        echo 'Client: ' . $request->getClientIp() . "\n";

        echo "\n--- Headers ---\n";
        foreach ($request->headers->all() as $key => $values) {
            echo sprintf("%s: %s\n", $key, implode(', ', $values));
        }

        echo "\n--- Query Params ---\n";
        if ($request->query->all()) {
            print_r($request->query->all());
        } else {
            echo "(none)\n";
        }

        echo "\n--- POST Body ---\n";
        if ($request->request->all()) {
            print_r($request->request->all());
        } elseif ($raw = $request->getContent()) {
            echo $raw . "\n";
        } else {
            echo "(empty)\n";
        }

        echo "==========================================================\n\n";
    }

    /**
     * 打印响应信息.
     */
    protected function dumpResponse(Response $response): void
    {
        echo "\n==================== [RESPONSE DEBUG] ====================\n";
        echo 'Status: ' . $response->getStatusCode() . "\n";

        echo "\n--- Headers ---\n";
        foreach ($response->headers->allPreserveCase() as $key => $values) {
            foreach ($values as $v) {
                echo sprintf("%s: %s\n", $key, $v);
            }
        }

        echo "==========================================================\n\n";
    }
}
