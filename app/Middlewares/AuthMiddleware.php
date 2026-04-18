<?php

declare(strict_types=1);

namespace App\Middlewares;

use Framework\Attributes\Auth;
use Framework\Basic\BaseJsonResponse;
use Framework\Tenant\TenantContext;
use Framework\Utils\JwtFactory;
use App\Models\SysUser;
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
        if (strtoupper((string) $request->getMethod()) === 'OPTIONS') {
            return $next($request);
        }

        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        // 白名单之外的所有请求都必须验证 token
        app('db');

        $accessToken = $this->extractAccessToken($request);
        if (! $accessToken) {
            $this->debugLog('[AuthMiddleware] 401 - 无access_token', [
                'path' => $request->getPathInfo(),
                'method' => $request->getMethod(),
                'auth_header' => $request->headers->get('Authorization'),
                'cookie_access_token' => $request->cookies->has('access_token') ? 'exists' : 'missing',
            ]);
            return BaseJsonResponse::unauthorized('请先登录');
        }


        /** @var JwtFactory $jwt */
        $jwt = app('jwt');

        try {
            // 1️⃣ 严格校验 access token
            $parsed = $jwt->parseForAccess($accessToken);
            $claims = $parsed->claims();

            $uid  = (int) $claims->get('uid');
            $role = $claims->get('role') ?? 'user';
            $tenantId = (int) ($claims->get('tenant_id') ?? 0);
            $exp  = $claims->get('exp')->getTimestamp();

            // 2️⃣ 设置租户上下文（多租户隔离）
            TenantContext::setTenantIdToRequest($request, $tenantId > 0 ? $tenantId : null);

            // 3️⃣ 角色校验（仅当路由明确声明了角色限制时才校验）
            $attributes = $request->attributes->get('_attributes', []);
			

            /** @var Auth|null $auth */
            $auth = $attributes[Auth::class] ?? null;
            $routeInfo = $request->attributes->get('_route');

            $routeRoles = $request->attributes->get('_roles');
            if (!is_array($routeRoles) && is_array($routeInfo) && isset($routeInfo['__meta_flat']['_roles']) && is_array($routeInfo['__meta_flat']['_roles'])) {
                $routeRoles = $routeInfo['__meta_flat']['_roles'];
            }
            $routeRoles = is_array($routeRoles) ? $routeRoles : [];

            if ((! empty($auth?->roles) && ! in_array($role, $auth->roles, true))
                || (! empty($routeRoles) && ! in_array($role, $routeRoles, true))) {
                $this->debugLog('[AuthMiddleware] 403 - 角色不匹配', [
                    'path' => $request->getPathInfo(),
                    'user_role' => $role,
                    'required_roles' => $auth?->roles ?? $routeRoles,
                ]);
                return BaseJsonResponse::error('无权限访问！', 403);
            }

            // 4️⃣ 自动续期（失败不影响当前请求）
            $remaining = $exp - time();
            if ($remaining < $this->refreshThreshold) {
                $this->tryRefresh($request, $jwt, $uid);
            }

            //error_log(json_encode($claims->all()));

            // 5️⃣ 注入用户上下文
            $request->attributes->set('user', [
                'id'        => $uid,
                'username'  => $claims->get('name') ?? '',
                'role'      => $role,
                'tenant_id' => $tenantId,
            ]);

            $request->attributes->set('user_claims', $claims->all());

            // 6️⃣ 验证用户状态（使用数据库字段：status 和 deleted_at）
            //$currentUser = SysUser::with(['dept', 'roles', 'roles.depts', 'roles.menus'])->find($uid);
            $currentUser = SysUser::with(['dept', 'roles', 'roles.dataScopeDepts', 'roles.menus'])->find($uid);
            if ($currentUser) {
                // 检查用户是否被禁用（status = 0 表示禁用）
                if ($currentUser->isDisabled()) {
                    return BaseJsonResponse::unauthorized('账号已被禁用');
                }

                // 检查用户是否被软删除
                if ($currentUser->trashed()) {
                    return BaseJsonResponse::unauthorized('账号已被删除');
                }

                $request->attributes->set('current_user', $currentUser);
            } else {
                return BaseJsonResponse::unauthorized('用户不存在');
            }

        } catch (\Throwable $e) {
            $this->debugLog('[AuthMiddleware] 401 - parseForAccess 异常', [
                'path' => $request->getPathInfo(),
                'error' => get_class($e) . ': ' . $e->getMessage(),
                'token_prefix' => substr($accessToken, 0, 30) . '...',
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);
            // 开发环境返回详细错误信息，生产环境返回通用提示
            $debug = env('APP_DEBUG', false);
            $msg = $debug
                ? '登录已过期或无效: ' . $e->getMessage()
                : '登录已过期或无效';
            return BaseJsonResponse::unauthorized($msg);
        }

        // 7️⃣ 执行后续逻辑
        $response = $next($request);

        $this->writeCookiesIfNeeded($request, $response);

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

    protected function isAdminRequest(Request $request, mixed $routeInfo): bool
    {
        // 简单判定：Controller 命名空间 或者 路径前缀
        $controller = $request->attributes->get('_controller');
        if (is_string($controller) && str_starts_with($controller, 'App\\Controllers\\Admin\\')) {
            return true;
        }
        
        $path = $request->getPathInfo();
        if (str_starts_with($path, '/system/')) {
            return true;
        }

        return false;
    }

    protected function isWhitelisted(Request $request): bool
    {
        $path = (string) $request->getPathInfo();

        // 精确匹配白名单路径
        $exactWhitelist = [
            '/api/core/login',
            '/api/core/logout',
            '/api/core/captcha',
            '/api/core/refresh',
            '/api/core/tenants-by-username',
        ];

        if (in_array($path, $exactWhitelist, true)) {
            return true;
        }

        // 验证码接口（前缀匹配）
        if (str_starts_with($path, '/api/core/captcha')) {
            return true;
        }

        return false;
    }

    /**
     * 临时诊断日志（排查401后可删除）
     */
    protected function debugLog(string $message, array $context = []): void
    {
        $logFile = BASE_PATH . '/runtime/logs/auth_debug.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $time = date('Y-m-d H:i:s');
        $line = "[{$time}] {$message}" . ($context ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '') . PHP_EOL;
        @file_put_contents($logFile, $line, FILE_APPEND);
    }
}
