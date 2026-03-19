<?php

declare(strict_types=1);

/**
 * 认证控制器（支持多租户）
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
use App\Services\SysUserService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Tenant\TenantContext;
use Framework\Tenant\JwtTenantContext;
use Framework\Tenant\SessionTenantContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * AuthController 认证控制器（多租户版）
 *
 * 处理用户登录、登出、刷新Token、租户切换等认证相关操作
 * 支持多租户场景下的租户选择和切换
 */
class AuthController extends BaseController
{
    /**
     * 用户服务
     * @var SysUserService
     */
    protected SysUserService $userService;

    /**
     * JWT 配置
     */
    protected array $jwtConfig;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->userService = new SysUserService();
        $this->jwtConfig = config('jwt', []);
    }

    /**
     * 用户登录（支持租户选择）
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse|JsonResponse
     */
    public function login(Request $request): BaseJsonResponse|JsonResponse
    {
        // 获取登录参数
        $username = $this->input('username', '');
        $password = $this->input('password', '');
        $tenantId = $this->input('tenant_id', null);
        $remember = $this->input('remember', false);

        if (empty($username) || empty($password)) {
            return $this->fail('用户名和密码不能为空');
        }

        // 1. 验证用户
        $user = SysUser::where('username', $username)->first();
        if (!$user || !$user->verifyPassword($password)) {
            return $this->fail('用户名或密码错误');
        }

        if ($user->isDisabled()) {
            return $this->fail('账号已被禁用', 403);
        }

        // 2. 验证租户
        if ($tenantId) {
            // 检查用户是否属于该租户
            $hasTenant = SysUserTenant::isUserInTenant($user->id, (int)$tenantId);

            if (!$hasTenant && !$user->isSuperAdmin()) {
                return $this->fail('您不属于该租户', 403);
            }

            // 验证租户有效性
            $tenant = SysTenant::find($tenantId);
            if (!$tenant || !$tenant->isValid()) {
                return $this->fail('租户无效或已过期', 403);
            }
        } else {
            // 使用默认租户
            $tenantId = SysUserTenant::getDefaultTenantId($user->id);

            if (!$tenantId && !$user->isSuperAdmin()) {
                return $this->fail('请先选择租户', 403);
            }
        }

        // 3. 生成 Token（包含租户信息）
        $ttl = $remember ? 604800 : ($this->jwtConfig['ttl'] ?? 3600);
        $tokens = JwtTenantContext::generateLoginTokens([
            'uid' => $user->id,
            'name' => $user->username,
            'nickname' => $user->nickname,
            'tenant_id' => (int)$tenantId,
            'role' => $user->isSuperAdmin() ? 'super_admin' : 'user',
        ], $ttl);

        // 4. 设置 Session（可选）
        if ($request->hasSession()) {
            $tenants = SysUserTenant::getTenantsByUser($user->id);
            SessionTenantContext::setTenantSession((int)$tenantId, $user->id, $tenants);
        }

        // 5. 更新登录信息
        $user->updateLoginInfo($request->getClientIp() ?? '');

        // 6. 获取用户菜单和权限
        $menus = $user->getMenuTree();
        $permissions = $user->getPermissions();

        // 7. 构建响应
        $response = $this->success([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'is_admin' => $user->isSuperAdmin(),
            ],
            'token' => $tokens['token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in' => $tokens['ttl'],
            'tenant_id' => (int)$tenantId,
            'menus' => $menus,
            'permissions' => $permissions,
        ], '登录成功');

        // 8. 设置 Cookie
        $this->setAuthCookies($response, $tokens['token'], $tokens['refresh_token'], $request->isSecure());

        return $response;
    }

    /**
     * 获取用户可访问的租户列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function getUserTenants(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);

        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $tenants = SysUserTenant::getTenantsByUser($userId);

        return $this->success($tenants);
    }

    /**
     * 切换租户
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function switchTenant(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        $newTenantId = (int)$this->input('tenant_id', 0);

        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        // 验证用户是否属于该租户
        if (!SysUserTenant::isUserInTenant($userId, $newTenantId)) {
            return $this->fail('您不属于该租户', 403);
        }

        // 验证租户有效性
        $tenant = SysTenant::find($newTenantId);
        if (!$tenant || !$tenant->isValid()) {
            return $this->fail('租户无效或已过期', 403);
        }

        // 获取用户信息
        $user = SysUser::find($userId);
        if (!$user) {
            return $this->fail('用户不存在', 404);
        }

        // 设置新的默认租户
        SysUserTenant::setDefaultTenant($userId, $newTenantId);

        // 生成新的 Token
        $tokens = JwtTenantContext::generateLoginTokens([
            'uid' => $user->id,
            'name' => $user->username,
            'nickname' => $user->nickname,
            'tenant_id' => $newTenantId,
            'role' => $user->isSuperAdmin() ? 'super_admin' : 'user',
        ]);

        // 更新 Session
        if ($request->hasSession()) {
            SessionTenantContext::switchTenant($newTenantId);
        }

        // 获取新租户下的菜单和权限
        TenantContext::setTenantId($newTenantId);
        $menus = $user->getMenuTree();
        $permissions = $user->getPermissions();

        // 构建响应
        $response = $this->success([
            'token' => $tokens['token'],
            'refresh_token' => $tokens['refresh_token'],
            'expires_in' => $tokens['ttl'],
            'tenant_id' => $newTenantId,
            'tenant_name' => $tenant->tenant_name,
            'menus' => $menus,
            'permissions' => $permissions,
        ], '切换成功');

        // 更新 Cookie
        $this->setAuthCookies($response, $tokens['token'], $tokens['refresh_token'], $request->isSecure());

        return $response;
    }

    /**
     * 刷新 Token
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function refresh(Request $request): BaseJsonResponse
    {
        $refreshToken = $request->cookies->get('refresh_token');

        if (!$refreshToken) {
            return $this->fail('Refresh token 不存在', 401);
        }

        try {
            // 1. 轮换 refresh token（用完即焚）
            $newRefreshToken = JwtTenantContext::rotateRefreshToken($refreshToken);

            // 2. 验证并获取用户ID
            $userId = JwtTenantContext::validateRefreshToken($newRefreshToken);

            // 3. 获取用户信息
            $user = SysUser::find($userId);
            if (!$user) {
                return $this->fail('用户不存在', 404);
            }

            // 4. 获取当前租户
            $tenantId = SessionTenantContext::getTenantId() ??
                       SysUserTenant::getDefaultTenantId($userId) ??
                       0;

            // 5. 签发新的 access token
            $tokens = JwtTenantContext::generateLoginTokens([
                'uid' => $user->id,
                'name' => $user->username,
                'nickname' => $user->nickname,
                'tenant_id' => $tenantId,
                'role' => $user->isSuperAdmin() ? 'super_admin' : 'user',
            ]);

            // 构建响应
            $response = $this->success([
                'token' => $tokens['token'],
                'refresh_token' => $newRefreshToken,
                'expires_in' => $tokens['ttl'],
            ]);

            // 更新 Cookie
            $this->setAuthCookies($response, $tokens['token'], $newRefreshToken, $request->isSecure());

            return $response;

        } catch (\Throwable $e) {
            return $this->fail('Token 刷新失败: ' . $e->getMessage(), 401);
        }
    }

    /**
     * 用户登出
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function logout(Request $request): BaseJsonResponse
    {
        $token = $request->cookies->get('access_token');
        $refreshToken = $request->cookies->get('refresh_token');

        // 1. 吊销 Token
        if ($token) {
            try {
                JwtTenantContext::revokeToken($token);
            } catch (\Throwable $e) {
                // 忽略吊销失败
            }
        }

        // 2. 清理 Session
        if ($request->hasSession()) {
            SessionTenantContext::clearTenantSession();
        }

        // 3. 构建响应
        $response = $this->success([], '登出成功');

        // 4. 清除 Cookie
        $this->clearAuthCookies($response, $request->isSecure());

        return $response;
    }

    /**
     * 获取当前登录用户信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function me(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        $tenantId = TenantContext::getTenantId();

        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $user = SysUser::find($userId);
        if (!$user) {
            return $this->fail('用户不存在', 404);
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
     * 获取当前用户菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function menus(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);

        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $sysUser = SysUser::find($userId);
        if (!$sysUser) {
            return $this->fail('用户不存在', 404);
        }

        $menus = $sysUser->getMenuTree();

        return $this->success($menus);
    }

    /**
     * 获取当前用户权限
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function permissions(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);

        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $sysUser = SysUser::find($userId);
        if (!$sysUser) {
            return $this->fail('用户不存在', 404);
        }

        $permissions = $sysUser->getPermissions();

        return $this->success($permissions);
    }

    /**
     * 修改密码
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function changePassword(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);

        if (!$userId) {
            return $this->fail('未登录', 401);
        }

        $oldPassword = $this->input('old_password', '');
        $newPassword = $this->input('new_password', '');

        if (empty($oldPassword) || empty($newPassword)) {
            return $this->fail('旧密码和新密码不能为空');
        }

        if (strlen($newPassword) < 6) {
            return $this->fail('新密码长度不能少于6位');
        }

        try {
            $this->userService->changePassword($userId, $oldPassword, $newPassword);
            return $this->success([], '密码修改成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 辅助方法 ====================

    /**
     * 获取当前用户ID
     *
     * @param Request $request
     * @return int|null
     */
    protected function getCurrentUserId(Request $request): ?int
    {
        // 从 Request 属性获取
        $userId = $request->attributes->get('user')['id'] ?? null;
        if ($userId) {
            return (int)$userId;
        }

        // 从 JWT Token 解析
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader) {
            $token = JwtTenantContext::extractTokenFromHeader($authHeader);
            if ($token) {
                return JwtTenantContext::getUserIdFromToken($token);
            }
        }

        // 从 Cookie 获取
        $token = $request->cookies->get('access_token');
        if ($token) {
            return JwtTenantContext::getUserIdFromToken($token);
        }

        // 从 Session 获取
        if ($request->hasSession()) {
            return SessionTenantContext::getUserId();
        }

        return null;
    }

    /**
     * 设置认证 Cookie
     *
     * @param BaseJsonResponse $response
     * @param string $accessToken
     * @param string $refreshToken
     * @param bool $isSecure
     * @return void
     */
    protected function setAuthCookies(
        BaseJsonResponse $response,
        string $accessToken,
        string $refreshToken,
        bool $isSecure
    ): void {
        $sameSite = $isSecure ? 'Strict' : 'Lax';

        // Access Token Cookie
        $response->headers->setCookie(
            new Cookie(
                'access_token',
                $accessToken,
                time() + 3600,
                '/',
                null,
                $isSecure,
                true,
                false,
                $sameSite
            )
        );

        // Refresh Token Cookie
        $response->headers->setCookie(
            new Cookie(
                'refresh_token',
                $refreshToken,
                time() + 86400 * 7,
                '/',
                null,
                $isSecure,
                true,
                false,
                $sameSite
            )
        );
    }

    /**
     * 清除认证 Cookie
     *
     * @param BaseJsonResponse $response
     * @param bool $isSecure
     * @return void
     */
    protected function clearAuthCookies(BaseJsonResponse $response, bool $isSecure): void
    {
        // 清除 Access Token
        $response->headers->setCookie(
            new Cookie(
                'access_token',
                '',
                time() - 3600,
                '/',
                null,
                $isSecure,
                true
            )
        );

        // 清除 Refresh Token
        $response->headers->setCookie(
            new Cookie(
                'refresh_token',
                '',
                time() - 3600,
                '/',
                null,
                $isSecure,
                true
            )
        );
    }
}
