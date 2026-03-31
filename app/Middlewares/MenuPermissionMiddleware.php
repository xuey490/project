<?php

declare(strict_types=1);

namespace App\Middlewares;

use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuPermissionMiddleware
{
    /**
     * 处理请求
     *
     * 核心逻辑：取请求的 URL + HTTP 方法，与用户菜单中的 path + method 做匹配。
     * 菜单表中 type=2（页面菜单）的 path 字段记录的是前端路由路径（如 /system/user），
     * 对应的后端 API 前缀为 /api/system/user/... 。
     *
     * 匹配规则：
     * 1. 菜单 path="/system/user" + method="GET" → 放行 /api/system/user/list, /api/system/user/detail/1 等 GET 请求
     * 2. 菜单 path="/system/user" + method="POST" → 放行 /api/system/user/create 等 POST 请求
     * 3. 如果菜单未配置 method，则只匹配路径前缀，不区分方法
     */
    public function handle(Request $request, callable $next): Response
    {
        if (strtoupper((string) $request->getMethod()) === 'OPTIONS') {
            return $next($request);
        }

        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        $currentUser = $request->attributes->get('current_user');
        if (!$currentUser) {
            return BaseJsonResponse::error('请先登录', 401);
        }

        if ($this->isSuper($currentUser)) {
            return $next($request);
        }

        $path   = $request->getPathInfo();
        $method = strtoupper($request->getMethod());

        // 从用户菜单中构建可访问的 API 规则列表
        $allowedRules = $this->buildAllowedRules($currentUser);

        // 如果该路径前缀不在任何规则的管控范围内，直接放行
        // （即用户没有分配任何相关菜单，且路径也不属于已知业务模块）
        $isGuarded = $this->isGuardedPath($path);
        if (!$isGuarded) {
            return $next($request);
        }

        // 逐条规则匹配
        $allowed = false;
        foreach ($allowedRules as $rule) {
            if ($this->matchRule($path, $method, $rule)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return BaseJsonResponse::error('无权限访问', 403);
        }

        return $next($request);
    }

    /**
     * 白名单检查
     */
    private function isWhitelisted(Request $request): bool
    {
        $path = (string) $request->getPathInfo();

        // 精确匹配白名单
        $whitelist = [
            '/api/admin/login',
            '/api/admin/logout',
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
            '/api/core/user/modifyPassword',
            '/api/core/user/updateInfo',
            '/api/core/system/dictAll',
            '/api/core/system/statistics',
            '/api/core/system/loginChart',
            '/api/core/system/loginBarChart',
            '/api/core/console/list',
            '/api/core/console/login-bar',
            '/api/core/console/login-chart',
        ];

        if (in_array($path, $whitelist, true)) {
            return true;
        }

        // 前缀匹配白名单
        $prefixWhitelist = [
            '/api/core/captcha',
        ];
        foreach ($prefixWhitelist as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 超级管理员判断
     */
    private function isSuper(object $currentUser): bool
    {
        $userName = (string) ($currentUser->user_name ?? '');
        if ($userName === 'super') {
            return true;
        }

        if (isset($currentUser->roles) && $currentUser->roles) {
            foreach ($currentUser->roles as $role) {
                if ((string) ($role->role_key ?? '') === 'super_admin') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 判断请求路径是否属于需要权限管控的业务路径
     *
     * 不在已知模块前缀下的路径，直接放行（避免对未知路由误拦截）
     */
    private function isGuardedPath(string $path): bool
    {
        $guardedPrefixes = [
            '/api/system/',
            '/api/safeguard/',
            '/api/core/logs/',
        ];

        foreach ($guardedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 从用户菜单中构建可访问的 API 规则列表
     *
     * 规则结构: ['path' => '/system/user', 'method' => 'GET']
     * path 来自菜单表的 path 字段（前端路由路径）
     * method 来自菜单表的 method 字段
     *
     * 只收集 type=2（菜单页面）和 type=1（目录）的菜单
     */
    private function buildAllowedRules(object $currentUser): array
    {
        try {
            if (method_exists($currentUser, 'relationLoaded') && !$currentUser->relationLoaded('roles')) {
                $currentUser->load('roles.menus');
            } elseif (method_exists($currentUser, 'loadMissing')) {
                $currentUser->loadMissing('roles.menus');
            }
        } catch (\Throwable $e) {
        }

        $rules = [];

        if (!isset($currentUser->roles) || !$currentUser->roles) {
            return $rules;
        }

        foreach ($currentUser->roles as $role) {
            $roleStatus = (string) ($role->status ?? '0');
            $roleDelFlag = (string) ($role->del_flag ?? '0');
            if ($roleStatus === '1' || $roleDelFlag === '2') {
                continue;
            }

            if (!isset($role->menus) || !$role->menus) {
                continue;
            }

            foreach ($role->menus as $menu) {
                $menuStatus = (string) ($menu->status ?? '0');
                if ($menuStatus === '1') {
                    continue;
                }

                $menuType = (int) ($menu->type ?? 0);

                // 收集 type=2（菜单页面）的 path + method 作为规则
                if ($menuType === 2) {
                    $menuPath = (string) ($menu->path ?? '');
                    if ($menuPath === '') {
                        continue;
                    }

                    // 统一格式：确保以 / 开头
                    if (!str_starts_with($menuPath, '/')) {
                        $menuPath = '/' . $menuPath;
                    }

                    $menuMethod = strtoupper((string) ($menu->method ?? ''));

                    $rules[] = [
                        'path'   => $menuPath,
                        'method' => $menuMethod,
                    ];
                }

                // 收集 type=1（目录）的 path，用于前缀匹配
                if ($menuType === 1) {
                    $menuPath = (string) ($menu->path ?? '');
                    if ($menuPath === '') {
                        continue;
                    }
                    if (!str_starts_with($menuPath, '/')) {
                        $menuPath = '/' . $menuPath;
                    }

                    $rules[] = [
                        'path'   => $menuPath,
                        'method' => '', // 目录不限制方法
                    ];
                }
            }
        }

        return $rules;
    }

    /**
     * 将请求的 API 路径转换为菜单路径前缀
     *
     * 例如：
     *   /api/system/user/list       → /system/user
     *   /api/system/user/detail/1   → /system/user
     *   /api/core/logs/getLoginLogPageList → /core/logs
     */
    private function apiPathToMenuPrefix(string $apiPath): string
    {
        // 去掉 /api 前缀
        $normalized = ltrim($apiPath, '/');

        if (str_starts_with($normalized, 'api/')) {
            $normalized = substr($normalized, 4);
        }

        // 拆分段
        $segments = explode('/', $normalized);

        // 至少需要 2 段才能构成有意义的模块路径（如 system/user）
        if (count($segments) < 2) {
            return '/' . $normalized;
        }

        // 取前两段作为菜单路径（如 system/user, core/logs）
        // 这是菜单 path 字段最常见的格式
        return '/' . $segments[0] . '/' . $segments[1];
    }

    /**
     * 将请求的 API 路径转换为可能的多种菜单路径前缀
     *
     * 返回从最长到最短的前缀列表，便于逐级匹配
     *
     * 例如 /api/system/dict/type/list 返回:
     *   ['/system/dict/type', '/system/dict', '/system']
     */
    private function apiPathToMenuPrefixes(string $apiPath): array
    {
        // 去掉 /api 前缀
        $normalized = ltrim($apiPath, '/');

        if (str_starts_with($normalized, 'api/')) {
            $normalized = substr($normalized, 4);
        }

        $segments = explode('/', $normalized);
        $prefixes = [];

        // 生成 /seg1/seg2, /seg1 两级前缀
        for ($i = count($segments); $i >= 1; $i--) {
            $prefix = '/' . implode('/', array_slice($segments, 0, $i));
            $prefixes[] = $prefix;
        }

        return $prefixes;
    }

    /**
     * 匹配请求路径和方法是否命中规则
     *
     * @param string $requestPath   请求路径（如 /api/system/user/list）
     * @param string $requestMethod 请求方法（如 GET）
     * @param array  $rule          菜单规则 ['path' => '/system/user', 'method' => 'GET']
     */
    private function matchRule(string $requestPath, string $requestMethod, array $rule): bool
    {
        $rulePath   = $rule['path'];
        $ruleMethod = $rule['method'];

        // 将请求路径转换为菜单路径前缀
        $menuPrefix = $this->apiPathToMenuPrefix($requestPath);

        // 第一层：精确匹配菜单路径（前两级）
        if ($menuPrefix === $rulePath) {
            // 如果规则指定了方法，则必须匹配
            if ($ruleMethod !== '') {
                return $requestMethod === $ruleMethod;
            }
            // 未指定方法的规则，放行所有方法
            return true;
        }

        // 第二层：请求路径前缀包含在菜单路径中
        // 例如请求 /api/system/dict/type/list，菜单路径为 /system/dict
        $allPrefixes = $this->apiPathToMenuPrefixes($requestPath);
        foreach ($allPrefixes as $prefix) {
            if ($prefix === $rulePath) {
                if ($ruleMethod !== '') {
                    return $requestMethod === $ruleMethod;
                }
                return true;
            }
        }

        // 第三层：菜单路径是请求路径的前缀（用于目录级别的匹配）
        // 例如菜单路径 /system，请求路径 /api/system/user/list
        if (str_starts_with($menuPrefix, $rulePath)) {
            if ($ruleMethod !== '') {
                return $requestMethod === $ruleMethod;
            }
            return true;
        }

        return false;
    }
}
