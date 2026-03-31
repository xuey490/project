<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Models\SysUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    protected array $exactWhiteList = [
        '/api/auth/login',
        '/api/auth/logout',
        '/api/auth/refresh',
        '/api/captcha',
        '/api/public',
        '/api/core/login',
        '/api/core/logout',
        '/api/core/captcha',
        '/api/core/refresh',
        '/api/core/tenants-by-username',
        '/api/core/user-tenants',
        '/api/core/switch-tenant',
        '/api/core/system/user',
        '/api/core/system/menu',
        '/api/core/system/permissions',
        '/api/core/system/dictAll',
        '/api/core/user/modifyPassword',
        '/api/core/user/updateInfo',
        '/api/core/system/uploadImage',
        '/api/core/system/uploadFile',
        '/api/core/system/chunkUpload',
    ];

    protected array $prefixWhiteList = [
        '/api/core/captcha',
    ];

    protected array $guardedPrefixes = [
        '/api/system/',
        '/api/tool/',
        '/api/core/system/',
        '/api/core/server/',
        '/api/core/redis/',
        '/api/core/database/',
        '/api/core/config/',
        '/api/core/configGroup/',
        '/api/core/email/',
        '/api/core/console/',
        '/api/safeguard/',
    ];

    public function handle(Request $request, callable $next): Response
    {
        $path = $request->getPathInfo();

        if (strtoupper((string) $request->getMethod()) === 'OPTIONS' || $this->isWhiteListed($path)) {
            return $next($request);
        }

        // --- 防御多路径“权限越狱”漏洞 ---
        $routeInfo = $request->attributes->get('_route');
        if (is_array($routeInfo)) {
            $controller = $routeInfo['controller'] ?? null;
            $method = $routeInfo['method'] ?? null;
            
            // 约定路由（Auto Route）匹配的标志是：参数中不包含 _route 键
            // 框架自带的 #[Route] 注解（Symfony URL Matcher）匹配时会自带 _route 键
            $isAutoRoute = !isset($routeInfo['params']['_route']);
            
            if ($isAutoRoute && $controller && $method) {
                try {
                    $rm = new \ReflectionMethod($controller, $method);
                    // 探测目标方法是否已被显式的路由注解保护
                    $hasRouteAttr = !empty($rm->getAttributes(\Framework\Attributes\Route::class, \ReflectionAttribute::IS_INSTANCEOF)) ||
                                    !empty($rm->getAttributes(\Framework\Attributes\Routes\BaseMapping::class, \ReflectionAttribute::IS_INSTANCEOF));
                    
                    if ($hasRouteAttr) {
                        return $this->forbidden('非法访问：此接口受显式路由保护，禁止通过约定路径绕过权限。');
                    }
                } catch (\Throwable $e) {
                    // 忽略异常
                }
            }
        }
        // ----------------------------------

        $sysUser = $this->resolveUser($request);
        if (!$sysUser) {
            return $this->unauthorized('请先登录');
        }

        if ($sysUser->isDisabled()) {
            return $this->forbidden('用户已被禁用');
        }

        if ($sysUser->isSuperAdmin()) {
            return $next($request);
        }

        if (!$this->shouldGuard($path)) {
            return $next($request);
        }

        if (!$this->checkPermission($sysUser, $request)) {
            return $this->forbidden('无权限访问');
        }

        return $next($request);
    }

    protected function resolveUser(Request $request): ?SysUser
    {
        $currentUser = $request->attributes->get('current_user');
        if ($currentUser instanceof SysUser) {
            return $currentUser;
        }

        $user = $request->attributes->get('user');
        $userId = (int) ($user['id'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        return SysUser::find($userId);
    }

    protected function isWhiteListed(string $path): bool
    {
        if (in_array($path, $this->exactWhiteList, true)) {
            return true;
        }

        foreach ($this->prefixWhiteList as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    protected function shouldGuard(string $path): bool
    {
        foreach ($this->guardedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    protected function checkPermission(SysUser $user, Request $request): bool
    {
        $userSlugs = $user->getPermissions();
        
        // 1. 优先检查 #[Permission] 注解
        $permissionAttr = $this->getPermissionsFromAttribute($request);
        if ($permissionAttr !== null) {
            $requiredSlugs = $permissionAttr->slugs;
            if (empty($requiredSlugs)) {
                return true; // 如果注解传了空数组，表示无权限要求，直接放行
            }

            if ($permissionAttr->mode === 'AND') {
                foreach ($requiredSlugs as $slug) {
                    if (!in_array($slug, $userSlugs, true)) {
                        return false;
                    }
                }
                return true;
            } else { // 'OR' mode
                foreach ($requiredSlugs as $slug) {
                    if (in_array($slug, $userSlugs, true)) {
                        return true;
                    }
                }
                return false;
            }
        }

        // 2. 如果没有 #[Permission] 注解，走原来的推导逻辑 (向下兼容)
        $candidateSlugs = $this->generateCandidateSlugs($request);

        foreach ($candidateSlugs as $slug) {
            if (in_array($slug, $userSlugs, true)) {
                return true;
            }
        }

        return false;
    }

    protected function getPermissionsFromAttribute(Request $request): ?\Framework\Attributes\Permission
    {
        $routeInfo = $request->attributes->get('_route');
        if (!is_array($routeInfo)) {
            return null;
        }

        $controllerClass = $routeInfo['controller'] ?? null;
        $method = $routeInfo['method'] ?? null;

        if (!$controllerClass || !$method) {
            return null;
        }

        try {
            $reflectionMethod = new \ReflectionMethod($controllerClass, $method);
            $attributes = $reflectionMethod->getAttributes(\Framework\Attributes\Permission::class);
            if (!empty($attributes)) {
                return $attributes[0]->newInstance();
            }

            // 回退到类级别注解
            $reflectionClass = new \ReflectionClass($controllerClass);
            $attributes = $reflectionClass->getAttributes(\Framework\Attributes\Permission::class);
            if (!empty($attributes)) {
                return $attributes[0]->newInstance();
            }
        } catch (\ReflectionException $e) {
            return null;
        }

        return null;
    }

    protected function generateCandidateSlugs(Request $request): array
    {
        $routeName = $this->getRouteName($request);
        
        $controllerAction = $request->attributes->get('_controller');
        $routeInfo = $request->attributes->get('_route');
       //dump($controllerAction);
        $path = $request->getPathInfo();

        $slugs = [];

        if ($routeName !== '') {
            $parts = explode('.', $routeName);
            if (count($parts) >= 2) {
                $controller1 = $parts[0];
                $controller2 = $parts[count($parts) - 2];
                $action = end($parts);
                
                $actionMap = [
                    'list' => 'index',
                    'detail' => 'read',
                    'create' => 'save',
                    'update' => 'update',
                    'delete' => 'destroy',
                    'tree' => 'index',
                    'status' => 'update',
                    'batch_status' => 'update',
                    'batch-status' => 'update',
                    'options' => 'index',
                    'children' => 'index',
                    'export' => 'export',
                    'import' => 'import',
                    'clearCache' => 'cache',
                ];

                $mappedAction = $actionMap[$action] ?? $action;
                
                $slugs[] = "core:{$controller1}:{$mappedAction}";
                $slugs[] = "core:{$controller1}:{$action}";
                
                if ($controller1 !== $controller2) {
                    $slugs[] = "core:{$controller2}:{$mappedAction}";
                    $slugs[] = "core:{$controller2}:{$action}";
                }
            }
        }

        // Add standard mappings based on Path (e.g. /api/system/user/list -> core:user:index)
        $normalizedPath = ltrim($path, '/');
        if (str_starts_with($normalizedPath, 'api/')) {
            $normalizedPath = substr($normalizedPath, 4);
        }
        $segments = explode('/', $normalizedPath);
        if (count($segments) >= 3) {
            $module = $segments[0];
            $controller = $segments[1];
            $action = end($segments);

            $actionMap = [
                'list' => 'index',
                'detail' => 'read',
                'create' => 'save',
                'update' => 'update',
                'delete' => 'destroy',
                'tree' => 'index',
                'status' => 'update',
                'batch-status' => 'update',
                'batch_status' => 'update',
            ];
            $mappedAction = $actionMap[$action] ?? $action;

            $slugs[] = "core:{$controller}:{$mappedAction}";
            $slugs[] = "core:{$controller}:{$action}";
            $slugs[] = "core:{$module}:{$mappedAction}";
            $slugs[] = "core:{$module}:{$action}";
            
            if (count($segments) >= 4) {
                $subController = $segments[2];
                $slugs[] = "core:{$subController}:{$mappedAction}";
                $slugs[] = "core:{$subController}:{$action}";
            }
        }

        return array_values(array_unique($slugs));
    }

    protected function getRouteName(Request $request): string
    {
        $routeInfo = $request->attributes->get('_route');
        if (is_array($routeInfo)) {
            return (string) ($routeInfo['params']['_route_name'] ?? ($routeInfo['name'] ?? ''));
        }

        if (is_string($routeInfo)) {
            return $routeInfo;
        }

        return '';
    }

    protected function unauthorized(string $message): Response
    {
        return new Response(
            json_encode([
                'code' => 401,
                'message' => $message,
                'data' => null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            401,
            ['Content-Type' => 'application/json']
        );
    }

    protected function forbidden(string $message): Response
    {
        return new Response(
            json_encode([
                'code' => 403,
                'message' => $message,
                'data' => null,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            403,
            ['Content-Type' => 'application/json']
        );
    }
}
