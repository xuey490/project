<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Attributes\Routes\Prefix;
use Framework\Attributes\Routes\GetMapping;
use Framework\Attributes\Routes\PostMapping;

#[Prefix('/auths',  middleware: [\App\Middlewares\AuthMiddleware::class])] //加上auth:true 需要登录验证
class Auth1
{
    #[GetMapping('/list')] //加上auth:true 需要登录验证
    public function list()
    {
        return 'User list';
    }

    #[PostMapping('/create')]
    public function create()
    {
        return 'User created';
    }
}
