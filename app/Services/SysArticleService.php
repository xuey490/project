<?php

namespace App\Services;

use App\Models\SysArticle;
use Framework\Basic\BaseService;

class SysArticleService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysArticle::where('del_flag', '0');
        
        // Data Scope
        if (isset($params['currentUser']) && !empty($params['currentUser'])) {
            $user = $params['currentUser'];
            // 简单实现：如果是超级管理员(role_id=1)，不做限制
            // 这里假设 user 对象有 roles 关联
             $isSuper = false;
             foreach ($user->roles as $role) {
                 if ($role->role_key === 'super_admin' || $role->role_id === 1) {
                     $isSuper = true;
                     break;
                 }
             }
             
             if (!$isSuper) {
                 // 简单的部门过滤示例：只能看本部门
                 // 实际应根据 Role 的 data_scope 处理，这里为保持原逻辑暂且简化，后续可完善
                 $query->where('dept_id', $user->dept_id);
             }
        }
        
        if (!empty($params['title'])) {
            $query->where('title', 'like', "%{$params['title']}%");
        }
        
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (string) $params['status']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($params['limit'] ?? 10);
    }
    
    public function getById(int $id)
    {
        return SysArticle::where('del_flag', '0')->findOrFail($id);
    }
    
    public function create(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['del_flag'] = '0';
        return SysArticle::create($data);
    }
    
    public function update(int $id, array $data)
    {
        $article = SysArticle::where('del_flag', '0')->findOrFail($id);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $article->update($data);
        return $article;
    }
    
    public function delete(int $id): void
    {
        // 简单模型没有关联表需要清理，可以直接更新 del_flag
        // 但为了统一风格，使用事务包裹（未来扩展如文章标签、评论时有用）
        $this->transaction(function () use ($id) {
            $article = SysArticle::where('del_flag', '0')->findOrFail($id);
            $article->update([
                'del_flag' => '2',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        });
    }
    
    public function changeStatus(int $id, string $status): void
    {
        if ($status !== '0' && $status !== '1') {
            throw new \InvalidArgumentException('Invalid status');
        }
        
        $article = SysArticle::where('del_flag', '0')->findOrFail($id);
        $article->update([
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
