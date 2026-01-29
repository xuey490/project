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

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware implements MiddlewareInterface
{
    protected const ALLOW_HEADERS = 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-Tenant-Id, X-XSRF-TOKEN, X-CSRF-TOKEN, X-TOKEN-REFRESH';
    protected const ALLOW_METHODS = 'GET, POST, PUT, DELETE, PATCH, OPTIONS';
    protected const ALLOW_CREDENTIALS = 'true';
    protected const ALLOW_ORIGINS = ['https://example.com', 'https://sub.example.com'];

    public function handle(Request $request, callable $next): Response
    {
        $response = new Response();

        if (!$request->isMethod('OPTIONS')) {
            try {
                $response = $next($request);
            } catch (\Exception $e) {
                // 返回 500 错误并记录日志
                return new Response('Internal Server Error', 500);
            }
        }

        // 动态设置 Allow-Origin
        $origin = $request->headers->get('Origin');
		
        #if (in_array($origin, self::ALLOW_ORIGINS, true)) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Credentials', self::ALLOW_CREDENTIALS);
        #}

        $response->headers->set('Access-Control-Allow-Headers', self::ALLOW_HEADERS);
        $response->headers->set('Access-Control-Allow-Methods', self::ALLOW_METHODS);

        return $response;
    }
}