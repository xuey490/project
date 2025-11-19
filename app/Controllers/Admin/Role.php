<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers\Admin;

use Framework\Annotations\Get;
use Framework\Annotations\Post;
use Framework\Annotations\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理员用户控制器（路径前缀：/admin/role）.
 * @Route(path="/admin/role", name="admin.role_prefix")
 */
class Role
{
    /**
     * 用户列表（匹配 GET /admin/role）
     * URL:http://localhost:8000/admin/role/getrole.
     * @Get(path="/getrole", name="admin.role.index")
     */
    public function index()
    {
        return new Response('Admin role List');
    }

    /**
     * @Get(
     *     path="/edits/{id}",
     *     name="user.edit",
     *     requirements={"id": "\d+"},
     *     options={}
     * )
     */
    public function edit(int $id)
    {
        return new Response("Edit Admin role: ID = {$id}");
    }

    /**
     * 保存用户（匹配 POST /admin/role/save）.
     * @Post(path="/save", name="admin.role.save")
     */
    public function save(Request $request)
    {
        $rolename = $request->request->get('rolename');
        return new Response("Save Admin role: {$rolename}");
    }
}
