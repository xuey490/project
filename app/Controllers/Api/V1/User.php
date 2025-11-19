<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers\Api\V1;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class User
{
    public function index()
    {
        return new Response('User List');
    }

    // 方法签名：直接接收 $id（由 ArgumentResolver 注入）
    public function view(string $id): array
    {
        return [
            'message' => 'User fetched successfully',
            'id'      => $id,
        ];
    }

    // ✅ 混合参数：标量 + Request 对象
    public function show(string $id, Request $request): array
    {
        $action = $request->query->get('action', 'view');
        return [
            'id'     => $id,
            'action' => $action,
            'query'  => $request->query->all(),
        ];
    }

    // ✅ 纯查询参数（搜索）
    public function search(string $keyword, string $category = 'all'): array
    {
        return compact('keyword', 'category');
    }

    // ✅ 路径参数 + 默认值
    public function edit(string $id, string $redirect = 'profile'): array
    {
        return compact('id', 'redirect');
    }
}
