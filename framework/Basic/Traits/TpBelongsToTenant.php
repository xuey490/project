<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-9
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Traits;

use Framework\Basic\Scopes\TpTenantScope;
use think\db\Query;
use think\Model;
use Framework\Tenant\TenantContext;
use Closure;

trait TpBelongsToTenant
{

    /**
     * Trait 初始化方法
     * 注意：在 TP8 中，我们通常不需要手动调用 event 绑定
     * 框架会自动扫描 onBeforeInsert 等静态方法
     * 此方法保留仅用于兼容旧代码或手动调用
     */
    public static function initTpBelongsToTenant(): void
    {
        // 留空或仅用于调试，主要逻辑移至 onBeforeInsert
        // TP8 会自动识别 onBeforeInsert 静态方法
    }

    // =========================================================================
    //  链式调用：开启/关闭 租户隔离
    // =========================================================================
	
    /**
     * 临时忽略租户隔离，链式调用
     *
     * 用法：
     *   User::ignoreTenant()->find(123);
     *
     * @return static
     */
    public function ignoreTenant(): static
    {
        TenantContext::ignore();
        return $this;
    }	

    /**
     * 恢复租户隔离（一般用于链式操作后）
     * @return static
     */
    public function restoreTenant(): static
    {
        TenantContext::restore();
        return $this;
    }

    /**
     * 临时超管访问闭包执行
     *
     * 用法：
     *   User::withIgnoreTenant(function () {
     *       return User::all();
     *   });
     *
     * @param Closure $closure
     * @return mixed
     */
    public static function withIgnoreTenant(Closure $closure): mixed
    {
        TenantContext::ignore();
        try {
            return $closure();
        } finally {
            TenantContext::restore();
        }
    }

    // =========================================================================
    //  模型事件：自动填充 tenant_id
    // =========================================================================

    /**
     * ThinkPHP 8 模型事件：新增前
     * 统一使用这个标准事件，避免使用 init + event 的旧模式
     */
    public static function onBeforeInsert(Model $model): void
    {
        // 如果已经设置了 tenant_id 或者开启了忽略模式，跳过
        if (isset($model->tenant_id) || !TenantContext::shouldApplyTenant()) {
            return;
        }

        $tenantId = TenantContext::getTenantId();
        if ($tenantId) {
            $model->setAttr('tenant_id', $tenantId);
        }
    }

    // 如果需要更新时也检查（通常不需要，租户ID不应被修改），可以开启
    // public static function onBeforeUpdate(Model $model): void { ... }


    /**
     * 全局作用域或本地作用域钩子
     * 会被基类的全局作用域调用，或者手动调用
     */
    public function scopeTenant(Query $query): void
    {
        // 如果开启了忽略模式，则不加 tenant_id 条件
        if (!TenantContext::shouldApplyTenant()) {
			// 超管或无租户，跳过条件
            return;
        }

        // 委托给具体的 Scope 类处理（保持逻辑分离）
        // 确保 TpTenantScope 存在且 apply 方法正确
        (new TpTenantScope())->apply($query, $this);
    }



}