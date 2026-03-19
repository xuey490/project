<?php

namespace App\Services;

use App\Models\SysDict;
use App\Models\SysDictItem;
use Framework\Basic\BaseService;

class SysDictService extends BaseService
{
    // ================= Dict Type =================

    public function getTypeList(array $params)
    {
        $query = SysDict::where('del_flag', '0');
        
        if (!empty($params['name'])) {
            $query->where('name', 'like', "%{$params['name']}%");
        }
        
        if (!empty($params['code'])) {
            $query->where('code', 'like', "%{$params['code']}%");
        }
        
        if (isset($params['enabled']) && $params['enabled'] !== '') {
            $query->where('enabled', (int) $params['enabled']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($params['limit'] ?? 10);
    }
    
    public function getTypeById(int $id)
    {
        return SysDict::where('del_flag', '0')->findOrFail($id);
    }
    
    public function createType(array $data)
    {
        if (SysDict::where('code', $data['code'])->where('del_flag', '0')->exists()) {
            throw new \Exception('Dict code already exists');
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        return SysDict::create($data);
    }
    
    public function updateType(int $id, array $data)
    {
        $dict = SysDict::where('del_flag', '0')->findOrFail($id);
        
        if (isset($data['code']) && $data['code'] !== $dict->code) {
             if (SysDict::where('code', $data['code'])->where('del_flag', '0')->where('id', '<>', $id)->exists()) {
                throw new \Exception('Dict code already exists');
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $dict->update($data);
        return $dict;
    }
    
    public function deleteType(array $ids): void
    {
        $this->transaction(function () use ($ids) {
            foreach ($ids as $id) {
                $dict = SysDict::where('del_flag', '0')->findOrFail($id);
                // 检查是否有数据项
                if ($dict->items()->count() > 0) {
                     throw new \Exception("Dict {$dict->name} has items, cannot delete");
                }
                
                $dict->update([
                    'del_flag' => '2',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        });
    }
    
    public function refreshCache(): void
    {
        // Implement cache refresh logic here (e.g. clear redis)
    }
    
    // ================= Dict Data (Item) =================
    
    public function getDataList(array $params)
    {
        $query = SysDictItem::query();
        
        if (!empty($params['dict_code'])) {
            $query->where('code', $params['dict_code']);
        }
        
        if (!empty($params['label'])) {
            $query->where('label', 'like', "%{$params['label']}%");
        }
        
        if (isset($params['enabled']) && $params['enabled'] !== '') {
            $query->where('enabled', (int) $params['enabled']);
        }
        
        return $query->orderBy('sort')->paginate($params['limit'] ?? 10);
    }
    
    public function getDataById(int $id)
    {
        return SysDictItem::findOrFail($id);
    }
    
    public function createData(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return SysDictItem::create($data);
    }
    
    public function updateData(int $id, array $data)
    {
        $item = SysDictItem::findOrFail($id);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $item->update($data);
        return $item;
    }
    
    public function deleteData(array $ids): void
    {
        SysDictItem::destroy($ids);
    }
    
    public function getDictDataByType(string $dictType)
    {
        return SysDictItem::where('code', $dictType)
            ->where('enabled', 1)
            ->orderBy('sort')
            ->get();
    }
}
