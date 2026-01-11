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

use Illuminate\Database\Eloquent\Model;
use Framework\Basic\Scopes\LaTenantScope;
use Framework\Tenant\TenantContext;

trait LaBelongsToTenant
{
    public static function bootLaBelongsToTenant()
    {
        // 1. 添加全局作用域（读、改、删限制）
        static::addGlobalScope(new LaTenantScope());

        // 2. 创建时自动写入租户ID（新增限制）
        static::creating(function (Model $model) {
			$tenantId =  TenantContext::getTenantId() ;	// function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;
            // 如果有租户ID，且模型里还没有设置该字段
            if ($tenantId && !isset($model->tenant_id)) {
                $model->setAttribute('tenant_id', $tenantId);
            }

        });
    }
	
    // ==================================================
    // 对齐你最初 TP8 / 旧版接口的“超管模式”
    // ==================================================

    /**
     * 开启超管模式（仅影响当前请求上下文）
     * 用法：
     *   Custom::ignoreTenant()->find(1)
     */
    public static function ignoreTenant(): self
    {
        TenantContext::ignore();
        return new static();
    }

    /**
     * 恢复租户隔离
     */
    public static function restoreTenant(): void
    {
        TenantContext::restore();
    }

    /**
     * 推荐方式：作用域安全调用
     */
    public static function withIgnoreTenant(callable $fn)
    {
        return TenantContext::withIgnore($fn);
    }

    /**
     * 允许临时忽略租户限制（例如超级管理员后台查看所有数据）
     */
    public static function withoutTenancy()
    {
        return static::withoutGlobalScope(LaTenantScope::class);
    }
	
    /**
     * 兼容你旧的 withoutTenancy()
     */
    public static function withoutTenancy_1()
    {
        TenantContext::ignore();
        return static::query();
    }
}