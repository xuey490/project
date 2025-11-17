<?php

declare(strict_types=1);

/**
 * This file is part of NovaFrame.
 *
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;
use Framework\Utils\CookieManager;
use App\Middlewares\AuthMiddleware;
use Framework\Attributes\Auth;


#[Route(prefix: '/jwts/apijwt', group: 'apijwt' , middleware: [AuthMiddleware::class] )]
class Jwt
{
	private string $tokenString;
	
    public function __construct(
        private readonly CookieManager $cookie
    ) {}
	
	
	public function issue(Request $request)
	{
		
		#$response = app('response')->setContent('Hello NovaPHP!');
		$response = new Response('非常复杂的html内容:'); // 可传空字符串
		
		// 登录页面登录-->获取uid，role，name-->签发token-->token存入cookie/缓存-->到下一个页面的时候
		//-->中间件请求头（或 Cookie）中提取 Token，验证 JWT 签名、issuer、exp、nbf 等标准 claims，再验证Redis 中是否存在 login:token:{jti}（用于判断是否被提前注销）-->验证失败，跳转到登录，
		$this->tokenString = app('jwt')->issue(['uid' => 42, 'name'=>'张三' ,'role'=>'admin']);
		$token = "  Token: {$this->tokenString}<br/>";


		
		//app('cookie')->queueCookie('token', $this->tokenString, 3600);
		app('cookie')->queueCookie('token_123', 'hello world', 3600); //适合FPM 和部分workerman启动器

		// 快捷设置 Cookie 可以这样设置
		app('cookie')->setResponseCookie($response, 'token1111', $this->tokenString , 3600); //兼容fpm和所有workerman启动器
		app('cookie')->setResponseCookie($response, 'token123', $this->tokenString , 3600); //兼容fpm和所有workerman启动器

		// 在发送 Response 前统一绑定队列中的 Cookie
		app('cookie')->sendQueuedCookies($response);
		
		#dump($response);

		// 快捷删除 Cookie
		//app('cookie')->forgetResponseCookie($response, 'old_cookie');

		// 如果续期了，可以获取新 token：
		//$newToken = $request->attributes->get('_new_token');
		//if ($newToken) {
			// 在日志或前端提示中使用
		//	$logger->info("Token refreshed for user {$user['uid']}");
		//}


		//解析结果
		# $string = app('jwt')->getPayload($this->tokenString);


		
		return $response;
		
		
	}
	
	public function refresh()
	{

		$token = app('session')->get('jwttoken');

		// 刷新
		//$newToken = $jwt->refresh($token);
		
		//解析token
		$string = app('jwt')->getPayload($token);

		
		return new Response('token:' . $token);
	}
	
	#[Route(path: '/get', methods: ['GET'], name: 'demo1.index' )]
	public function getdatas()
	{
		return new Response('hello router');
	}
	
	//banner uid=42的token
	public function banner()
	{
		app('jwt')->revokeAllForUser(42);
		return new Response('kick off');
	}
	
	
	

	//获取cookie，cookie字符长度单项为超过4k
	public function getcookie( Request $request):Response
	{
		//$this->cookie->make('token' , 'okkkkkk');
		//$token = $request->cookies->get('token');
		
		
		$token = app('cookie')->get($request , 'token');
		//return new Response('token:'.$token );
		$user = app('jwt')->getPayload($token);
		
		//dump($user);
		
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
	public function logout(Request $request): Response
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
