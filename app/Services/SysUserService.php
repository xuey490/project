<?php

namespace App\Services;

use App\Models\SysUser;
use Framework\Basic\BaseService;

class SysUserService extends BaseService
{
    public function getList(array $params)
    {
        // 增加 del_flag 过滤
        $query = SysUser::with(['dept', 'roles', 'posts'])->where('del_flag', '0');
        
        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', "%{$params['user_name']}%");
        }
        
        if (!empty($params['mobile_phone'])) {
            $query->where('mobile_phone', 'like', "%{$params['mobile_phone']}%");
        }
        
        if (isset($params['enabled']) && $params['enabled'] !== '') {
            $query->where('enabled', (int) $params['enabled']);
        }
        
        if (!empty($params['dept_id'])) {
            $query->where('dept_id', $params['dept_id']);
        }
        
        return $query->paginate($params['limit'] ?? 10);
    }
    
    public function getById(int $id)
    {
        return SysUser::with(['dept', 'roles', 'posts'])
            ->where('del_flag', '0')
            ->findOrFail($id);
    }
    
    public function create(array $data)
    {
        return $this->transaction(function () use ($data) {
            $data['created_at'] = date('Y-m-d H:i:s');
            // 默认密码处理（如果未提供）
            if (empty($data['password'])) {
                $data['password'] = password_hash('123456', PASSWORD_BCRYPT); 
            } else {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
            
            $user = SysUser::create($data);
            
            if (!empty($data['role_ids'])) {
                $user->roles()->sync($data['role_ids']);
            }
            
            if (!empty($data['post_ids'])) {
                $user->posts()->sync($data['post_ids']);
            }
            
            return $user;
        });
    }
    
    public function update(int $id, array $data)
    {
        return $this->transaction(function () use ($id, $data) {
            $user = SysUser::where('del_flag', '0')->findOrFail($id);
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // 密码处理：如果不为空则更新
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            } else {
                unset($data['password']);
            }
            
            $user->update($data);
            
            if (isset($data['role_ids'])) {
                $user->roles()->sync($data['role_ids']);
            }
            
            if (isset($data['post_ids'])) {
                $user->posts()->sync($data['post_ids']);
            }
            
            return $user;
        });
    }
    
    public function delete(array $ids): void
    {
        $this->transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $user = SysUser::where('del_flag', '0')->findOrFail($id);
                // 软删除用户
                $user->update([
                    'del_flag' => '2',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        });
    }
    
    public function changeStatus(int $id, int $enabled): void
    {
        if ($enabled !== 0 && $enabled !== 1) {
            throw new \InvalidArgumentException('Invalid enabled status');
        }
        
        $user = SysUser::where('del_flag', '0')->findOrFail($id);
        $user->update([
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function resetPassword(int $id, string $password): void
    {
        $user = SysUser::where('del_flag', '0')->findOrFail($id);
        $user->update([
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function grantRole(int $id, array $roleIds): void
    {
        $user = SysUser::where('del_flag', '0')->findOrFail($id);
        $user->roles()->sync($roleIds);
    }
}
