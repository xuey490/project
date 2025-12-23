<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-23
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */
 
namespace Framework\Basic\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class LaTenantScope implements Scope
{
    /**
     * 将租户约束应用于所有查询
     */
    public function apply(Builder $builder, Model $model)
    {
        // 假设有一个全局函数或服务获取当前租户ID
        // 如果没有租户上下文（例如登录前或命令行），则不限制，或者视业务需求而定
        $tenantId = \getCurrentTenantId(); 

        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', '=', $tenantId);
        }
    }
}