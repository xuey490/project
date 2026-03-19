<?php

namespace App\Services;

use App\Models\SysDept;
use Framework\Basic\BaseService;

class SysDeptService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysDept::where('del_flag', '0');
        
        if (!empty($params['dept_name'])) {
            $query->where('dept_name', 'like', "%{$params['dept_name']}%");
        }
        
        if (isset($params['enabled']) && $params['enabled'] !== '') {
            $query->where('enabled', (int) $params['enabled']);
        }
        
        $list = $query->orderBy('order_num')->get();
        return $this->buildTree($list->toArray());
    }
    
    public function getById(int $id)
    {
        return SysDept::where('del_flag', '0')->findOrFail($id);
    }
    
    public function create(array $data)
    {
        // 计算 ancestors
        $parentId = (int) ($data['pid'] ?? 0);
        $ancestors = '0';
        if ($parentId > 0) {
            $parent = SysDept::where('del_flag', '0')->find($parentId);
            if ($parent) {
                $ancestors = $parent->ancestors . ',' . $parentId;
            }
        }
        $data['ancestors'] = $ancestors;
        $data['created_at'] = date('Y-m-d H:i:s');
        
        return SysDept::create($data);
    }
    
    public function update(int $id, array $data)
    {
        $dept = SysDept::where('del_flag', '0')->findOrFail($id);
        
        if (isset($data['pid']) && $data['pid'] == $id) {
             throw new \Exception('Parent dept cannot be self');
        }
        
        // 如果修改了 pid，需要更新 ancestors
        if (isset($data['pid']) && $data['pid'] != $dept->pid) {
            $parentId = (int) $data['pid'];
            $newAncestors = '0';
            if ($parentId > 0) {
                $parent = SysDept::where('del_flag', '0')->find($parentId);
                if ($parent) {
                    $newAncestors = $parent->ancestors . ',' . $parentId;
                }
            }
            $data['ancestors'] = $newAncestors;
            
            // TODO: Update children ancestors
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $dept->update($data);
        return $dept;
    }
    
    public function delete(array $ids): void
    {
        $this->transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $dept = SysDept::where('del_flag', '0')->findOrFail($id);
                
                // 检查是否有子部门
                if (SysDept::where('pid', $id)->where('del_flag', '0')->exists()) {
                    throw new \Exception("Dept ID {$id} has sub-depts, cannot delete");
                }
                
                // 检查是否有用户
                if (\App\Models\SysUser::where('dept_id', $id)->where('del_flag', '0')->exists()) {
                     throw new \Exception("Dept ID {$id} has users, cannot delete");
                }
                
                $dept->update([
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
        
        $dept = SysDept::where('del_flag', '0')->findOrFail($id);
        $dept->update([
            'enabled' => $enabled,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function buildTree(array $elements, int $parentId = 0): array
    {
        $branch = [];
        foreach ($elements as $element) {
            if ($element['pid'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }
}
