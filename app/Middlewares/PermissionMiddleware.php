<?php

declare(strict_types=1);

/**
 * 权限验证中间件
 *
 * @package App\Middlewares
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Middlewares;

use App\Models\SysUser;
use App\Services\Casbin\CasbinService;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionMiddleware 权限验证中间件
 *
 * 基于 Casbin 实现权限验证
 */
class PermissionMiddleware
{
    /**
     * Casbin 服务
     * @var CasbinService
     */
    protected CasbinService $casbinService;

    /**
     * 白名单路径 (不需要权限验证)
     * @var array
     */
    protected array $whiteList = [
        '/api/auth/login',
        '/api/auth/logout',
        '/api/auth/refresh',
        '/api/captcha',
        '/api/public',
    ];

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->casbinService = new CasbinService();
    }

    /**
     * 处理请求
     *
     * @param Request  $request 请求对象
     * @param callable $next    下一个处理器
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 获取请求路径
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        // 检查是否在白名单中
        if ($this->isWhiteListed($path)) {
            return $next($request);
        }

        // 获取用户信息 (由 AuthMiddleware 注入)
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->unauthorized('请先登录');
        }

        $userId = (int)$user['id'];

        // 检查用户状态
        $sysUser = SysUser::find($userId);
        if (!$sysUser || $sysUser->status === SysUser::STATUS_DISABLED) {
            return $this->forbidden('用户已被禁用');
        }

        // 超级管理员跳过权限验证
        if ($sysUser->isSuperAdmin()) {
            return $next($request);
        }

        // 执行权限验证
        if (!$this->checkPermission($userId, $path, $method)) {
            return $this->forbidden('无权限访问');
        }

        // 通过验证，继续执行
        return $next($request);
    }

    /**
     * 检查是否在白名单中
     *
     * @param string $path 请求路径
     * @return bool
     */
    protected function isWhiteListed(string $path): bool
    {
        foreach ($this->whiteList as $whitePath) {
            if (str_starts_with($path, $whitePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查权限
     *
     * @param int    $userId 用户ID
     * @param string $path   请求路径
     * @param string $method 请求方法
     * @return bool
     */
    protected function checkPermission(int $userId, string $path, string $method): bool
    {
        return $this->casbinService->checkPermission($userId, $path, $method);
    }

    /**
     * 返回未授权响应
     *
     * @param string $message 消息
     * @return Response
     */
    protected function unauthorized(string $message): Response
    {
        return new Response(
            json_encode([
                'code' => 401,
                'message' => $message,
                'data' => null,
            ]),
            401,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * 返回禁止访问响应
     *
     * @param string $message 消息
     * @return Response
     */
    protected function forbidden(string $message): Response
    {
        return new Response(
            json_encode([
                'code' => 403,
                'message' => $message,
                'data' => null,
            ]),
            403,
            ['Content-Type' => 'application/json']
        );
    }
}
