<?php
declare(strict_types=1);

namespace Framework\Basic\Traits;

use Closure;
use RuntimeException;

/**
 * 多租户数据自动过滤 Trait
 * 用于 BaseDao 层，自动拼接租户条件
 */
trait TenantFilterTrait
{
    /**
     * 租户字段名（支持子类自定义）
     * @var string
     */
    protected string $tenantField = 'tenant_id';

    /**
     * 是否忽略租户过滤（单次请求有效）
     * @var bool
     */
    protected bool $ignoreTenantFilter = false;

    /**
     * 获取当前租户ID的闭包
     * 需在框架初始化时注入，例如从请求头、上下文、登录信息中获取
     * @var Closure|null
     */
    protected static ?Closure $getTenantIdCallback = null;

    /**
     * 初始化租户回调函数
     * @param Closure $callback 回调函数需返回当前租户ID
     * @return void
     */
    public static function initTenantCallback(Closure $callback): void
    {
        self::$getTenantIdCallback = $callback;
    }

    /**
     * 忽略租户过滤（单次查询有效）
     * 用法：$dao->ignoreTenant()->selectList(...)
     * @return $this
     */
    public function ignoreTenant(): self
    {
        $this->ignoreTenantFilter = true;
        return $this;
    }

    /**
     * 重置租户过滤状态
     * @return void
     */
    protected function resetTenantFilter(): void
    {
        $this->ignoreTenantFilter = false;
    }

    /**
     * 获取当前租户ID
     * @return int|string
     */
    protected function getCurrentTenantId(): int|string
    {
        if (!self::$getTenantIdCallback) {
            throw new RuntimeException('租户ID获取回调未初始化，请调用 ' . static::class . '::initTenantCallback()');
        }
        $tenantId = call_user_func(self::$getTenantIdCallback);
        if ($tenantId === null || $tenantId === '') {
            throw new RuntimeException('当前租户ID为空，无法进行数据过滤');
        }
        return $tenantId;
    }

    /**
     * 自动拼接租户条件
     * @param array $where 原始查询条件
     * @return array 拼接后的查询条件
     */
    protected function autoAddTenantCondition(array $where): array
    {
        // 1. 忽略过滤或无租户字段时，直接返回原条件
        if ($this->ignoreTenantFilter || empty($this->tenantField)) {
            $this->resetTenantFilter();
            return $where;
        }

        // 2. 获取当前租户ID
        $tenantId = $this->getCurrentTenantId();

        // 3. 避免重复添加租户条件
        $hasTenantCondition = false;
        foreach ($where as $key => $value) {
            if (is_string($key) && $key === $this->tenantField) {
                $hasTenantCondition = true;
                break;
            }
        }

        if (!$hasTenantCondition) {
            $where[static::getModel()->getTable().'.'.$this->tenantField] = $tenantId;
        }

        // 4. 重置状态，确保单次忽略只生效一次
        $this->resetTenantFilter();
        return $where;
    }
}