<?php
namespace App\Middlewares;

use App\Core\Auth\CasbinRbac;
use Closure;

/**
 * Casbin RBAC 路由权限校验中间件
 * 适配自研框架，自动拦截无权限请求
 */
class CasbinRbacMiddleware
{
    /**
     * Casbin 权限核心服务
     * @var CasbinRbac
     */
    protected $casbinRbac;

    /**
     * 无需校验的路由白名单
     * @var array
     */
    protected $whiteRoutes = [
        '/api/login',
        '/api/register',
        '/api/refresh-token',
        // 其他公开接口...
    ];

    public function __construct()
    {
        // 从自研框架容器获取 Casbin 服务实例
        $this->casbinRbac = App(CasbinRbac::class);
    }

    /**
     * 中间件处理逻辑
     * @param mixed $request 自研框架请求对象
     * @param Closure $next 下一个中间件/控制器
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 1. 获取当前请求的资源路径和方法
        $currentPath = $this->normalizePath($request->getPathInfo()); // 如 /api/user/list
        $currentMethod = strtoupper($request->getMethod()); // 如 GET/POST

        // 2. 白名单路由直接放行
        if (in_array($currentPath, $this->whiteRoutes)) {
            return $next($request);
        }

        // 3. 获取当前登录用户 ID 和租户 ID（需适配自研框架的用户认证逻辑）
        $userId = $this->getCurrentUserId($request);
        $tenantId = $this->getCurrentTenantId($request);

        // 4. 用户未登录，直接返回未授权
        if (empty($userId)) {
            return $this->responseError(401, '请先登录');
        }

        // 5. 调用 Casbin 进行权限校验
        $isAllowed = $this->casbinRbac->checkPermission(
            $userId,
            $currentPath,
            $currentMethod,
            $tenantId
        );

        // 6. 无权限，返回 403
        if (!$isAllowed) {
            return $this->responseError(403, '无权限访问该接口');
        }

        // 7. 有权限，继续执行后续逻辑
        return $next($request);
    }

    /**
     * 标准化路径（去除末尾斜杠、统一小写）
     * @param string $path
     * @return string
     */
    protected function normalizePath(string $path): string
    {
        $path = rtrim($path, '/');
        return empty($path) ? '/' : $path;
    }

    /**
     * 获取当前登录用户 ID（需适配自研框架的用户认证）
     * @param mixed $request
     * @return string|int|null
     */
    protected function getCurrentUserId($request)
    {
        // 示例：从请求头 Token 解析用户 ID，需替换为自研框架的实际逻辑
        $token = $request->header('Authorization', '');
        if (empty($token)) {
            return null;
        }
        // 假设存在 Token 解析服务
        $userInfo = App('token')->parseToken(substr($token, 7)); // 去除 Bearer 前缀
        return $userInfo['id'] ?? null;
    }

    /**
     * 获取当前租户 ID（适配 SaaS 多租户场景）
     * @param mixed $request
     * @return string
     */
    protected function getCurrentTenantId($request)
    {
        // 租户 ID 获取方式可选：
        // 方式1：从请求头获取（推荐，如 X-Tenant-ID）
        $tenantId = $request->header('X-Tenant-ID', 'default');
        // 方式2：从用户信息中获取（用户绑定租户）
        // $tenantId = $this->getCurrentUserId($request) ? $userInfo['tenant_id'] : 'default';
        return $tenantId;
    }

    /**
     * 统一错误响应格式
     * @param int $code 状态码
     * @param string $msg 错误信息
     * @return mixed
     */
    protected function responseError(int $code, string $msg)
    {
        // 适配自研框架的响应方法，如 json()
        return App('response')->json([
            'code' => $code,
            'msg' => $msg,
            'data' => null
        ]);
    }
}
