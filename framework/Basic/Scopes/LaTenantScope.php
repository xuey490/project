<?php

declare(strict_types=1);

namespace Framework\Basic\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Framework\Tenant\TenantContext;

/**
 * Laravel 模型租户隔离作用域
 *
 * 为 Eloquent 模型提供自动的租户隔离功能。当模型使用此全局作用域时，
 * 所有查询、更新、删除操作都会自动添加 tenant_id 条件，
 * 确保多租户环境下的数据隔离。
 *
 * 使用方式：
 * 在模型中注册全局作用域：
 * protected static function booted() {
 *     static::addGlobalScope(new LaTenantScope());
 * }
 *
 * 临时跳过租户隔离：
 * Model::withoutTenancy()->get();
 *
 * @package Framework\Basic\Scopes
 */
class LaTenantScope implements Scope
{
    /**
     * 应用租户隔离作用域
     *
     * 在查询、更新、删除时自动追加 tenant_id 条件。
     * 如果当前处于"忽略租户"状态或租户ID为空，则不添加限制条件。
     * 只有模型表包含 tenant_id 字段时才会生效。
     *
     * @param Builder $builder Eloquent 查询构建器
     * @param Model $model 模型实例
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // 如果当前处于"忽略租户"状态，直接放行
        if (!TenantContext::shouldApplyTenant()) {
            return;
        }

        $tenantId = TenantContext::getTenantId();

        if ($tenantId === null) {
            return;
        }
		
        //$tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;

		#dump($model->getFields());
        // 只有获取到租户ID，且当前没有请求移除租户限制时才生效
        if ($tenantId && !isset($model->tenant_id) && in_array('tenant_id' , $model->getFields()) ) {
            // 使用 qualiftyColumn 防止联表字段冲突 (输出 table.tenant_id)
            $column = $model->qualifyColumn('tenant_id');
			/*
			$column = property_exists($model, 'tenantColumn')
				? $model->tenantColumn
				: 'tenant_id';
			*/
            $builder->where($column , '=', $tenantId);
        }
    }


    /**
     * 扩展查询构建器，增加 withoutTenancy 方法
     *
     * 允许在特定查询中临时跳过租户隔离限制。
     *
     * 使用示例：
     * User::withoutTenancy()->get(); // 获取所有用户，不限制租户
     *
     * @param Builder $builder Eloquent 查询构建器
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
