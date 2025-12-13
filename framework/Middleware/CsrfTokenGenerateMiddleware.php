<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use Framework\Security\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
前后端 CSRF 工作流变成如下：
请求前

后端写入 cookie：XSRF-TOKEN

axios 自动后端生成csrf的Url或者 读取 cookie 并设置 header X-XSRF-TOKEN

请求中

后端比对：

header: X-XSRF-TOKEN

cookie: XSRF-TOKEN

session: csrf_token

token 过期

后端返回 419（你自己约定也可以 403/400）
axios → 自动请求 /csrf-token 获取新 token
axios → 自动重试原请求一次
*/


class CsrfTokenGenerateMiddleware
{
    public function __construct(
        private CsrfTokenManager $tokenManager,
        private string $cookieName = 'XSRF-TOKEN',
        private string $tokenId = 'default'
    ) {}

	public function handle(Request $request, callable $next): Response
	{
		
		$path = $request->getPathInfo();
		if ($path === '/login/getCsrfToken') {
			return $next($request); // ❗ 不生成csrf TOKEN
		}
		
		// 1. 只获取已有 token（不存在才生成）
		$token = $this->tokenManager->getToken($this->tokenId);

		/** @var Response $response */
		$response = $next($request);

		// 2. 仅在 Cookie 不存在 或 不一致时才写
		$cookieToken = $request->cookies->get($this->cookieName);

		if ($cookieToken !== $token) {
			$response->headers->setCookie(
				new Cookie(
					$this->cookieName,
					$token,
					0,
					'/',
					null,
					false,
					false,
					false,
					Cookie::SAMESITE_LAX
				)
			);
		}

		return $response;
	}

}