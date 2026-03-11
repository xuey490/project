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

/**
 * Laravel 多租户隔离 Trait
 * 
 * 该 Trait 为 Laravel Eloquent 模型提供多租户数据隔离功能。
 * 通过全局作用域自动为查询添加租户条件，并在创建记录时自动填充租户ID。
 * 
 * 主要功能：
 * - 自动添加租户查询条件（读、改、删）
 * - 创建时自动写入租户ID
 * - 支持超管模式忽略租户隔离
 * 
 * 使用方式：在需要多租户隔离的模型中 use 此 Trait。
 * 
 * @package Framework\Basic\Traits
 */
trait LaBelongsToTenant
{
    /**
     * Trait 引导方法
     * 
     * 在模型启动时自动调用，完成以下操作：
     * 1. 注册全局租户作用域（用于读、改、删操作的数据隔离）
     * 2. 绑定 creating 事件（用于新增时自动填充租户ID）
     * 
     * @return void
     */
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
    // 超管模式相关方法
    // ==================================================

    /**
     * 开启超管模式（忽略租户隔离）
     * 
     * 用于超级管理员查看所有租户数据的场景。
     * 注意：此方法仅影响当前请求上下文。
     * 
     * 用法示例：
     *   Custom::ignoreTenant()->find(1);
     * 
     * @return static 返回模型实例，支持链式调用
     */
    public static function ignoreTenant(): self
    {
        TenantContext::ignore();
        return new static();
    }

    /**
     * 恢复租户隔离
     * 
     * 在调用 ignoreTenant() 后，手动恢复租户隔离机制。
     * 
     * @return void
     */
    public static function restoreTenant(): void
    {
        TenantContext::restore();
    }

    /**
     * 安全作用域方式忽略租户
     * 
     * 推荐使用的临时超管访问方式，在闭包执行完毕后自动恢复租户隔离。
     * 
     * 用法示例：
     *   Custom::withIgnoreTenant(function() {
     *       return Custom::all();
     *   });
     * 
     * @param callable $fn 需要在忽略租户隔离下执行的闭包
     * @return mixed 闭包的返回值
     */
    public static function withIgnoreTenant(callable $fn)
    {
        return TenantContext::withIgnore($fn);
    }

    /**
     * 移除全局租户作用域
     * 
     * 允许临时忽略租户限制（例如超级管理员后台查看所有数据）。
     * 此方法直接移除全局作用域，不会影响 TenantContext 状态。
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutTenancy()
    {
        return static::withoutGlobalScope(LaTenantScope::class);
    }
	
    /**
     * 兼容旧版 withoutTenancy 方法
     * 
     * 此方法同时设置 TenantContext 忽略状态并返回查询构建器。
     * 建议使用 withoutTenancy() 或 withIgnoreTenant() 替代。
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     * @deprecated 建议使用 withIgnoreTenant() 方法
     */
    public static function withoutTenancy_1()
    {
        TenantContext::ignore();
        return static::query();
    }
}