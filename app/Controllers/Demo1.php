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

#[Route(prefix: '/api/v1/demo1', group: 'api', middleware: [AuthMiddleware::class, LogMiddleware::class])]
class Demo1
{

    #[Route(path: '/', methods: ['GET'], name: 'demo1.index')]
    public function index(Request $request): Response
    {
        return new Response(json_encode([
            'message'    => 'User list fetched successfully',
            'method'     => 'GET',
            'controller' => __METHOD__,
        ]), 200, ['Content-Type' => 'application/json']);
    }


    #[Route(path: '/create', methods: ['POST'], name: 'demo1.create', middleware: [LogMiddleware::class])]
    public function create(Request $request): Response
    {
        return new Response(json_encode([
            'message'    => 'New user created',
            'method'     => 'POST',
            'controller' => __METHOD__,
        ]), 201, ['Content-Type' => 'application/json']);
    }

  
    #[Route(path: '/{id}', methods: ['DELETE'], name: 'demo1.delete')]
    public function delete(Request $request, $id): Response
    {
        return new Response(json_encode([
            'message'    => "User #{$id} deleted",
            'method'     => 'DELETE',
            'controller' => __METHOD__,
        ]), 200, ['Content-Type' => 'application/json']);
    }
}
