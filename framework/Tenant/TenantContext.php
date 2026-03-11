<?php
declare(strict_types=1);

namespace Framework\Tenant;

/**
 * 租户上下文管理器.
 *
 * 该类提供全局的租户隔离上下文，使用静态方法管理当前请求的租户信息。
 * 支持多租户场景下的数据隔离和权限控制。
 *
 * 主要功能：
 * - 设置和获取当前租户 ID
 * - 控制是否启用租户隔离
 * - 提供忽略租户隔离的临时作用域执行
 * - 限制批量操作的最大影响行数
 *
 * 使用示例：
 * ```php
 * TenantContext::setTenantId(1001);    // 设置当前用户的租户
 * TenantContext::ignore();              // 超管模式：忽略租户隔离
 * TenantContext::restore();             // 恢复租户隔离
 * TenantContext::shouldApplyTenant();   // ORM 判断是否加租户条件
 * ```
 *
 * @package Framework\Tenant
 */
final class TenantContext
{
    /**
     * 当前租户 ID（普通用户登录后设置）.
     *
     * @var int|null
     */
    private static ?int $tenantId = null;

    /**
     * 是否忽略租户隔离标志（超管或系统操作时设置为 true）.
     *
     * @var bool
     */
    private static bool $ignoreTenant = false;

    /**
     * 单次操作最大影响行数限制，防止误操作导致大批量数据变更.
     *
     * @var int
     */
    private static int $maxAffectRows = 100;

    /**
     * 获取最大影响行数限制.
     *
     * @return int 最大影响行数
     */
    public static function maxAffectRows(): int
    {
        return self::$maxAffectRows;
    }

    /**
     * 设置最大影响行数限制.
     *
     * 用于限制批量删除或更新操作的最大影响行数，防止误操作。
     *
     * @param int $limit 最大影响行数
     *
     * @return void
     */
    public static function setMaxAffectRows(int $limit): void
    {
        self::$maxAffectRows = $limit;
    }
	
    /**
     * 设置当前租户 ID.
     *
     * 通常在用户登录成功后调用，用于后续的数据隔离查询。
     *
     * @param int|null $tenantId 租户 ID，传入 null 表示未登录或无租户
     *
     * @return void
     */
    public static function setTenantId(?int $tenantId): void
    {
        self::$tenantId = $tenantId;
    }

    /**
     * 获取当前租户 ID.
     *
     * @return int|null 当前租户 ID，未设置时返回 null
     */
    public static function getTenantId(): ?int
    {
        return self::$tenantId;
    }

    /**
     * 判断是否应启用租户隔离.
     *
     * 当租户 ID 不为空且未设置忽略标志时返回 true。
     * ORM 层据此判断是否自动添加租户过滤条件。
     *
     * @return bool 需要启用租户隔离返回 true，否则返回 false
     */
    public static function shouldApplyTenant(): bool
    {
        return !self::$ignoreTenant && self::$tenantId !== null;
    }

    /**
     * 忽略租户隔离（超管或系统操作模式）.
     *
     * 调用后所有查询将不再添加租户过滤条件。
     * 通常用于超管查看所有数据或系统级操作。
     *
     * @return void
     */
    public static function ignore(): void
    {
        self::$ignoreTenant = true;
    }

    /**
     * 恢复租户隔离.
     *
     * 取消 ignore() 的效果，恢复正常的租户隔离机制。
     *
     * @return void
     */
    public static function restore(): void
    {
        self::$ignoreTenant = false;
    }

    /**
     * 检查当前是否处于忽略租户隔离状态.
     *
     * @return bool 正在忽略租户隔离返回 true，否则返回 false
     */
    public static function isIgnoring(): bool
    {
        return self::$ignoreTenant;
    }
	
    /**
     * 在忽略租户隔离的作用域内安全执行回调函数.
     *
     * 执行期间临时忽略租户隔离，执行完成后自动恢复原状态。
     * 使用 try-finally 确保状态始终被恢复。
     *
     * @param callable $fn 要执行的回调函数
     *
     * @return mixed 回调函数的返回值
     */
    public static function withIgnore(callable $fn)
    {
        $prev = self::$ignoreTenant;

        self::$ignoreTenant = true;

        try {
            return $fn();
        } finally {
            self::$ignoreTenant = $prev;
        }
    }
	
}
/*
TenantContext::setTenantId(1001);   // 当前登录用户的租户
TenantContext::ignore();           // 超管模式
TenantContext::restore();          // 恢复隔离
TenantContext::shouldApplyTenant();// ORM 判断是否加租户条件

*/