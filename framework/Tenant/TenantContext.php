<?php
declare(strict_types=1);

namespace Framework\Tenant;

final class TenantContext
{
    /** 当前租户 ID（普通用户） */
    private static ?int $tenantId = null;

    /** 是否忽略租户隔离（超管 / 系统） */
    private static bool $ignoreTenant = false;

	/*  最多一次删除的影响函数 */
    private static int $maxAffectRows = 100;

    public static function maxAffectRows(): int
    {
        return self::$maxAffectRows;
    }

    public static function setMaxAffectRows(int $limit): void
    {
        self::$maxAffectRows = $limit;
    }
	
    /**
     * 设置当前租户（登录后调用）
     */
    public static function setTenantId(?int $tenantId): void
    {
        self::$tenantId = $tenantId;
    }

    /**
     * 获取当前租户 ID
     */
    public static function getTenantId(): ?int
    {
        return self::$tenantId;
    }

    /**
     * 是否启用租户隔离
     */
    public static function shouldApplyTenant(): bool
    {
        return !self::$ignoreTenant && self::$tenantId !== null;
    }

    /**
     * 超管 / 系统：忽略租户隔离
     */
    public static function ignore(): void
    {
        self::$ignoreTenant = true;
    }

    /**
     * 恢复租户隔离（一般不常用）
     */
    public static function restore(): void
    {
        self::$ignoreTenant = false;
    }

    /**
     * 是否正在忽略租户隔离
     */
    public static function isIgnoring(): bool
    {
        return self::$ignoreTenant;
    }
}
