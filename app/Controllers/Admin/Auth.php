<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\SysLoginLog;
use App\Models\SysUser;
use App\Services\SysAuthService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Auth
{
    #[Autowire]
    protected SysAuthService $authService;

    public function login(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            $payload = $request->request->all();
        }

        $username = (string) ($payload['username'] ?? $payload['user_name'] ?? '');
        $password = (string) ($payload['password'] ?? '');

        $ip = (string) ($request->getClientIp() ?? '');
        $userAgent = $request->headers->get('User-Agent');

        try {
            if ($username === '' || $password === '') {
                throw new \RuntimeException('用户名或密码不能为空');
            }

            $token = $this->authService->login($username, $password);

            $user = SysUser::with(['roles'])->where('user_name', $username)->first();
            $uid = (int) ($user?->id ?? 0);

            // 记录登录日志
            SysLoginLog::create([
                'user_id' => $uid ?: null,
                'user_name' => $username,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'status' => 1,
                'message' => '登录成功',
                'login_time' => date('Y-m-d H:i:s'),
            ]);

            return BaseJsonResponse::success([
                'token' => $token['token'],
            ], '登录成功');

        } catch (\Throwable $e) {
            SysLoginLog::create([
                'user_id' => null,
                'user_name' => $username,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'status' => 0,
                'message' => $e->getMessage(),
                'login_time' => date('Y-m-d H:i:s'),
            ]);

            return BaseJsonResponse::fail($e->getMessage());
        }
    }

    public function logout(Request $request): Response
    {
        // 这里可以做一些 Token 黑名单处理
        return BaseJsonResponse::success([], '退出成功');
    }
    
    public function getInfo(Request $request): Response
    {
        $user = $request->attributes->get('current_user');
        if (!$user) {
            return BaseJsonResponse::unauthorized();
        }
        
        $data = $this->authService->getUserInfo($user->id);
        return BaseJsonResponse::success($data);
    }
    
    public function getRouters(Request $request): Response
    {
        $user = $request->attributes->get('current_user');
        if (!$user) {
            return BaseJsonResponse::unauthorized();
        }
        
        $data = $this->authService->getRouters($user->id);
        return BaseJsonResponse::success($data);
    }

    public function getPermCode(Request $request): Response
    {
        $user = $request->attributes->get('current_user');
        if (!$user) {
            return BaseJsonResponse::unauthorized();
        }
        
        $data = $this->authService->getUserInfo($user->id);
        return BaseJsonResponse::success($data['permissions']);
    }

    public function getUserPermissions(Request $request): Response
    {
        return $this->getPermCode($request);
    }
}
