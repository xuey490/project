<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Framework\Utils\CookieManager;
use Framework\Utils\Json;
use Framework\Basic\BaseJsonResponse;
use App\Middlewares\AuthMiddleware;
use Framework\Attributes\Auth;
use Framework\Attributes\Route;

class Jwt
{
	private string $tokenString;
	
    public function __construct(
        private readonly CookieManager $cookie
    ) {}
	
	
	public function issue(Request $request): Response
	{
		$userId = 42;

		// 1. access token（短期）
		$access = app('jwt')->issue([
			'uid'  => $userId,
			'name' => '张三',
			'role' => 'admin'
		]);

		// 2. refresh token（长期，仅服务器 + 浏览器）
		$refreshToken = app('jwt')->issueRefreshToken($userId);

		$response = new JsonResponse([
			'code' => 0,
			'data' => [
				'tokenValue' => $access['token'],
				'expireTime'   => $access['ttl'],
				'userId'	 => $userId,
				'userName'	 => '张三',
			],
		]);
		
		$response->headers->set('x-token-refresh', $access['token']);
		
        // 判断是否为 API/Ajax 请求
        $isApi = false;
        if ($request) {
            $accept = $request->headers->get('Accept');
            $ajax   = $request->headers->get('X-Requested-With');
            // 简单的 API 判断逻辑：Accept 头包含 'json' 或 X-Requested-With 是 'XMLHttpRequest' (Ajax)
            $isApi  = str_contains((string) $accept, 'json') || $ajax === 'XMLHttpRequest';
        }

        if ($isApi) {
            // === API / Ajax 场景：添加 Authorization 头 ===
            $response->headers->set('Authorization', 'Bearer ' . $access['token'] );
        } else {
			// ✅ 只写 refresh token（HttpOnly）
			$response->headers->setCookie(
				new Cookie(
					'refresh_token',
					$refreshToken,
					time() + 86400 * 7,
					'/',
					null,
					true,   // secure
					true,   // httponly
					false,
					'Strict'
				)
			);
			
			$response->headers->setCookie(
				new Cookie(
					'access_token',
					$access['token'],
					time() +3600,
					'/',
					null,
					true,   // secure
					true,   // httponly
					false,
					'Strict'
				)
			);		
		}

		return $response;
	}

	//刷新jwt token
	public function refresh(Request $request): Response
	{
		$isSecure = $request->isSecure(); 
		
		$refreshToken = $request->cookies->get('refresh_token');
		if (! $refreshToken) {
			return Json::fail('Refresh token missing', [], 401);
		}

		try {
			// 1. rotation（旧 refresh 立即失效）
			$newRefreshToken = app('jwt')->rotateRefreshToken($refreshToken);

			// 2. 验证并取 uid
			$uid = app('jwt')->validateRefreshToken($newRefreshToken);

			// 3. 签发新的 access token
			$access = app('jwt')->issue([
				'uid' => $uid,
			]);

			$response = BaseJsonResponse::success([
				'access_token' => $access['token'],
				'expires_in'   => $access['ttl'],
			]);
			
			$response->headers->set('x-token-refresh', $access['token']);

			// 判断是否为 API/Ajax 请求
			$isApi = false;
			if ($request) {
				$accept = $request->headers->get('Accept');
				$ajax   = $request->headers->get('X-Requested-With');
				// 简单的 API 判断逻辑：Accept 头包含 'json' 或 X-Requested-With 是 'XMLHttpRequest' (Ajax)
				$isApi  = str_contains((string) $accept, 'json') || $ajax === 'XMLHttpRequest';
			}

			if ($isApi) {
				// === API / Ajax 场景：添加 Authorization 头 ===
				$response->headers->set('Authorization', 'Bearer ' . $access['token'] );
			} else {
				// 4. 只写 refresh token（HttpOnly）
				$response->headers->setCookie(
					new Cookie(
						'refresh_token',
						$newRefreshToken,
						time() + 86400 * 7,
						'/',
						null,
						true,
						true,
						false,
						'Strict'
					)
				);
				
				// 5. 写 access token（HttpOnly）
				$response->headers->setCookie(
					new Cookie(
						'access_token',
						$access['token'],
						time() + $access['ttl'],
						'/',
						null,
						true,
						true,
						false,
						'Strict'
					)
				);
			}

			return $response;

		} catch (\Throwable $e) {
			return BaseJsonResponse::error('Refresh failed', 401);
		}
	}
	

	// 获取当前可用 access_token（必要时自动 refresh）
	public function getJwtToken(Request $request): Response
	{
		$accessToken  = $request->cookies->get('access_token');
		$refreshToken = $request->cookies->get('refresh_token');

		// ① refreshToken 不存在 → 会话无效
		if (empty($refreshToken)) {
			return BaseJsonResponse::error('Refresh Token Missed', 401);
		}

		// ② access_token 仍有效
		if (is_string($accessToken) && $accessToken !== '') {

			$response = BaseJsonResponse::success([
				'access_token' => $accessToken,
			]);

			// 可选：同步 cookie / 滑动过期
			$response->headers->setCookie(
				new Cookie(
					'access_token',
					$accessToken,
					time() + 3600,
					'/',
					null,
					true,
					true,
					false,
					'Strict'
				)
			);

			return $response;
		}

		// ③ access 不在，但 refresh 在 → 静默刷新
		return $this->refresh($request);
	}



	
	//退出登录 清空jwt token
	public function logout(Request $request): Response
	{
		$refreshToken = $request->cookies->get('refresh_token');

		if ($refreshToken) {
			try {
				// 1. 服务端吊销 refresh token（Redis / DB）
				app('jwt')->revokeRefreshToken($refreshToken);
			} catch (\Throwable $e) {
				// 忽略异常，保证登出流程不中断
			}
		}

		$response = Json::success('Logout success');

		// 2. 清空 refresh_token cookie
		$response->headers->setCookie(
			new Cookie(
				'refresh_token',
				'',
				time() - 3600,
				'/',
				null,
				true,
				true,
				false,
				'Strict'
			)
		);

		// 3. 清空 access_token cookie
		$response->headers->setCookie(
			new Cookie(
				'access_token',
				'',
				time() - 3600,
				'/',
				null,
				true,
				true,
				false,
				'Strict'
			)
		);

		return $response;
	}

	


	
	#[Route(path: '/jwts/xss', methods: ['GET'], name: 'jwts.xss', auth: true, roles: ['admin'], middleware: [\App\Middlewares\AuthMiddleware::class] )]
	public function xss():Response
	{
		$response  = (app('response')->setContent('hello world！'));
		return $response;
	}

	/*
	* 从token 解析出用户信息
	*/
	public function getdatas(Request $request):JsonResponse
	{
		$access_token ='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJGc3NQaHAuSW5jIiwianRpIjoiNGE0MzJkZDUyZWY2YmUzZjFiMWI3M2VhZWY2ZTEwMDAiLCJpc3MiOiJGc3NQaHAiLCJpYXQiOjE3NjU2MzE0NzUuODA2MTc0LCJuYmYiOjE3NjU2MzE0NzUuODA2MTc0LCJleHAiOjE3NjU2MzUwNzUuODA2MTc0LCJ1aWQiOjQyfQ.uxQqPIFS5m2kfrUgoXhn0vk36NWFmp3iedoP7bclmXM' ;// $request->cookies->get('access_token');
		
		$userInfo = app('jwt')->getPayload($access_token);
		
		#dump($userInfo);
		
		return new JsonResponse(($userInfo));
	}
	

	public function banner()
	{
		app('jwt')->revokeAllForUser(42);
		return new Response('kick off');
	}
	
	
	

	//获取cookie，cookie字符长度单项为超过4k
	public function getcookie( Request $request):Response
	{

		$token = app('cookie')->get($request , 'token');
		//return new Response('token:'.$token );
		$user = app('jwt')->getPayload($token);
		
		#dump($user);
		
		/*
		#原生cookie操作
		// 1. 获取单个 Cookie（带默认值）
		$username = $request->cookies->get('username', '匿名用户'); // 若 cookie 不存在，返回 "匿名用户"

		// 2. 判断 Cookie 是否存在
		if ($request->cookies->has('token')) {
		$token = $request->cookies->get('token');
		} else {
		$token = '无 token Cookie';
		}

		// 3. 获取整数/布尔类型 Cookie（自动转换）
		#$uid = $request->cookies->getInt('uid', 0); // 非整数返回 0
		#$isVip = $request->cookies->getBoolean('is_vip', false); // 非布尔返回 false

		// 4. 获取所有 Cookie
		#$allCookies = $request->cookies->all();
		#dump($allCookies);
		*/
		return new Response('token:'.$token );
	}

	//清理某个token
	public function revoke():Response
	{
		$token = app('cookie')->get('token');
		app('cookie')->forget('token');	
		if($token){
			app('jwt')->revoke($token);
			return new Response('revoke' );
		}
		return new Response('revoke failed' );
	}
	

	// 退出接口
	public function logout1(Request $request): Response
	{
		$token = app('cookie')->get('token');

		if ($token) {
			app('jwt')->revoke($token);
		}
		
		//清理cookie
		app('cookie')->forget('token');	
		
		$response = new Response('logout succesfully');


		return $response;
	}
	
}
