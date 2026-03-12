<?php

declare(strict_types=1);

/**
 * 认证控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysUserService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * AuthController 认证控制器
 *
 * 处理用户登录、登出、刷新Token等认证相关操作
 */
class AuthController extends BaseController
{
    /**
     * 用户服务
     * @var SysUserService
     */
    protected SysUserService $userService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->userService = new SysUserService();
    }

    /**
     * 用户登录
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function login(Request $request): BaseJsonResponse
    {
        // 获取登录参数
        $username = $this->input('username', '');
        $password = $this->input('password', '');

        if (empty($username) || empty($password)) {
            return $this->fail('用户名和密码不能为空');
        }

        // 获取客户端IP
        $ip = $request->getClientIp() ?? '';

        // 执行登录
        $result = $this->userService->login($username, $password, $ip);

        if (!$result) {
            return $this->fail('用户名或密码错误，或用户已被禁用');
        }

        // 构建响应
        $response = $this->success([
            'user' => $result['user'],
            'token' => $result['token'],
            'menus' => $result['menus'],
            'permissions' => $result['permissions'],
        ], '登录成功');

        // 设置 Token Cookie (HttpOnly)
        $isSecure = $request->isSecure();
        $sameSite = $isSecure ? 'Strict' : 'Lax';

        $response->headers->setCookie(
            new Cookie(
                'access_token',
                $result['token'],
                time() + 3600,
                '/',
                null,
                $isSecure,
                true,
                false,
                $sameSite
            )
        );

        return $response;
    }

    /**
     * 用户登出
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function logout(Request $request): BaseJsonResponse
    {
        // 清除 Token
        $response = $this->success([], '登出成功');

        // 清除 Cookie
        $response->headers->setCookie(
            new Cookie(
                'access_token',
                '',
                time() - 3600,
                '/',
                null,
                $request->isSecure(),
                true
            )
        );

        $response->headers->setCookie(
            new Cookie(
                'refresh_token',
                '',
                time() - 3600,
                '/',
                null,
                $request->isSecure(),
                true
            )
        );

        return $response;
    }

    /**
     * 获取当前用户信息
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function me(Request $request): BaseJsonResponse
    {
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->fail('未登录', 401);
        }

        $userInfo = $this->userService->getDetail($user['id']);

        if (!$userInfo) {
            return $this->fail('用户不存在', 404);
        }

        return $this->success($userInfo);
    }

    /**
     * 获取当前用户菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    public function menus(Request $request): BaseJsonResponse
    {
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->fail('未登录', 401);
        }

        $sysUser = \App\Models\SysUser::find($user['id']);

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
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->fail('未登录', 401);
        }

        $sysUser = \App\Models\SysUser::find($user['id']);

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
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
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
            $this->userService->changePassword($user['id'], $oldPassword, $newPassword);
            return $this->success([], '密码修改成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
