<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: JwtTenantContext.php
 * @Date: 2026-03-19
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Tenant;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * JWT Token 租户上下文管理器
 *
 * 提供基于 JWT Token 的租户信息管理能力，支持：
 * - 生成包含租户ID的 JWT Token
 * - 从 JWT Token 解析租户ID
 * - Token 刷新和验证
 *
 * 适用于无状态 API 场景，租户信息存储在 Token 中，
 * 不依赖 Session，适合分布式部署和 Workerman 环境。
 *
 * Token 结构：
 * ```
 * {
 *   "iss": "fssphp",           // 签发者
 *   "iat": 1234567890,         // 签发时间
 *   "exp": 1234571490,         // 过期时间
 *   "sub": 1001,               // 用户ID
 *   "tenant_id": 2001,         // 租户ID
 *   "username": "admin",       // 用户名
 *   "is_super_admin": false    // 是否超管
 * }
 * ```
 *
 * @package Framework\Tenant
 */
final class JwtTenantContext
{
    /**
     * Token 签发者
     */
    private const ISSUER = 'fssphp';

    /**
     * 默认 Token 有效期（秒）
     */
    private const DEFAULT_TTL = 7200; // 2小时

    /**
     * 刷新 Token 有效期（秒）
     */
    private const REFRESH_TTL = 604800; // 7天

    /**
     * 生成访问 Token（包含租户信息）
     *
     * @param array $userData 用户数据，必须包含：
     *   - user_id: 用户ID
     *   - username: 用户名
     *   - tenant_id: 租户ID
     *   - is_super_admin: 是否超管（可选）
     * @param string $secret JWT 密钥
     * @param int|null $ttl Token 有效期（秒），默认 2 小时
     * @return string JWT Token
     */
    public static function generateToken(array $userData, string $secret, ?int $ttl = null): string
    {
        $now = time();
        $expire = $now + ($ttl ?? self::DEFAULT_TTL);

        $payload = [
            'iss' => self::ISSUER,
            'iat' => $now,
            'exp' => $expire,
            'sub' => $userData['user_id'],
            'tenant_id' => $userData['tenant_id'],
            'username' => $userData['username'],
        ];

        // 可选字段
        if (isset($userData['is_super_admin'])) {
            $payload['is_super_admin'] = $userData['is_super_admin'];
        }

        if (isset($userData['email'])) {
            $payload['email'] = $userData['email'];
        }

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * 生成刷新 Token
     *
     * 刷新 Token 只包含用户ID，用于获取新的访问 Token
     *
     * @param int $userId 用户ID
     * @param string $secret JWT 密钥
     * @return string 刷新 Token
     */
    public static function generateRefreshToken(int $userId, string $secret): string
    {
        $now = time();

        $payload = [
            'iss' => self::ISSUER,
            'iat' => $now,
            'exp' => $now + self::REFRESH_TTL,
            'sub' => $userId,
            'type' => 'refresh',
        ];

        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * 解析 Token 获取完整 Payload
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @return object Payload 对象
     * @throws \Exception Token 无效或过期时抛出
     */
    public static function decodeToken(string $token, string $secret): object
    {
        return JWT::decode($token, new Key($secret, 'HS256'));
    }

    /**
     * 从 Token 获取租户ID
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @return int|null 租户ID，解析失败返回 null
     */
    public static function getTenantIdFromToken(string $token, string $secret): ?int
    {
        try {
            $payload = self::decodeToken($token, $secret);
            return $payload->tenant_id ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 从 Token 获取用户ID
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @return int|null 用户ID，解析失败返回 null
     */
    public static function getUserIdFromToken(string $token, string $secret): ?int
    {
        try {
            $payload = self::decodeToken($token, $secret);
            return $payload->sub ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 从 Token 获取完整用户信息
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @return array|null 用户信息数组，解析失败返回 null
     */
    public static function getUserDataFromToken(string $token, string $secret): ?array
    {
        try {
            $payload = self::decodeToken($token, $secret);

            return [
                'user_id' => $payload->sub ?? null,
                'tenant_id' => $payload->tenant_id ?? null,
                'username' => $payload->username ?? null,
                'is_super_admin' => $payload->is_super_admin ?? false,
                'email' => $payload->email ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 验证 Token 是否有效
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @return bool 有效返回 true
     */
    public static function validateToken(string $token, string $secret): bool
    {
        try {
            self::decodeToken($token, $secret);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查 Token 是否即将过期
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @param int $threshold 提前阈值（秒），默认 300 秒（5分钟）
     * @return bool 即将过期返回 true
     */
    public static function isTokenExpiringSoon(string $token, string $secret, int $threshold = 300): bool
    {
        try {
            $payload = self::decodeToken($token, $secret);
            $exp = $payload->exp ?? 0;
            return (time() + $threshold) >= $exp;
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * 刷新访问 Token
     *
     * @param string $refreshToken 刷新 Token
     * @param string $secret JWT 密钥
     * @param callable $getUserData 获取用户数据的回调函数，参数为 userId，返回用户数据数组
     * @return string|null 新的访问 Token，刷新失败返回 null
     */
    public static function refreshAccessToken(string $refreshToken, string $secret, callable $getUserData): ?string
    {
        try {
            $payload = self::decodeToken($refreshToken, $secret);

            // 验证是否为刷新 Token
            if (($payload->type ?? '') !== 'refresh') {
                return null;
            }

            $userId = $payload->sub;
            $userData = $getUserData($userId);

            if (!$userData) {
                return null;
            }

            return self::generateToken($userData, $secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 从 Authorization Header 提取 Token
     *
     * @param string|null $authHeader Authorization Header 值
     * @return string|null Token 字符串，未找到返回 null
     */
    public static function extractTokenFromHeader(?string $authHeader): ?string
    {
        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * 获取 Token 过期时间
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @return int|null 过期时间戳，解析失败返回 null
     */
    public static function getTokenExpireTime(string $token, string $secret): ?int
    {
        try {
            $payload = self::decodeToken($token, $secret);
            return $payload->exp ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取 Token 剩余有效时间
     *
     * @param string $token JWT Token
     * @param string $secret JWT 密钥
     * @return int 剩余秒数，已过期返回 0，解析失败返回 -1
     */
    public static function getTokenRemainingTime(string $token, string $secret): int
    {
        try {
            $payload = self::decodeToken($token, $secret);
            $exp = $payload->exp ?? 0;
            $remaining = $exp - time();
            return max(0, $remaining);
        } catch (ExpiredException $e) {
            return 0;
        } catch (\Exception $e) {
            return -1;
        }
    }

    /**
     * 解析 Token（不验证签名，仅用于调试）
     *
     * @param string $token JWT Token
     * @return array|null Payload 数组，解析失败返回 null
     */
    public static function decodeTokenWithoutVerify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        return $payload ?: null;
    }
}
