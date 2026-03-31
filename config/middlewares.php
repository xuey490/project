<?php

return [
    // 认证中间件（验证 JWT Token）
    \App\Middlewares\AuthMiddleware::class,

    // 操作日志中间件（记录写操作）
    \App\Middlewares\OperationLogMiddleware::class,

    // 以下中间件按需启用：
    // \App\Middlewares\CasbinRbacMiddleware::class,
    // \App\Middlewares\MenuPermissionMiddleware::class,
];
