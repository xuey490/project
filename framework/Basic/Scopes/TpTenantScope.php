<?php 

declare(strict_types=1);

namespace Framework\Basic\Scopes;

use think\db\Query;
use think\Model;

class TpTenantScope
{
    public function apply(Query $query, Model $model): void
    {
        // 1. 获取当前租户ID
        $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null; // 这里改成你的实际获取逻辑
        
        if (!$tenantId) {
            return;
        }

        // 2. 只有表里有 tenant_id 字段才加限制
        $fields = $model->getFields();
        if (is_array($fields) && array_key_exists('tenant_id', $fields)) {
            
            // 3. 构建带表名的字段，防止联表冲突
            $fieldKey = $model->getTable() . '.tenant_id';
            
            // 4. 【关键】获取当前所有 where 条件，避免重复添加
            $options = $query->getOptions();
            $hasWhere = false;
            
            // 简单的重复检测（可选，防止特定情况下加了两次）
            if (!empty($options['where'])) {
                foreach ($options['where'] as $where) {
                    if (isset($where[0]) && $where[0] === $fieldKey) {
                        $hasWhere = true;
                        break;
                    }
                }
            }

            if (!$hasWhere) {
                $query->where($fieldKey, '=', $tenantId);
            }
        }
    }
}