<?php
declare(strict_types=1);

namespace Framework\Basic\Traits;

use Framework\Basic\Scopes\TpTenantScope;
use think\db\Query;
use think\Model;

trait TpBelongsToTenant
{
    /**
     * 标识是否忽略租户隔离（高管超限使用）
     * @var bool
     */
    protected static bool $ignoreTenantScope = false;

    /**
     * Trait 初始化（模拟 TP 的 boot 机制，自动注册作用域和事件）
     * 注意：TP 模型的 init 是静态方法，Trait 中通过静态构造函数兼容
     */
	/* 
    public static function initTpBelongsToTenant()
    {
        // 1. 动态添加全局作用域（无需手动配置 $globalScope 属性）
        static::addGlobalScope('tenant', function (Query $query) {
            // 若标记忽略租户隔离，则不应用作用域
            if (static::$ignoreTenantScope) {
                return;
            }
            (new TpTenantScope())->apply($query, new static());
        });

        // 2. 注册模型事件：新增前自动填充 tenant_id（与原有逻辑一致，整合到 Trait）
        static::event('before_insert', function (Model $model) {
            if (!isset($model->tenant_id) && !static::$ignoreTenantScope) {
                $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;
                if ($tenantId) {
                    $model->setAttr('tenant_id', $tenantId);
                }
            }
        });
    }
	*/

    /**
     * 开启高管超限模式（忽略租户隔离）
     * @return static
     */
    public static function ignoreTenant(): static
    {
        static::$ignoreTenantScope = true;
        return new static();
    }

    /**
     * 关闭高管超限模式（恢复租户隔离）
     * （静态属性共享，使用后建议手动关闭，或通过查询结束自动重置）
     */
    public static function restoreTenant(): void
    {
        static::$ignoreTenantScope = false;
    }

    /**
     * 【兼容原有逻辑】租户作用域（可选，保留向下兼容）
	 * ThinkPHP 会自动调用 scopeTenant($query)
     */
    public function scopeTenant(Query $query)
    {
        if (static::$ignoreTenantScope) {
            return;
        }
        (new TpTenantScope())->apply($query, $this);
    }

    /**
     * 模型事件：写入前自动追加 tenant_id
     */
    public static function onBeforeInsert(Model $model):void
    {
        if (!isset($model->tenant_id)) {
            $tenantId = \getCurrentTenantId();
            if ($tenantId) {
                $model->setAttr('tenant_id', $tenantId);
            }
        }
    }
}