<?php
// config/jwt.php

return [
	'single_device_login' => env('JWT_SINGLE_DEVICE_LOGIN', false), // 默认允许多点登录
	
    'secret' => env('JWT_SECRET', 'your-secret-key-here_dGkiOiJhNTE0YzhhMzZjZGRkZDhkM2FlOGY2NDRhMDdlMTJjYXQiOjE3Nj'), 
	
    'algo' => 'HS256', // 支持 HS256, HS384, HS512, RS256 等
	
    'ttl' => 3600, // 默认 1 小时（秒）
	
    'refresh_ttl' => 86400, // 刷新令牌有效期：24 小时
	
	'audience'	=> 'FssPhp.Inc',
	
    'issuer' => 'FssPhp',
	
    'blacklist_enabled' => true,
	
    'blacklist_grace_period' => 60, // 黑名单宽限期（秒），防止并发请求问题
];