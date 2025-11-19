<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\LogMiddleware;
use Framework\Attributes\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route(prefix: '/lists', middleware: [AuthMiddleware::class, LogMiddleware::class])]
class Lists
{
    #[Route(path: '/', methods: ['GET'])]
    public function index(Request $request)
    {
        echo 'index';
    }

    #[Route(path: '/profile', methods: ['GET'])]

    /*
    @Middleware(class="App\Middlewares\AuthMiddleware")
    */
    public function profile(Request $request)
    {
        echo 'profile';
    }

    #[Route(path: '/get/{id}', methods: ['GET'])]
    public function show(Request $request, string $id)
    {
        echo 'show: ' . $id;
    }
}
