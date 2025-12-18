<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\DI\ContextBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContextInitMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // 🔥 核心：把 request 放入上下文容器，key 必须和注解里的 Context('request') 一致
        ContextBag::set('request', $request);

        // 如果你将来还要注入用户，也可以在这里或者 Auth中间件里 set
        // ContextBag::set('user', $user);

        try {
            return $next($request);
        } finally {
            // (可选) 请求结束后清理，防止内存泄漏（特别是 Swoole 环境）
            ContextBag::clear();
        }
    }
}