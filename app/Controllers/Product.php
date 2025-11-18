<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;
use Framework\Attributes\Routes\Prefix;
use Framework\Attributes\Routes\GetMapping;
use Framework\Attributes\Routes\PostMapping;
use Framework\Attributes\Routes\PutMapping;

#[Prefix('/products', middleware: [\App\Middlewares\AuthMiddleware::class] , auth: true)]
class Product
{
    #[GetMapping('/list' )]
    public function list()
    {
        return 'Product list';
    }

    #[PostMapping('/create', middleware: [\App\Middlewares\AuthMiddleware::class])]
    public function create()
    {
        return 'Product created';
    }

    // 混合 Route 写法 auth:false 覆盖
    #[Route(path: '/{id}', auth: false ,methods: ['GET'], requirements: ['id' => '\d+'], name: 'product_show')]
    public function show(Request $request , int $id)
    {
		//$id= $request->query->get('id', 1);
		$id= $request->attributes->get('id');
        return "Product: $id";
    }

    // 覆盖 auth = false → 开放接口
    #[PutMapping('/{id}', auth: false)]
    public function update($id)
    {
		$id= $request->attributes->get('id');
        return "Product $id updated (no auth required)";
    }
}
