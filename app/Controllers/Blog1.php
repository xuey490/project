<?php

declare(strict_types=1);

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Framework\Attributes\Route;
use Framework\Attributes\Routes\Prefix;
use Framework\Attributes\Routes\GetMapping;

#[Prefix('/blog11')]
##[Route(prefix: '/blog11', group: 'blog11',name:'blog11')]
class Blog1
{
    // 使用你原生的 Route
    #[Route(path: '/detail/{id}', methods: ['GET'], name: 'blog.detail')]
    public function detail(Request $request , int $id)
    {
		$id= $request->attributes->get('id');
        return "Blog detail: $id";
    }

    // 混合写法：SpringBoot 风格
    #[GetMapping('/latest')]
    public function latest()
    {
        return 'Latest blog';
    }
}
