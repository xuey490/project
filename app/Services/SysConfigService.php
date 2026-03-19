<?php

namespace App\Services;

use App\Models\SysConfig;
use Framework\Basic\BaseService;

class SysConfigService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysConfig::query();
        
        if (!empty($params['config_name'])) {
            $query->where('config_name', 'like', "%{$params['config_name']}%");
        }
        
        if (!empty($params['config_key'])) {
            $query->where('config_key', 'like', "%{$params['config_key']}%");
        }
        
        if (isset($params['config_type']) && $params['config_type'] !== '') {
            $query->where('config_type', $params['config_type']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($params['limit'] ?? 10);
    }
    
    public function getById(int $id)
    {
        return SysConfig::findOrFail($id);
    }
    
    public function create(array $data)
    {
        if (SysConfig::where('config_key', $data['config_key'])->exists()) {
            throw new \Exception('Config key already exists');
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        return SysConfig::create($data);
    }
    
    public function update(int $id, array $data)
    {
        $config = SysConfig::findOrFail($id);
        
        if (isset($data['config_key']) && $data['config_key'] !== $config->config_key) {
             if (SysConfig::where('config_key', $data['config_key'])->where('id', '<>', $id)->exists()) {
                throw new \Exception('Config key already exists');
            }
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        $config->update($data);
        return $config;
    }
    
    public function delete(array $ids): void
    {
        SysConfig::destroy($ids);
    }
    
    public function refreshCache(): void
    {
        // Implement cache refresh
    }
    
    public function getConfigValue(string $key)
    {
        $config = SysConfig::where('config_key', $key)->first();
        return $config ? $config->config_value : '';
    }
}
