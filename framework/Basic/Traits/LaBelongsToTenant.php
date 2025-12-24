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

namespace Framework\Basic\Traits;


use Framework\Basic\Scopes\LaTenantScope;

trait LaBelongsToTenant
{
    public static function bootBelongsToTenant()
    {
        // 1. 添加全局作用域（读、改、删限制）
        static::addGlobalScope(new LaTenantScope);

        // 2. 创建时自动写入租户ID（新增限制）
        static::creating(function ($model) {
            // 如果模型并未手动设置 tenant_id，则自动填充
            if (!isset($model->tenant_id)) {
                $tenantId = \getCurrentTenantId();
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    /**
     * 允许临时忽略租户限制（例如超级管理员后台查看所有数据）
     */
    public static function withoutTenancy()
    {
        return static::withoutGlobalScope(LaTenantScope::class);
    }
}