<?php

declare(strict_types=1);

namespace Framework\Basic\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LaTenantScope implements Scope
{
    /**
     * 应用作用域：查询、更新、删除时自动追加 tenant_id
     */
    public function apply(Builder $builder, Model $model)
    {
		
        $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;

		#dump($model->getFields());
        // 只有获取到租户ID，且当前没有请求移除租户限制时才生效
        if ($tenantId && !isset($model->tenant_id) && in_array('tenant_id' , $model->getFields()) ) {
            // 使用 qualiftyColumn 防止联表字段冲突 (输出 table.tenant_id)
            $column = $model->qualifyColumn('tenant_id');
            $builder->where($column, '=', $tenantId);
        }
    }

    /**
     * 扩展 Builder，增加 withoutTenancy 方法
     * 用法: User::withoutTenancy()->get();
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}