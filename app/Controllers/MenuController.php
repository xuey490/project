<?php

declare(strict_types=1);

/**
 * 菜单管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysMenuService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * MenuController 菜单管理控制器
 *
 * 处理菜单的增删改查等操作
 */
class MenuController extends BaseController
{
    /**
     * 菜单服务
     * @var SysMenuService
     */
    protected SysMenuService $menuService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->menuService = new SysMenuService();
    }

    /**
     * 获取菜单列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/list', methods: ['GET'], name: 'menu.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'menu_name' => $this->input('menu_name', ''),
            'menu_type' => $this->input('menu_type', ''),
            'status' => $this->input('status', ''),
        ];

        $result = $this->menuService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取菜单树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/tree', methods: ['GET'], name: 'menu.tree')]
    #[Auth(required: true)]
    public function tree(Request $request): BaseJsonResponse
    {
        $result = $this->menuService->getMenuTree();

        return $this->success($result);
    }

    /**
     * 获取用户菜单树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/user-tree', methods: ['GET'], name: 'menu.userTree')]
    #[Auth(required: true)]
    public function userTree(Request $request): BaseJsonResponse
    {
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->fail('未登录', 401);
        }

        $result = $this->menuService->getUserMenuTree($user['id']);

        return $this->success($result);
    }

    /**
     * 获取用户权限列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/user-permissions', methods: ['GET'], name: 'menu.userPermissions')]
    #[Auth(required: true)]
    public function userPermissions(Request $request): BaseJsonResponse
    {
        $user = $request->attributes->get('user');

        if (!$user || !isset($user['id'])) {
            return $this->fail('未登录', 401);
        }

        $result = $this->menuService->getUserPermissions($user['id']);

        return $this->success($result);
    }

    /**
     * 获取目录和菜单树 (用于分配权限)
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/permission-tree', methods: ['GET'], name: 'menu.permissionTree')]
    #[Auth(required: true)]
    public function permissionTree(Request $request): BaseJsonResponse
    {
        $result = $this->menuService->getDirectoryAndMenuTree();

        return $this->success($result);
    }

    /**
     * 获取菜单详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/detail/{id}', methods: ['GET'], name: 'menu.detail')]
    #[Auth(required: true)]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $result = $this->menuService->getDetail($id);

        if (!$result) {
            return $this->fail('菜单不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/create', methods: ['POST'], name: 'menu.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function create(Request $request): BaseJsonResponse
    {
        $data = [
            'parent_id' => (int)$this->input('parent_id', 0),
            'menu_name' => $this->input('menu_name', ''),
            'menu_type' => (int)$this->input('menu_type', 1),
            'path' => $this->input('path', ''),
            'component' => $this->input('component', ''),
            'permission' => $this->input('permission', ''),
            'icon' => $this->input('icon', ''),
            'sort' => (int)$this->input('sort', 0),
            'visible' => (int)$this->input('visible', 1),
            'status' => (int)$this->input('status', 1),
            'is_frame' => (int)$this->input('is_frame', 0),
            'is_cache' => (int)$this->input('is_cache', 0),
            'remark' => $this->input('remark', ''),
        ];

        // 参数验证
        if (empty($data['menu_name'])) {
            return $this->fail('菜单名称不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $menu = $this->menuService->create($data, $operator);
            return $this->success(['id' => $menu->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/update/{id}', methods: ['PUT'], name: 'menu.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        
        $data = [
            'parent_id' => $this->input('parent_id') !== '' ? (int)$this->input('parent_id') : null,
            'menu_name' => $this->input('menu_name', ''),
            'menu_type' => $this->input('menu_type') !== '' ? (int)$this->input('menu_type') : null,
            'path' => $this->input('path', ''),
            'component' => $this->input('component', ''),
            'permission' => $this->input('permission', ''),
            'icon' => $this->input('icon', ''),
            'sort' => $this->input('sort') !== '' ? (int)$this->input('sort') : null,
            'visible' => $this->input('visible') !== '' ? (int)$this->input('visible') : null,
            'status' => $this->input('status') !== '' ? (int)$this->input('status') : null,
            'is_frame' => $this->input('is_frame') !== '' ? (int)$this->input('is_frame') : null,
            'is_cache' => $this->input('is_cache') !== '' ? (int)$this->input('is_cache') : null,
            'remark' => $this->input('remark', ''),
        ];

        // 过滤空值
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->menuService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除菜单
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/delete/{id}', methods: ['DELETE'], name: 'menu.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        try {
            $this->menuService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新菜单状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/menu/status/{id}', methods: ['PUT'], name: 'menu.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $status = (int)$this->input('status', 1);

        $result = $this->menuService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取操作人ID
     *
     * @param Request $request 请求对象
     * @return int
     */
    protected function getOperatorId(Request $request): int
    {
        $user = $request->attributes->get('user');
        return $user['id'] ?? 0;
    }
}
