<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Security;

use Framework\Utils\JwtFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; // 之前实现的 JWT 工厂

class AuthGuard
{
    protected int $refreshThreshold = 300; // 剩余 <300 秒时自动续期（5分钟）

    private JwtFactory $jwt;

    private ?string $refreshedToken = null;

    public function __construct(JwtFactory $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * 验证请求
     * @return array             用户信息
     * @throws \RuntimeException 验证失败
     */
    public function check(Request $request, ?array $requiredRoles = null): array|Response
    {
        $token = $this->extractToken($request);
        if (! $token) {
            return $this->unauthorized('Missing or invalid token');
        }

        try {
            $parsed = $this->jwt->parse($token);
            if (! $parsed) {
                return $this->unauthorized('Invalid or expired token');
            }

            $claims   = $parsed->claims();
            $userId   = $claims->get('uid');
            $userRole = $claims->get('role') ?? 'user';
            $exp      = $claims->get('exp')->getTimestamp();
            $now      = time();

            // --- 角色验证 ---
            if ($requiredRoles && ! in_array($userRole, $requiredRoles)) {
                return $this->forbidden("Role '{$userRole}' not allowed");
            }

            $remaining = $exp - $now;
            if ($remaining <= 0) {
                // Token 已过期
                return null;
            }

            // --- 自动续期 ---
            $ttl = $exp - $now;
            if ($ttl < $this->refreshThreshold) { // 剩余不到 5 分钟
                $newToken             = $this->jwt->refresh($token);
                $this->refreshedToken = $newToken;
                if ($newToken) {
                    $request->attributes->set('_new_token', $newToken);
                }
            }

            return [
                'id'   => $userId,
                'role' => $userRole,
                'exp'  => $exp,
            ];
        } catch (\Throwable $e) {
            return $this->unauthorized('Token parse error: ' . $e->getMessage());
        }
    }

    public function hasRefreshedToken(): bool
    {
        return $this->refreshedToken !== null;
    }

    public function getRefreshedToken(): ?string
    {
        return $this->refreshedToken;
    }

    private function unauthorized(string $msg): Response
    {
        return new Response('<h1>401 unauthorized:' . $msg . '</h1>');
        /*
        return new Response(json_encode([
            'error' => 'unauthorized',
            'message' => $msg,
            'code' => 401,
        ]), 401, ['Content-Type' => 'application/json']);
        */
    }

    private function forbidden(string $msg): Response
    {
        return new Response('<h1>403 forbidden:' . $msg . '</h1>');
        /*
        return new Response(json_encode([
            'error' => 'forbidden',
            'message' => $msg,
            'code' => 403,
        ]), 403, ['Content-Type' => 'application/json']);
        */
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->headers->get('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        $cookie = app('cookie')->get($request, 'token');
        if ($cookie) {
            return $cookie;
        }

        return null;
    }
}
