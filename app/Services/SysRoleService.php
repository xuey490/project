<?php

namespace App\Services;

use App\Models\SysRole;
use App\Models\SysUser;
use Framework\Basic\BaseService;

class SysRoleService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysRole::with(['menus', 'depts'])->where('del_flag', '0');

        if (!empty($params['role_name'])) {
            $query->where('role_name', 'like', "%{$params['role_name']}%");
        }
        if (!empty($params['role_key'])) {
            $query->where('role_key', 'like', "%{$params['role_key']}%");
        }
        if (isset($params['enabled']) && $params['enabled'] !== '') {
            $query->where('enabled', (int) $params['enabled']);
        }

        return $query->orderBy('role_sort')->paginate($params['limit'] ?? 10);
    }

    public function getById(int $id)
    {
        return SysRole::with(['menus', 'depts'])
            ->where('del_flag', '0')
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->transaction(function () use ($data) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $role = SysRole::create($data);
            
            if (isset($data['menu_ids'])) {
                $role->menus()->sync($data['menu_ids']);
            }
            
            return $role;
        });
    }
    
    public function update(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            $role = SysRole::where('del_flag', '0')->findOrFail($id);
            $data['updated_at'] = date('Y-m-d H:i:s');
            $role->update($data);
            
            if (isset($data['menu_ids'])) {
                $role->menus()->sync($data['menu_ids']);
            }
            
            // Data Scope
            if (isset($data['data_scope'])) {
                 // Handle custom dept ids if data_scope is 2
                 if ($data['data_scope'] == '2' && isset($data['dept_ids'])) {
                     $role->depts()->sync($data['dept_ids']);
                 }
            }
            
            return $role;
        });
    }

    public function delete(array $ids): void
    {
        $this->transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $role = SysRole::findOrFail($id);
                $role->menus()->detach();
                $role->depts()->detach();
                $role->users()->detach();
                $role->update([
                    'del_flag' => '2',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        });
    }

    public function changeStatus(int $id, int $enabled): void
    {
        if ($enabled !== 0 && $enabled !== 1) {
            throw new \InvalidArgumentException('Invalid enabled status');
        }

        $role = SysRole::where('del_flag', '0')->findOrFail($id);
        $role->update([
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 查询已分配该角色的用户列表
     */
    public function allocatedUserList(int $roleId, array $params)
    {
        $role = SysRole::where('del_flag', '0')->findOrFail($roleId);
        
        $query = $role->users()->where('del_flag', '0');
        
        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', "%{$params['user_name']}%");
        }
        if (!empty($params['mobile_phone'])) {
            $query->where('mobile_phone', 'like', "%{$params['mobile_phone']}%");
        }
        
        return $query->paginate($params['limit'] ?? 10);
    }

    /**
     * 查询未分配该角色的用户列表
     */
    public function unallocatedUserList(int $roleId, array $params)
    {
        $query = SysUser::where('del_flag', '0')
            ->whereDoesntHave('roles', function ($q) use ($roleId) {
                $q->where('sys_role.id', $roleId);
            });
            
        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', "%{$params['user_name']}%");
        }
        if (!empty($params['mobile_phone'])) {
            $query->where('mobile_phone', 'like', "%{$params['mobile_phone']}%");
        }
        
        return $query->paginate($params['limit'] ?? 10);
    }

    /**
     * 批量给用户授权该角色
     */
    public function authUser(int $roleId, array $userIds): void
    {
        $role = SysRole::where('del_flag', '0')->findOrFail($roleId);
        $role->users()->syncWithoutDetaching($userIds);
    }

    /**
     * 批量取消用户的该角色授权
     */
    public function cancelAuthUser(int $roleId, array $userIds): void
    {
        $role = SysRole::where('del_flag', '0')->findOrFail($roleId);
        $role->users()->detach($userIds);
    }

    public function getRoleMenuIds(int $roleId): array
    {
        $role = SysRole::where('del_flag', '0')->findOrFail($roleId);
        return $role->menus()->pluck('sys_menu.id')->toArray();
    }

    public function getRoleScopeIds(int $roleId): array
    {
        $role = SysRole::where('del_flag', '0')->findOrFail($roleId);
        return $role->depts()->pluck('sys_dept.id')->toArray();
    }
}
