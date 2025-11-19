<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // dump('--- 进入 LogMiddleware (中间件) ---');
        // $id = $request->getSession();
        // 模拟鉴权：如果没有登录，返回401
        // if (!$id->get('user_id')) {
        //    return new Response('<h1>401 Unauthorized: Please login first</h1>', 401);
        // }

        // 鉴权通过，执行下一个中间件/控制器
        return $next($request);
        // dump('--- 退出 LogMiddleware (中间件) ---');
    }
}
