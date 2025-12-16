<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use App\Middlewares\AuthMiddleware;
use Framework\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MiddlewareDispatcher.
 *
 * 自动调度中间件，包括：
 * - 全局中间件
 * - 路由中间件
 * - 自动扫描控制器 @auth true / #[Auth] 动态添加 AuthMiddleware
 */
class MiddlewareDispatcher
{
    private Container $container;

    // 全局中间件（所有请求都会执行）
    private array $globalMiddleware = [
        MethodOverrideMiddleware::class,
        CorsMiddleware::class,
        CsrfTokenGenerateMiddleware::class,
		RateLimitMiddleware::class,
        #CircuitBreakerMiddleware::class, //熔断中间件，正式环境使用，开发环境直接溢出错误堆栈
        IpBlockMiddleware::class,
        XssFilterMiddleware::class,
        CsrfProtectionMiddleware::class,
        RefererCheckMiddleware::class,
        CookieConsentMiddleware::class,
        DebugMiddleware::class,
        // 添加日志、CORS、熔断器、限流器，xss、 ip block、Debug等全局中间件
    ];


    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 调度中间件：先执行全局中间件，再执行路由中间件.
     * @param callable $next 下一个中间件/控制器
     */
    public function dispatch(Request $request, callable $destination): Response
    {
        // 1. 获取路由中间件
        // 此时 $request->attributes 已经由 UrlMatcher 填充完毕
        $rawRouteMiddleware = $request->attributes->get('_middleware', []);
		$currentRouteName = $request->attributes->get('_route' , []); 
		#dump($rawRouteMiddleware);
        // 2. 拍平并去重
        $flattenedRouteMiddleware = $this->flattenArray($rawRouteMiddleware);
        
        // 移除全局已存在的，避免重复执行
        $uniqueRouteMiddleware = array_values(array_diff(
            $flattenedRouteMiddleware,
            $this->globalMiddleware
        ));
		
		

        // 3. 处理 Auth 逻辑
        // 直接读取 UrlMatcher 注入的 _auth 和 _roles
        $needsAuth = $request->attributes->get('_auth', false);
        
        // 如果需要认证，且 AuthMiddleware 不在列表中，则强制添加
        if ($needsAuth) {
            if (!in_array(AuthMiddleware::class, $uniqueRouteMiddleware) && 
                !in_array(AuthMiddleware::class, $this->globalMiddleware)) {
                // 建议将 Auth 加在路由中间件的最前面
                array_unshift($uniqueRouteMiddleware, AuthMiddleware::class);
            }
        }

        // 4. 合并所有中间件：全局 -> 路由
        $allMiddleware = array_merge($this->globalMiddleware, $uniqueRouteMiddleware);
		
		#dump($allMiddleware);

        // 5. 构建洋葱模型 (反向包装)
        $middlewareChain = $destination;
        
        foreach (array_reverse($allMiddleware) as $middlewareClass) {
            if (empty($middlewareClass)) {
                continue;
            }

            // 从容器解析
            $middleware = $this->container->get($middlewareClass);

            // 包装
            $middlewareChain = function ($req) use ($middleware, $middlewareChain) {
                return $middleware->handle($req, $middlewareChain);
            };
        }

        // 6. 启动链条
        return $middlewareChain($request);
    }
	
    /**
     * 将多维数组递归“拍平”成一维数组.
     */
    private function flattenArray(array $array): array
    {
        $result = [];
        array_walk_recursive($array, function ($value) use (&$result) {
            $result[] = $value;
        });
        return $result;
    }

}
