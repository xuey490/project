<?php
declare(strict_types=1);

namespace Framework\Basic\Scopes;

use think\db\Query;
use think\Model;

class TpTenantScope
{
    /**
     * 对查询应用全局作用域
     * 适用于: select, update, delete (通过模型调用时)
     */
    public function apply(Query $query, Model $model)
    {
        // 假设有一个全局帮助函数获取当前租户ID
        $tenantId = \getCurrentTenantId();

        // 只有当获取到租户ID，且表中确实有 tenant_id 字段时才限制
        // 注意：如果你确定所有继承此Model的表都有tenant_id，可以去掉 try-catch 或 field 检查以提升性能
        if ($tenantId) {
            $query->where($model->getTable() . '.tenant_id', '=', $tenantId);
        }
    }
}