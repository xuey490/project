<?php

declare(strict_types=1);

/**
 * 租户上下文中间件
 *
 * @package App\Middlewares
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Tenant\TenantContext;
use Framework\Tenant\JwtTenantContext;
use Framework\Tenant\SessionTenantContext;
use Framework\Middleware\MiddlewareInterface;

/**
 * TenantMiddleware - 租户上下文中间件
 *
 * 负责从请求中解析租户信息并设置租户上下文。
 * 支持 JWT Token 和 Session 两种模式。
 */
class TenantMiddleware implements MiddlewareInterface
{
    /**
     * JWT 密钥
     */
    private string $jwtSecret;

    /**
     * 认证模式：jwt | session
     */
    private string $authMode;

    /**
     * 构造函数
     *
     * @param string $jwtSecret JWT 密钥
     * @param string $authMode 认证模式
     */
    public function __construct(string $jwtSecret = '', string $authMode = 'jwt')
    {
        $this->jwtSecret = $jwtSecret;
        $this->authMode = $authMode;
    }

    /**
     * 处理请求
     *
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 解析租户ID
        $tenantId = $this->resolveTenantId($request);
        $userId = $this->resolveUserId($request);

        // 设置租户上下文
        if ($tenantId !== null) {
            TenantContext::setTenantIdToRequest($request, $tenantId);
        }

        if ($userId !== null) {
            $request->attributes->set('_user_id', $userId);
        }

        // 执行后续中间件
        $response = $next($request);

        // 清理租户上下文（可选）
        // TenantContext::clear();

        return $response;
    }

    /**
     * 解析租户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveTenantId(Request $request): ?int
    {
        // 方式1：从 JWT Token 解析
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader) {
            $token = JwtTenantContext::extractTokenFromHeader($authHeader);
            if ($token && $this->jwtSecret) {
                $tenantId = JwtTenantContext::getTenantIdFromToken($token, $this->jwtSecret);
                if ($tenantId !== null) {
                    return $tenantId;
                }
            }
        }

        // 方式2：从 Session 获取
        if ($request->hasSession()) {
            $sessionTenantId = SessionTenantContext::getTenantId();
            if ($sessionTenantId !== null) {
                return $sessionTenantId;
            }
        }

        // 方式3：从自定义 Header 获取
        $tenantHeader = $request->headers->get('X-Tenant-ID');
        if ($tenantHeader && is_numeric($tenantHeader)) {
            return (int) $tenantHeader;
        }

        // 方式4：从 Query 参数获取（仅用于开发调试）
        $tenantParam = $request->query->get('tenant_id');
        if ($tenantParam && is_numeric($tenantParam)) {
            return (int) $tenantParam;
        }

        return null;
    }

    /**
     * 解析用户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function resolveUserId(Request $request): ?int
    {
        // 方式1：从 JWT Token 解析
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader) {
            $token = JwtTenantContext::extractTokenFromHeader($authHeader);
            if ($token && $this->jwtSecret) {
                $userId = JwtTenantContext::getUserIdFromToken($token, $this->jwtSecret);
                if ($userId !== null) {
                    return $userId;
                }
            }
        }

        // 方式2：从 Session 获取
        if ($request->hasSession()) {
            $sessionUserId = SessionTenantContext::getUserId();
            if ($sessionUserId !== null) {
                return $sessionUserId;
            }
        }

        return null;
    }
}
