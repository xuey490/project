<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Middlewares;

use Framework\Attributes\Auth;
use Framework\Security\AuthGuard;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use ReflectionClass;
use ReflectionMethod;

/**
 * AuthMiddleware 负责：
 * - 提取 JWT
 * - 验证签名、过期、角色权限
 * - 自动续期 Token
 */
class AuthMiddleware
{
    private AuthGuard $authGuard;

    public function __construct(AuthGuard $authGuard)
    {
        $this->authGuard = $authGuard;
    }

    public function handle(Request $request, callable $next): Response
    {
        try {
			
            // 判断当前路由是否需要验证
            $route = $request->attributes->get('_route', []);
            $controllerClass = $route['controller'] ?? null;
            $action = $route['method'] ?? null;

            $needAuth = false;	// 默认不需要认证
            $requiredRoles = [];
			
			if(isset($route['params']['_auth']) && $route['params']['_auth'] == true) { //注解路由上的auth，role
				$needAuth = true;
				$requiredRoles = $route['params']['_roles'] ?? [];
			}else if ($controllerClass && $action) {

				$refClass = new \ReflectionClass($controllerClass);
				
				// ✅ 1.检查类上的 Attribute
				foreach ($refClass->getAttributes(Auth::class) as $attr) {
					$instance = $attr->newInstance();
					if ($instance->required) {
						$needAuth = true;
						$requiredRoles = $instance->roles ?? [];
						break; // 类上只取第一个 #[Auth(...)]
					}
				}

				// 2.类 DocBlock
				$doc = $refClass->getDocComment();
				if ($doc) {
					if (preg_match('/@auth\s+(true|false)/i', $doc, $m)) {
						$needAuth = strtolower($m[1]) === 'true';
					}
					if (preg_match('/@role\s+([^\s]+)/i', $doc, $m)) {
						$requiredRoles = array_map('strtolower', array_map('trim', explode(',', $m[1])));
					}
				}

                // ✅ 支持 PHP Attribute
                $refMethod = new \ReflectionMethod($controllerClass, $action);

                foreach ($refMethod->getAttributes() as $attr) {
                    if ($attr->getName() === 'Framework\\Attributes\\Auth') {
                        $args = $attr->getArguments();
                        $needAuth = $args['required'] ?? true;
                        $requiredRoles = $args['roles'] ?? [];
						#break; // 方法级优先
                    }
                }

                // ✅ 4.方法支持老式 DocBlock 注释 @auth true @role admin,editor
                if (!$needAuth) {
                    $doc = $refMethod->getDocComment();
                    if ($doc && preg_match('/@auth\s+true/i', $doc)) {
                        $needAuth = true;
                    }
                    if ($doc && preg_match('/@role\s+([^\s]+)/i', $doc, $m)) {
                        $requiredRoles = array_map('trim', explode(',', $m[1]));
                    }
                }
            }

            if ($needAuth) {
                $result = $this->authGuard->check($request, $requiredRoles);
				// 如果返回的是 Response，直接中止链条
				if ($result instanceof Response) {
					return $result;
				}
                $request->attributes->set('user', $result);
            }

            // 继续执行下一个中间件或控制器
            $response = $next($request);

            // 如果有自动续期的 Token，则在响应头返回新的 JWT
            if ($this->authGuard->hasRefreshedToken()) {
                $response->headers->set('X-Token-Refresh', $this->authGuard->getRefreshedToken());
            }

			return $response;

        } catch (Throwable $e) {
            return new Response(json_encode([
                'error' => 'Unauthorized',
                'message' => $e->getMessage(),
            ]), 401, ['Content-Type' => 'application/json']);
        }
    }
	
    private static function unauthorized(string $msg): Response
    {
        return new Response(json_encode([
            'error' => 'unauthorized',
            'message' => $msg,
            'code' => 401,
        ]), 401, ['Content-Type' => 'application/json']);
    }
	
	public function handle1(Request $request, callable $next): Response
	{
		dump('--- 进入 AuthMiddleware (中间件) ---');
		$token = app('cookie')->get($request , 'token') ?? $request->headers->get('Authorization')?->replace('Bearer ', '');

		if (!$token) {
			 return new Response('<h1>401 Unauthorized: Please login first</h1>', 401);
			//return new \Symfony\Component\HttpFoundation\RedirectResponse('/jwt/issue', 301);
		}

		try {
			$parsed = app('jwt')->parse($token); // 内部已包含 JWT 验证 + Redis 检查
			$request->attributes->set('user_claims', $parsed->claims()->all());
		} catch (\Exception $e) {
			 return new Response('<h1>401 Token is expired</h1>', 401);
			//return new \Symfony\Component\HttpFoundation\RedirectResponse('/jwt/issue', 301);
		}

        // 鉴权通过，执行下一个中间件/控制器
        return $next($request);
        # dump('--- 退出 AuthMiddleware (中间件) ---');
	}

}
