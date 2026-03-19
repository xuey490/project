<?php

declare(strict_types=1);

/**
 * 多租户认证控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Controllers;

use App\Models\SysUser;
use App\Models\SysTenant;
use App\Models\SysUserTenant;
use App\Models\SysUserRole;
use Framework\Basic\BaseController;
use Framework\Tenant\TenantContext;
use Framework\Tenant\JwtTenantContext;
use Framework\Tenant\SessionTenantContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * TenantAuthController - 多租户认证控制器
 *
 * 处理多租户场景下的登录、登出、租户切换等功能
 */
class TenantAuthController extends BaseController
{
    /**
     * JWT 密钥
     */
    protected string $jwtSecret;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->jwtSecret = config('app.jwt_secret', 'your-secret-key');
    }

    /**
     * 登录（支持租户选择）
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $tenantId = $request->request->get('tenant_id');
        $remember = $request->request->getBoolean('remember', false);

        // 1. 验证用户
        $user = SysUser::where('username', $username)->first();
        if (!$user || !$user->verifyPassword($password)) {
            return $this->error('用户名或密码错误', 401);
        }

        if ($user->isDisabled()) {
            return $this->error('账号已被禁用', 403);
        }

        // 2. 验证租户
        if ($tenantId) {
            // 检查用户是否属于该租户
            $hasTenant = SysUserTenant::isUserInTenant($user->id, $tenantId);

            if (!$hasTenant && !$user->isSuperAdmin()) {
                return $this->error('您不属于该租户', 403);
            }

            // 验证租户有效性
            $tenant = SysTenant::find($tenantId);
            if (!$tenant || !$tenant->isValid()) {
                return $this->error('租户无效或已过期', 403);
            }
        } else {
            // 使用默认租户
            $tenantId = SysUserTenant::getDefaultTenantId($user->id);

            if (!$tenantId && !$user->isSuperAdmin()) {
                return $this->error('请先选择租户', 403);
            }
        }

        // 3. 生成 Token
        $token = JwtTenantContext::generateToken(
            [
                'user_id' => $user->id,
                'username' => $user->username,
                'is_super_admin' => $user->isSuperAdmin(),
            ],
            $tenantId ?? 0,
            $this->jwtSecret,
            $remember ? 604800 : 7200 // 记住我：7天，否则：2小时
        );

        // 4. 可选：同时设置 Session
        if ($request->hasSession()) {
            $tenants = SysUserTenant::getTenantsByUser($user->id);
            SessionTenantContext::setTenantSession($tenantId ?? 0, $user->id, $tenants);
        }

        // 5. 更新登录信息
        $user->updateLoginInfo($request->getClientIp());

        // 6. 返回用户信息
        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $remember ? 604800 : 7200,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'is_admin' => $user->isSuperAdmin(),
            ],
            'tenant_id' => $tenantId,
        ], '登录成功');
    }

    /**
     * 获取用户可访问的租户列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserTenants(Request $request): JsonResponse
    {
        $userId = $this->getCurrentUserId($request);

        if (!$userId) {
            return $this->error('未登录', 401);
        }

        $tenants = SysUserTenant::getTenantsByUser($userId);

        return $this->success($tenants);
    }

    /**
     * 切换租户
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function switchTenant(Request $request): JsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        $newTenantId = $request->request->getInt('tenant_id');

        if (!$userId) {
            return $this->error('未登录', 401);
        }

        // 验证用户是否属于该租户
        if (!SysUserTenant::isUserInTenant($userId, $newTenantId)) {
            return $this->error('您不属于该租户', 403);
        }

        // 验证租户有效性
        $tenant = SysTenant::find($newTenantId);
        if (!$tenant || !$tenant->isValid()) {
            return $this->error('租户无效或已过期', 403);
        }

        // 设置新的默认租户
        SysUserTenant::setDefaultTenant($userId, $newTenantId);

        // 获取用户信息
        $user = SysUser::find($userId);

        // 生成新的 Token
        $token = JwtTenantContext::generateToken(
            [
                'user_id' => $user->id,
                'username' => $user->username,
                'is_super_admin' => $user->isSuperAdmin(),
            ],
            $newTenantId,
            $this->jwtSecret
        );

        // 更新 Session
        if ($request->hasSession()) {
            SessionTenantContext::switchTenant($newTenantId);
        }

        return $this->success([
            'token' => $token,
            'tenant_id' => $newTenantId,
            'tenant_name' => $tenant->tenant_name,
        ], '切换成功');
    }

    /**
     * 登出
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // 清理 Session
        if ($request->hasSession()) {
            SessionTenantContext::clearTenantSession();
        }

        return $this->success(null, '登出成功');
    }

    /**
     * 获取当前登录用户信息
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrentUser(Request $request): JsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        $tenantId = TenantContext::getTenantIdFromRequest($request);

        if (!$userId) {
            return $this->error('未登录', 401);
        }

        $user = SysUser::find($userId);

        if (!$user) {
            return $this->error('用户不存在', 404);
        }

        // 获取当前租户信息
        $tenant = null;
        if ($tenantId) {
            $tenant = SysTenant::find($tenantId);
        }

        // 获取当前租户的角色
        $roles = [];
        if ($tenantId) {
            $roles = SysUserRole::getRoleCodesByTenant($userId, $tenantId);
        }

        return $this->success([
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'avatar' => $user->avatar,
            'is_admin' => $user->isSuperAdmin(),
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->tenant_name,
                'code' => $tenant->tenant_code,
            ] : null,
            'roles' => $roles,
        ]);
    }

    /**
     * 刷新 Token
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        $token = JwtTenantContext::extractTokenFromHeader($authHeader);

        if (!$token) {
            return $this->error('未提供 Token', 401);
        }

        // 验证 Token 是否即将过期
        if (!JwtTenantContext::isTokenExpiringSoon($token, $this->jwtSecret, 300)) {
            return $this->error('Token 尚未需要刷新', 400);
        }

        // 获取用户数据
        $userData = JwtTenantContext::getUserDataFromToken($token, $this->jwtSecret);

        if (!$userData) {
            return $this->error('Token 无效', 401);
        }

        // 生成新 Token
        $newToken = JwtTenantContext::generateToken(
            [
                'user_id' => $userData['user_id'],
                'username' => $userData['username'],
                'is_super_admin' => $userData['is_super_admin'],
            ],
            $userData['tenant_id'] ?? 0,
            $this->jwtSecret
        );

        return $this->success([
            'token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => 7200,
        ]);
    }

    /**
     * 获取当前用户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function getCurrentUserId(Request $request): ?int
    {
        // 从 Request 属性获取
        $userId = $request->attributes->get('_user_id');
        if ($userId) {
            return (int) $userId;
        }

        // 从 JWT Token 解析
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader) {
            $token = JwtTenantContext::extractTokenFromHeader($authHeader);
            if ($token) {
                return JwtTenantContext::getUserIdFromToken($token, $this->jwtSecret);
            }
        }

        // 从 Session 获取
        if ($request->hasSession()) {
            return SessionTenantContext::getUserId();
        }

        return null;
    }

    /**
     * 成功响应
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function success(mixed $data, string $message = 'success'): JsonResponse
    {
        return new JsonResponse([
            'code' => 200,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * 错误响应
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function error(string $message, int $code = 400): JsonResponse
    {
        return new JsonResponse([
            'code' => $code,
            'message' => $message,
            'data' => null,
        ], $code);
    }
}
