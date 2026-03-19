<?php

namespace App\Services;

use App\Models\SysUser;
use App\Models\SysMenu;
use Framework\Basic\BaseService;
use Framework\DI\Attribute\Autowire;

class SysAuthService extends BaseService
{
    #[Autowire]
    protected SysMenuService $menuService;

    public function login(string $username, string $password): array
    {
        $user = SysUser::with(['roles'])->where('user_name', $username)->where('del_flag', '0')->first();
        
        if (!$user) {
            throw new \Exception('用户不存在');
        }
        
        // 验证密码
        if (!password_verify($password, $user->password)) {
             throw new \Exception('密码错误');
        }
        
        if ($user->enabled == 0) {
            throw new \Exception('账号已禁用');
        }

        $primaryRole = $user->roles->first(); // 简单取第一个角色作为主角色
        $roleCode = $primaryRole?->code ?? 'user';
        
        // Issue Token
        $token = app('jwt')->issue([
            'uid' => $user->id,
            'name' => $user->user_name,
            'role' => $roleCode,
            'roles' => $user->roles->pluck('code')->values()->all(),
        ]);
        
        return $token;
    }
    
    public function getUserInfo(int $userId)
    {
        $user = SysUser::with(['roles', 'dept', 'posts'])->where('del_flag', '0')->findOrFail($userId);
        
        // Roles
        $roles = $user->roles->pluck('code')->toArray();
        // Permissions
        $permissions = [];
        if (in_array('admin', $roles)) {
            $permissions[] = '*:*:*';
        } else {
            // Get permissions from menus
            $menus = SysMenu::where('del_flag', '0')
                ->whereHas('roles', function($q) use ($user) {
                    $q->whereIn('sys_role.id', $user->roles->pluck('id'));
                })
                ->whereNotNull('code')
                ->where('code', '<>', '')
                ->pluck('code')
                ->toArray();
            $permissions = array_unique($menus);
        }
        
        return [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions
        ];
    }
    
    public function getRouters(int $userId)
    {
        $user = SysUser::with(['roles'])->where('del_flag', '0')->findOrFail($userId);
        $isAdmin = $user->roles->contains('code', 'admin');
        
        return $this->menuService->getRouters($userId, $isAdmin);
    }
}
