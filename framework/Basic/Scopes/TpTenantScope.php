<?php 
declare(strict_types=1);

namespace Framework\Basic\Scopes;

use think\db\Query;
use think\Model;

class TpTenantScope
{
    public function apply(Query $query, Model $model): void
    {
        // 1. 获取当前租户ID（保持原有逻辑）
        $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;
        
        if (!$tenantId) {
            return;
        }

        // 2. 检测模型是否存在 tenant_id 字段（保持原有逻辑，避免无此字段时报错）
        $fields = $model->getFields();
        if (!is_array($fields) || !array_key_exists('tenant_id', $fields)) {
            return;
        }

        // 3. 构建带表名的字段，防止联表冲突（保持原有逻辑）
        $fieldKey = $model->getTable() . '.tenant_id';

        // 4. 过滤重复条件，避免多次添加（保持原有逻辑）
        $options = $query->getOptions();
        $hasWhere = false;
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