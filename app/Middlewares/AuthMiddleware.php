<?php

declare(strict_types=1);

namespace App\Middlewares;

use Framework\Attributes\Auth;
use Framework\Basic\BaseJsonResponse;
use Framework\Utils\JwtFactory;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * 剩余 < N 秒才尝试 refresh
     * 推荐 300（5 分钟）
     */
    protected int $refreshThreshold = 300;

    public function handle(Request $request, callable $next): Response
    {
        /** @var array<class-string,object> $attributes */
        $attributes = $request->attributes->get('_attributes', []);
        /** @var Auth|null $auth */
        $auth = $attributes[Auth::class] ?? null;
		
		$routeInfo = $request->attributes->get('_route');        
		#dump($routeInfo['params']['_roles']);

		$legacyAuth = $request->attributes->get('_auth', false);
		$needAuth   = ($auth && $auth->required) || $legacyAuth === true;

        if (! $needAuth) {
            return $next($request);
        }

        $accessToken = $this->extractAccessToken($request);
        if (! $accessToken) {
            return $this->unauthorized('请先登录');
        }
		

        /** @var JwtFactory $jwt */
        $jwt = app('jwt');

        try {
            // 1️⃣ 严格校验 access token
            $parsed = $jwt->parseForAccess($accessToken);
            $claims = $parsed->claims();
			

            $uid  = (int) $claims->get('uid');
            $role = $claims->get('role') ?? 'user';
            $exp  = $claims->get('exp')->getTimestamp();

            // 2️⃣ 角色校验
            if ((! empty($auth?->roles) && ! in_array($role, $auth->roles, true))  
				||  (!empty($routeInfo['params']['_roles']) && ! in_array($role, $routeInfo['params']['_roles'], true)) ) {
                return $this->forbidden('无权限访问！');
            }

            // 3️⃣ 自动续期（失败不影响当前请求）
            $remaining = $exp - time();
			
			#dump($remaining);
			#dump($this->refreshThreshold);
            if ($remaining < $this->refreshThreshold) {
                $this->tryRefresh($request, $jwt, $uid);
            }
			

            // 4️⃣ 注入用户上下文
            $request->attributes->set('user', [
                'id'   => $uid,
                'role' => $role,
            ]);

            $request->attributes->set('user_claims', $claims->all());

        } catch (\Throwable $e) {
			dump($e->getMessage());
            return $this->unauthorized('登录已过期或无效');
        }

        // 5️⃣ 执行后续逻辑
        
		$response = $next($request);
		
		$this->writeCookiesIfNeeded($request, $response);
		
		//dd($response->headers->getCookies());

        // 6️⃣ 如果有新 token，写回 Cookie
        return $response;
    }

    /**
     * 尝试刷新 token（只尝试一次，失败即放弃）
     */
    protected function tryRefresh(Request $request, JwtFactory $jwt, int $uid): void
    {
        $refreshToken = $request->cookies->get('refresh_token');
        if (! $refreshToken) {
            return;
        }
		#dump($refreshToken);

        // 避免同一请求内重复 refresh
        if ($request->attributes->get('_refresh_attempted')) {
            return;
        }
        $request->attributes->set('_refresh_attempted', true);

        try {
            // 1️⃣ rotation refresh token（一次性）
            $newRefresh = $jwt->rotateRefreshToken($refreshToken);

            // 2️⃣ 签发新 access token（直接用已知 uid）
            $access = $jwt->issue(['uid' => $uid]);

            // 3️⃣ 暂存，交给 Response 阶段写 Cookie
            $request->attributes->set('_new_access_token', $access);
            $request->attributes->set('_new_refresh_token', $newRefresh);

        } catch (\Throwable $e) {
            // 静默失败：并发 / 已被使用 / 过期
			
			#dump('Refresh Error: ' . $e->getMessage());
        }
    }

    /**
     * 将新 token 写入 Response Cookie
     */
    protected function writeCookiesIfNeeded(Request $request, Response $response): Response
    {
		
		$isHttps = $request->isSecure();

		$sameSite = $isHttps ? 'Strict' : 'Lax';
		
        if ($request->attributes->has('_new_access_token')) {
            $access = $request->attributes->get('_new_access_token');

            $response->headers->setCookie(
                new Cookie(
                    'access_token',
                    $access['token'],
                    time() + $access['ttl'],
                    '/',
                    null,
                    $isHttps,   // secure
                    true,   // httpOnly
                    false,
                    $sameSite
                )
            );
        }

        if ($request->attributes->has('_new_refresh_token')) {
            $refresh = $request->attributes->get('_new_refresh_token');

            $response->headers->setCookie(
                new Cookie(
                    'refresh_token',
                    $refresh,
                    time() + 86400 * 7,
                    '/',
                    null,
                    $isHttps,
                    true,
                    false,
                    $sameSite
                )
            );
        }

        return $response;
    }

    protected function extractAccessToken(Request $request): ?string
    {
        $header = $request->headers->get('Authorization');
        if (is_string($header) && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return $request->cookies->get('access_token');
    }

    private function unauthorized(string $msg): Response
    {
        return new Response(json_encode([
            'error' => 'unauthorized',
            'message' => $msg,
            'code' => 401,
        ]), 401, ['Content-Type' => 'application/json']);
    }
	
	
    private function forbidden(string $msg): Response
    {
        return new Response(json_encode([
            'error' => 'unauthorized',
            'message' => $msg,
            'code' => 403,
        ]), 403, ['Content-Type' => 'application/json']);
    }
}
