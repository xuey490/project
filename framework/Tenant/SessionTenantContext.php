<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: SessionTenantContext.php
 * @Date: 2026-03-19
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Tenant;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Session/Cookie 租户上下文管理器
 *
 * 提供基于 Symfony Session 的租户信息管理能力，支持：
 * - 将租户ID存储在 Session 中
 * - 支持 Cookie 持久化
 * - 与 Symfony 的 Session 机制完全兼容
 *
 * 适用于传统 Web 应用，租户信息存储在服务端 Session 中，
 * 通过 Cookie 中的 Session ID 关联。
 *
 * Session 数据结构：
 * ```
 * $_SESSION = [
 *   '_tenant' => [
 *     'tenant_id' => 2001,        // 当前租户ID
 *     'user_id' => 1001,          // 当前用户ID
 *     'login_time' => 1234567890, // 登录时间
 *   ]
 * ]
 * ```
 *
 * @package Framework\Tenant
 */
final class SessionTenantContext
{
    /**
     * Session 命名空间键名
     */
    private const SESSION_NAMESPACE = '_tenant';

    /**
     * Session 中存储租户ID的键名
     */
    private const TENANT_ID_KEY = 'tenant_id';

    /**
     * Session 中存储用户ID的键名
     */
    private const USER_ID_KEY = 'user_id';

    /**
     * Session 中存储登录时间的键名
     */
    private const LOGIN_TIME_KEY = 'login_time';

    /**
     * Session 中存储租户列表的键名
     */
    private const TENANTS_KEY = 'tenants';

    /**
     * RequestStack 实例
     */
    private static ?RequestStack $requestStack = null;

    /**
     * 设置 RequestStack
     *
     * @param RequestStack $requestStack Symfony RequestStack
     */
    public static function setRequestStack(RequestStack $requestStack): void
    {
        self::$requestStack = $requestStack;
    }

    /**
     * 获取当前 Session
     *
     * @return SessionInterface|null Session 实例
     */
    public static function getSession(): ?SessionInterface
    {
        if (self::$requestStack !== null) {
            $request = self::$requestStack->getCurrentRequest();
            if ($request !== null && $request->hasSession()) {
                return $request->getSession();
            }
        }
        return null;
    }

    /**
     * 设置租户信息到 Session
     *
     * @param int $tenantId 租户ID
     * @param int $userId 用户ID
     * @param array $tenants 用户可访问的租户列表（可选）
     * @return void
     */
    public static function setTenantSession(int $tenantId, int $userId, array $tenants = []): void
    {
        $session = self::getSession();
        if ($session === null) {
            return;
        }

        $session->set(self::SESSION_NAMESPACE, [
            self::TENANT_ID_KEY => $tenantId,
            self::USER_ID_KEY => $userId,
            self::LOGIN_TIME_KEY => time(),
            self::TENANTS_KEY => $tenants,
        ]);
    }

    /**
     * 获取当前租户ID
     *
     * @return int|null 租户ID，未设置返回 null
     */
    public static function getTenantId(): ?int
    {
        $session = self::getSession();
        if ($session === null) {
            return null;
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);
        return $data[self::TENANT_ID_KEY] ?? null;
    }

    /**
     * 获取当前用户ID
     *
     * @return int|null 用户ID，未设置返回 null
     */
    public static function getUserId(): ?int
    {
        $session = self::getSession();
        if ($session === null) {
            return null;
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);
        return $data[self::USER_ID_KEY] ?? null;
    }

    /**
     * 获取登录时间
     *
     * @return int|null 登录时间戳，未设置返回 null
     */
    public static function getLoginTime(): ?int
    {
        $session = self::getSession();
        if ($session === null) {
            return null;
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);
        return $data[self::LOGIN_TIME_KEY] ?? null;
    }

    /**
     * 获取用户可访问的租户列表
     *
     * @return array 租户列表，未设置返回空数组
     */
    public static function getUserTenants(): array
    {
        $session = self::getSession();
        if ($session === null) {
            return [];
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);
        return $data[self::TENANTS_KEY] ?? [];
    }

    /**
     * 切换当前租户
     *
     * @param int $tenantId 新的租户ID
     * @return bool 切换成功返回 true
     */
    public static function switchTenant(int $tenantId): bool
    {
        $session = self::getSession();
        if ($session === null) {
            return false;
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);

        // 检查用户是否有权限访问该租户
        $tenants = $data[self::TENANTS_KEY] ?? [];
        $hasAccess = false;

        foreach ($tenants as $tenant) {
            if ($tenant['id'] === $tenantId) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            return false;
        }

        // 更新当前租户
        $data[self::TENANT_ID_KEY] = $tenantId;
        $session->set(self::SESSION_NAMESPACE, $data);

        return true;
    }

    /**
     * 更新租户列表
     *
     * @param array $tenants 租户列表
     * @return void
     */
    public static function updateTenants(array $tenants): void
    {
        $session = self::getSession();
        if ($session === null) {
            return;
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);
        $data[self::TENANTS_KEY] = $tenants;
        $session->set(self::SESSION_NAMESPACE, $data);
    }

    /**
     * 检查 Session 中是否有租户信息
     *
     * @return bool 有租户信息返回 true
     */
    public static function hasTenantSession(): bool
    {
        return self::getTenantId() !== null;
    }

    /**
     * 清理租户 Session
     *
     * @return void
     */
    public static function clearTenantSession(): void
    {
        $session = self::getSession();
        if ($session !== null) {
            $session->remove(self::SESSION_NAMESPACE);
        }
    }

    /**
     * 获取 Session 剩余有效时间
     *
     * @return int 剩余秒数，无 Session 返回 0
     */
    public static function getSessionRemainingTime(): int
    {
        $session = self::getSession();
        if ($session === null) {
            return 0;
        }

        // Symfony Session 的元数据包含有过期时间
        if (method_exists($session, 'getMetadataBag')) {
            $metadata = $session->getMetadataBag();
            if ($metadata) {
                $created = $metadata->getCreated();
                $lifetime = $metadata->getLifetime();
                $remaining = $created + $lifetime - time();
                return max(0, $remaining);
            }
        }

        return 0;
    }

    /**
     * 检查 Session 是否即将过期
     *
     * @param int $threshold 提前阈值（秒），默认 300 秒
     * @return bool 即将过期返回 true
     */
    public static function isSessionExpiringSoon(int $threshold = 300): bool
    {
        return self::getSessionRemainingTime() <= $threshold;
    }

    /**
     * 获取完整的租户 Session 数据
     *
     * @return array Session 数据，无 Session 返回空数组
     */
    public static function getAllSessionData(): array
    {
        $session = self::getSession();
        if ($session === null) {
            return [];
        }

        return $session->get(self::SESSION_NAMESPACE, []);
    }

    /**
     * 设置 Session 数据项
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @return void
     */
    public static function setSessionData(string $key, mixed $value): void
    {
        $session = self::getSession();
        if ($session === null) {
            return;
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);
        $data[$key] = $value;
        $session->set(self::SESSION_NAMESPACE, $data);
    }

    /**
     * 获取 Session 数据项
     *
     * @param string $key 键名
     * @param mixed $default 默认值
     * @return mixed 值，不存在返回默认值
     */
    public static function getSessionData(string $key, mixed $default = null): mixed
    {
        $session = self::getSession();
        if ($session === null) {
            return $default;
        }

        $data = $session->get(self::SESSION_NAMESPACE, []);
        return $data[$key] ?? $default;
    }

    /**
     * 将 Session 租户信息同步到 TenantContext
     *
     * 用于在请求开始时将 Session 中的租户信息设置到 Request 属性
     *
     * @return void
     */
    public static function syncToTenantContext(): void
    {
        $tenantId = self::getTenantId();
        $userId = self::getUserId();

        if ($tenantId !== null) {
            TenantContext::setTenantId($tenantId);
        }

        if ($userId !== null) {
            TenantContext::setUserId($userId);
        }
    }
}
