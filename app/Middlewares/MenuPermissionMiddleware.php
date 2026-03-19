<?php

declare(strict_types=1);

namespace App\Middlewares;

use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MenuPermissionMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (strtoupper((string) $request->getMethod()) === 'OPTIONS') {
            return $next($request);
        }

        if ($this->isWhitelisted($request)) {
            return $next($request);
        }

        $controllerAction = $request->attributes->get('_controller');
        if (!is_string($controllerAction) || !str_starts_with($controllerAction, 'App\\Controllers\\Admin\\')) {
            return $next($request);
        }

        if (str_starts_with($controllerAction, 'App\\Controllers\\Admin\\Auth::')) {
            return $next($request);
        }

        $currentUser = $request->attributes->get('current_user');
        if (!$currentUser) {
            return BaseJsonResponse::error('请先登录', 401);
        }

        if ($this->isSuper($currentUser)) {
            return $next($request);
        }

        $requiredPerm = $this->resolveRequiredPerm($controllerAction);
        if ($requiredPerm === null || $requiredPerm === '') {
            return $next($request);
        }

        $userPerms = $this->collectUserPerms($currentUser);
        if (!in_array($requiredPerm, $userPerms, true)) {
            return BaseJsonResponse::error('无权限访问', 403);
        }

        return $next($request);
    }

    private function isWhitelisted(Request $request): bool
    {
        $path = (string) $request->getPathInfo();
        if ($path === '/api/admin/login' || $path === '/api/admin/logout') {
            return true;
        }

        return false;
    }

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

    private function resolveRequiredPerm(string $controllerAction): ?string
    {
        [$class, $method] = array_pad(explode('::', $controllerAction, 2), 2, '');
        $shortClass = $class;
        $pos = strrpos($class, '\\');
        if ($pos !== false) {
            $shortClass = substr($class, $pos + 1);
        }

        $map = [
            'SysUser' => [
                'index' => 'sys:user:list',
                'show' => 'sys:user:query',
                'store' => 'sys:user:add',
                'update' => 'sys:user:edit',
                'destroy' => 'sys:user:remove',
                'changeStatus' => 'sys:user:status',
            ],
            'SysRole' => [
                'index' => 'sys:role:list',
                'show' => 'sys:role:query',
                'store' => 'sys:role:add',
                'update' => 'sys:role:edit',
                'destroy' => 'sys:role:remove',
                'changeStatus' => 'sys:role:status',
            ],
            'SysMenu' => [
                'index' => 'sys:menu:list',
                'show' => 'sys:menu:query',
                'store' => 'sys:menu:add',
                'update' => 'sys:menu:edit',
                'destroy' => 'sys:menu:remove',
                'changeStatus' => 'sys:menu:status',
            ],
            'SysDept' => [
                'index' => 'sys:dept:list',
                'show' => 'sys:dept:query',
                'store' => 'sys:dept:add',
                'update' => 'sys:dept:edit',
                'destroy' => 'sys:dept:remove',
                'changeStatus' => 'sys:dept:status',
            ],
            'SysArticle' => [
                'index' => 'cms:article:list',
                'show' => 'cms:article:query',
                'store' => 'cms:article:add',
                'update' => 'cms:article:edit',
                'destroy' => 'cms:article:remove',
                'changeStatus' => 'cms:article:status',
            ],
        ];

        return $map[$shortClass][$method] ?? null;
    }

    private function collectUserPerms(object $currentUser): array
    {
        try {
            if (method_exists($currentUser, 'relationLoaded') && !$currentUser->relationLoaded('roles')) {
                $currentUser->load('roles.menus');
            } elseif (method_exists($currentUser, 'loadMissing')) {
                $currentUser->loadMissing('roles.menus');
            }
        } catch (\Throwable $e) {
        }

        $perms = [];
        if (!isset($currentUser->roles) || !$currentUser->roles) {
            return $perms;
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

                $perm = (string) ($menu->perms ?? '');
                if ($perm !== '') {
                    $perms[$perm] = true;
                }
            }
        }

        return array_keys($perms);
    }
}

