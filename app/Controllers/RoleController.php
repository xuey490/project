<?php

declare(strict_types=1);

/**
 * 角色管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysRoleService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * RoleController 角色管理控制器
 *
 * 处理角色的增删改查等操作
 */
class RoleController extends BaseController
{
    /**
     * 角色服务
     * @var SysRoleService
     */
    protected SysRoleService $roleService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->roleService = new SysRoleService();
    }

    /**
     * 获取角色列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/list', methods: ['GET'], name: 'role.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1),
            'limit' => (int)$this->input('limit', 20),
            'role_name' => $this->input('role_name', ''),
            'role_code' => $this->input('role_code', ''),
            'status' => $this->input('status', ''),
        ];

        $result = $this->roleService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取所有启用的角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/all', methods: ['GET'], name: 'role.all')]
    #[Auth(required: true)]
    public function all(Request $request): BaseJsonResponse
    {
        $result = $this->roleService->getAllEnabled();

        return $this->success($result);
    }

    /**
     * 获取角色树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/tree', methods: ['GET'], name: 'role.tree')]
    #[Auth(required: true)]
    public function tree(Request $request): BaseJsonResponse
    {
        $result = $this->roleService->getRoleTree();

        return $this->success($result);
    }

    /**
     * 获取角色详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/detail/{id}', methods: ['GET'], name: 'role.detail')]
    #[Auth(required: true)]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $result = $this->roleService->getDetail($id);

        if (!$result) {
            return $this->fail('角色不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/create', methods: ['POST'], name: 'role.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function create(Request $request): BaseJsonResponse
    {
        $data = [
            'role_name' => $this->input('role_name', ''),
            'role_code' => $this->input('role_code', ''),
            'parent_id' => (int)$this->input('parent_id', 0),
            'sort' => (int)$this->input('sort', 0),
            'status' => (int)$this->input('status', 1),
            'remark' => $this->input('remark', ''),
            'menu_ids' => $this->input('menu_ids', []),
        ];

        // 参数验证
        if (empty($data['role_name'])) {
            return $this->fail('角色名称不能为空');
        }

        if (empty($data['role_code'])) {
            return $this->fail('角色编码不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $role = $this->roleService->create($data, $operator);
            return $this->success(['id' => $role->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/update/{id}', methods: ['PUT'], name: 'role.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        
        $data = [
            'role_name' => $this->input('role_name', ''),
            'role_code' => $this->input('role_code', ''),
            'parent_id' => $this->input('parent_id') !== '' ? (int)$this->input('parent_id') : null,
            'sort' => $this->input('sort') !== '' ? (int)$this->input('sort') : null,
            'status' => $this->input('status') !== '' ? (int)$this->input('status') : null,
            'remark' => $this->input('remark', ''),
            'menu_ids' => $this->input('menu_ids'),
        ];

        // 过滤空值
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->roleService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/delete/{id}', methods: ['DELETE'], name: 'role.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        try {
            $this->roleService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新角色状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/status/{id}', methods: ['PUT'], name: 'role.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $status = (int)$this->input('status', 1);

        $result = $this->roleService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 分配菜单给角色
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/assign-menus/{id}', methods: ['PUT'], name: 'role.assignMenus')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function assignMenus(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $menuIds = $this->input('menu_ids', []);
        $operator = $this->getOperatorId($request);

        try {
            $this->roleService->assignMenus($id, $menuIds, $operator);
            return $this->success([], '菜单分配成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
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
