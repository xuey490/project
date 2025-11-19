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
use Symfony\Component\HttpFoundation\Response;

#[Route(
    prefix: '/api/v1',
    group: 'api',
    middleware: [AuthMiddleware::class, LogMiddleware::class]
)]
class User
{
    // GET /api/v1/users
    #[Route(
        path: '/users',
        methods: ['GET'],
        name: 'user.list',
        middleware: [AuthMiddleware::class]
    )]
    public function list(Request $request): Response
    {
        return new Response('User list');
    }

    // POST /api/v1/users
    #[Route(
        path: '/users',
        methods: ['POST'],
        name: 'user.create',
        middleware: [LogMiddleware::class]
    )]
    public function create(Request $request): Response
    {
        return new Response('User created');
    }

    // GET /api/v1/users/{id}，要求 id 是数字
    #[Route(
        path: '/users/{id}',
        methods: ['GET'],
        name: 'user.show',
        requirements: ['id' => '\d+']
    )]
    public function show(Request $request, int $id): Response
    {
        return new Response("Show user {$id}");
    }

    // PUT /api/v1/users/{id}，修改用户
    #[Route(
        path: '/users/{id}',
        methods: ['PUT'],
        name: 'user.update',
        requirements: ['id' => '\d+'],
        middleware: [AuthMiddleware::class]
    )]
    public function update(Request $request, int $id): Response
    {
        return new Response("Update user {$id}");
    }

    // DELETE /api/v1/users/{id}
    #[Route(
        path: '/users/{id}',
        methods: ['DELETE'],
        name: 'user.delete',
        requirements: ['id' => '\d+'],
        middleware: [AuthMiddleware::class]
    )]
    public function delete(Request $request, int $id): Response
    {
        return new Response("Delete user {$id}");
    }
}
