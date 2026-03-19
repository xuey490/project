<?php

declare(strict_types=1);

/**
 * 操作日志中间件
 *
 * @package App\Middlewares
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Middlewares;

use App\Models\SysOperationLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OperationLogMiddleware 操作日志中间件
 */
class OperationLogMiddleware
{
    /**
     * 排除的路径
     * @var array
     */
    protected array $exceptPaths = [
        '/api/system/operation-log',
        '/api/system/login-log',
        '/api/system/monitor',
        '/api/system/redis',
        '/api/system/database',
    ];

    /**
     * 处理请求
     *
     * @param Request  $request 请求对象
     * @param callable $next    下一个处理器
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);

        // 执行请求
        $response = $next($request);

        // 检查是否需要记录日志
        if (!$this->shouldLog($request)) {
            return $response;
        }

        // 异步记录日志
        $this->recordLog($request, $response, $startTime);

        return $response;
    }

    /**
     * 检查是否需要记录日志
     *
     * @param Request $request 请求对象
     * @return bool
     */
    protected function shouldLog(Request $request): bool
    {
        $path = $request->getPathInfo();

        // 排除GET请求和特定路径
        if ($request->getMethod() === 'GET') {
            return false;
        }

        // 检查排除路径
        foreach ($this->exceptPaths as $exceptPath) {
            if (str_starts_with($path, $exceptPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 记录操作日志
     *
     * @param Request  $request  请求对象
     * @param Response $response 响应对象
     * @param float   $startTime 开始时间
     * @return void
     */
    protected function recordLog(Request $request, Response $response, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000); // 毫秒

        $user = $request->attributes->get('user', []);

        // 获取请求参数
        $params = $request->request->all() ?: $request->query->all() ?: [];
        $params = is_array($params) ? $params : [];
        $paramsString = json_encode($params, JSON_UNESCAPED_SLASHES);

        if (strlen($paramsString) > 2000) {
            $paramsString = substr($paramsString, 0, 2000) . '...';
        }

        // 获取响应结果
        $responseContent = $response->getContent();
        $resultString = is_string($responseContent) ? $responseContent : '';
        if (strlen($resultString) > 2000) {
            $resultString = substr($resultString, 0, 2000) . '...';
        }

        // 获取路由信息
        $route = $request->attributes->get('_route');

        $module = $route['params']['_module'] ?? 'system';
        $routeName = $route['params']['_route_name'] ?? '';

        // 获取客户端信息
        $userAgent = $request->headers->get('User-Agent', '');

        // 获取IP地理位置
        $ip = $request->getClientIp() ?? '';
        $location = $this->getIpLocation($ip);

        // 记录日志
        try {
            SysOperationLog::record([
                'user_id' => $user['id'] ?? 0,
                'username' => $user['username'] ?? '',
                'module' => $module,
                'business_type' => $this->getBusinessType($request->getMethod()),
                'method' => $request->getMethod(),
                'url' => $request->getPathInfo(),
                'route_name' => $routeName,
                'operation_ip' => $ip,
                'operation_location' => $location,
                'request_params' => $paramsString,
                'response_result' => $resultString,
                'status' => $response->getStatusCode() < 400 ? 0 : 1,
                'duration' => $duration,
                'browser' => $this->getBrowser($userAgent),
                'os' => $this->getOs($userAgent),
                'user_agent' => $userAgent,
            ]);
        } catch (\Exception $e) {
            // 日志记录失败不影响正常请求
        }
    }

    /**
     * 获取业务类型
     *
     * @param string $method HTTP方法
     * @return string
     */
    protected function getBusinessType(string $method): string
    {
        return match (strtoupper($method)) {
            'GET' => '查询',
            'POST' => '新增',
            'PUT' => '修改',
            'DELETE' => '删除',
            'PATCH' => '修改',
            default => '其他',
        };
    }

    /**
     * 获取浏览器
     *
     * @param string $userAgent User-Agent
     * @return string
     */
    protected function getBrowser(string $userAgent): string
    {
        if (preg_match('/Chrome/i', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/Safari/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Edge/i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            return 'IE';
        }
        return 'Other';
    }

    /**
     * 获取操作系统
     *
     * @param string $userAgent User-Agent
     * @return string
     */
    protected function getOs(string $userAgent): string
    {
        if (preg_match('/Windows/i', $userAgent)) {
            return 'Windows';
        }
        if (preg_match('/Mac/i', $userAgent)) {
            return 'Mac';
        }
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }
        if (preg_match('/Android/i', $userAgent)) {
            return 'Android';
        }
        if (preg_match('/iOS/i', $userAgent)) {
            return 'iOS';
        }
        return 'Other';
    }

}
