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
namespace App\Middlewares;

use Framework\Attributes\Auth; // 你的 Auth 注解类
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // 1. 获取所有业务注解
        $attributes = $request->attributes->get('_attributes', []);
		//所有注解属性		
		//dump($attributes);		
        
        // 2. 尝试获取 Auth 注解对象
        /** @var Auth|null $authAttr */
        $authAttr = $attributes[Auth::class] ?? null;
        
        // 3. 兼容旧的 _auth 参数 (来自 DocBlock 或 Route 属性)
        $legacyAuth = $request->attributes->get('_auth', false);
		
		//符合验证的角色
		//dump($authAttr->roles);		

        // 4. 判断是否需要认证
        $isAuthRequired = ($authAttr && $authAttr->required) || $legacyAuth === true;

				
        if ($isAuthRequired) {
            // ... 执行认证逻辑 ...
			//dump('aaaa');
            // 如果是 $authAttr，你还可以访问 $authAttr->guard 等参数
        }

        return $next($request);
    }
}