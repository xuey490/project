<?php

namespace App\Services;

use App\Models\SysPost;
use Framework\Basic\BaseService;

class SysPostService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysPost::where('del_flag', '0');
        
        if (!empty($params['post_code'])) {
            $query->where('post_code', 'like', "%{$params['post_code']}%");
        }
        
        if (!empty($params['post_name'])) {
            $query->where('post_name', 'like', "%{$params['post_name']}%");
        }
        
        if (isset($params['enabled']) && $params['enabled'] !== '') {
            $query->where('enabled', (int) $params['enabled']);
        }
        
        return $query->orderBy('post_sort')->paginate($params['limit'] ?? 10);
    }
    
    public function getById(int $id)
    {
        return SysPost::where('del_flag', '0')->findOrFail($id);
    }
    
    public function create(array $data)
    {
        // 唯一性校验
        if (SysPost::where('post_code', $data['post_code'])->where('del_flag', '0')->exists()) {
            throw new \Exception('Post code already exists');
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['del_flag'] = '0';
        
        return SysPost::create($data);
    }
    
    public function update(int $id, array $data)
    {
        $post = SysPost::where('del_flag', '0')->findOrFail($id);
        
        if (isset($data['post_code']) && $data['post_code'] !== $post->post_code) {
             if (SysPost::where('post_code', $data['post_code'])->where('del_flag', '0')->where('id', '<>', $id)->exists()) {
                throw new \Exception('Post code already exists');
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $post->update($data);
        
        return $post;
    }
    
    public function delete(array $ids): void
    {
        $this->transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $post = SysPost::where('del_flag', '0')->findOrFail($id);
                
                // 检查是否有用户分配了该职位
                if ($post->users()->count() > 0) {
                    throw new \Exception("Post ID {$id} is assigned to users, cannot delete");
                }
                
                $post->users()->detach();
                
                $post->update([
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
        
        $post = SysPost::where('del_flag', '0')->findOrFail($id);
        $post->update([
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
