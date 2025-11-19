<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Attributes\Routes\Prefix;
use Framework\Attributes\Routes\GetMapping;
use Framework\Attributes\Routes\PostMapping;

#[Prefix('/auths2', auth: true, roles: ['admin'],middleware: [\App\Middlewares\AuthMiddleware::class])]
class Auth2
{
    #[GetMapping('/dashboard')]
    public function dashboard()
    {
        return 'Admin dashboard';
    }

    #[PostMapping('/settings', roles: ['super-admin'], middleware: [\App\Middlewares\LogMiddleware::class])]
    public function settings()
    {
        return 'Admin settings updated';
    }
}
