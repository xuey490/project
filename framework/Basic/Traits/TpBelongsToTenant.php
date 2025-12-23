<?php
declare(strict_types=1);

namespace Framework\Basic\Traits;

use Framework\Basic\Scope\TenantScope;

trait TpBelongsToTenant
{
    /**
     * 自动注册全局作用域和模型事件
     * TP 模型初始化时会自动调用 init 方法
     */
    public static function init()
    {
        // 1. 注册全局查询范围 (读、改、删 限制)
        // 注意：这会叠加到 $globalScope 属性中
        // 如果父类已经有 init，需要注意兼容，但在 Trait 中通常使用特定命名方法
        // TP没有标准的 bootTrait，所以我们直接在 Model 的 init 中处理，或者在这里定义
    }

    /**
     * 必须在模型类中显式定义 globalScope 属性或在 init 中动态添加
     * 为了稳健，我们将在 BaseTpORMModel 中统一处理 Scope 的注册
     */

    /**
     * 模型事件：写入前自动追加 tenant_id
     */
    public static function onBeforeInsert($model)
    {
        if (!isset($model->tenant_id)) {
            $tenantId = \getCurrentTenantId();
            if ($tenantId) {
                $model->setAttr('tenant_id', $tenantId);
            }
        }
    }
}