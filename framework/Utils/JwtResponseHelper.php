<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

// 示例：应用层的 JwtResponseHelper (如果需要) 或直接在 Controller/Middleware 中处理

namespace Framework\Utils;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtResponseHelper
{
    /**
     * 根据请求类型设置 Token 响应 (Header 或 Cookie).
     *
     * @param string             $tokenStr  JWT Token 字符串
     * @param \DateTimeImmutable $expiresAt Token 的过期时间
     * @param int                $ttl       Token 的生命周期（秒）
     * @param null|Request       $request   当前的请求实例
     * @param null|Response      $response  当前的响应实例
     */
    public static function setTokenResponse(
        string $tokenStr,
        \DateTimeImmutable $expiresAt,
        int $ttl,
        ?Request $request,
        ?Response $response
    ): void {
        if (! $response) {
            // 没有响应实例，无法设置
            return;
        }

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
            $response->headers->set('Authorization', 'Bearer ' . $tokenStr);
        } else {
            // === Web 场景：写入 Cookie ===

            // 确保你的框架配置函数 (config()) 是可用的
            $cookieName     =  'token';
            $cookieDomain   = config('cookie.domain')    ?? '';
            $cookieSecure   = config('cookie.secure')    ?? true;
            $cookieHttpOnly = config('cookie.httponly')  ?? true;
            $cookiePath     = config('cookie.path')      ?? '/';
            $samesite       = config('cookie.samesite')  ?? 'lax';

            $cookie = Cookie::create(
                $cookieName,
                $tokenStr,	// token值
                $expiresAt, // Expires At (DateTimeImmutable 或 Unix 时间戳)
                $cookiePath,
                $cookieDomain,
                $cookieSecure,
                $cookieHttpOnly,
                false, // raw
                $samesite // SameSite
            );
            $response->headers->setCookie($cookie);

            // 如果你的框架使用一个独立的 Cookie 队列服务（如 Laravel/FssPhp 中的 app('cookie')）
            // 这一步也应在这里处理，因为它属于 HTTP 响应机制的一部分。
            if (function_exists('app') && app('cookie')) {
                // 假设 'app('cookie')->queueCookie' 存在
                // app('cookie')->queueCookie('token', $tokenStr, $ttl);  //加密的cookie
            }
        }
    }

    /**
     * 清除 Token Cookie (用于注销).
     */
    public static function forgetTokenCookie(?Response $response): void
    {
        if (! $response) {
            return;
        }

        $cookieName     =  'token';
        $cookieDomain   = config('cookie.domain')    ?? '';
        $cookieHttpOnly = config('cookie.httponly')  ?? true;
        $cookiePath     = config('cookie.path')      ?? '/';

        // 创建一个已过期且值为 null 的 Cookie 来强制浏览器删除
        $expiredCookie = Cookie::create(
            $cookieName,
            null, // 值设为 null 或空字符串
            (new \DateTimeImmutable())->modify('-1 hour'), // 设置为过去的时间
            $cookiePath,
            $cookieDomain,
            false, // Secure: 可以设置为 false 保证删除
            $cookieHttpOnly
        );

        $response->headers->setCookie($expiredCookie);

        // 如果存在队列服务，也应清除队列中的 Token
        if (function_exists('app') && app('cookie')) {
            // app('cookie')->forget('token');
        }
    }
}
