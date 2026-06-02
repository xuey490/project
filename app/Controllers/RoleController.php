<?php

declare(strict_types=1);

/**
 * @Filename: RoleController.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Controllers;

use App\Services\SysRoleService;
use Framework\Attributes\Auth;
use Framework\Attributes\Permission;
use Framework\Attributes\Route;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * 角色管理控制器
 *
 * 提供角色的增删改查、状态变更、菜单分配等接口。
 * 所有业务逻辑委托给 SysRoleService，控制器只负责参数解析和响应封装。
 */
class RoleController extends BaseController
{
    /** 系统内置角色 id，禁止编辑 / 删除 */
    protected const int SYSTEM_PROTECTED_ROLE_ID = 1;

    /**
     * 绑定服务类，框架自动实例化并赋值到 $this->service
     *
     * @var string
     */
    protected string $serviceClass = SysRoleService::class;

    /**
     * 明确类型，便于 IDE 提示；由 initialize() 从 $this->service 赋值
     *
     * @var SysRoleService
     */
    protected SysRoleService $roleService;

    /**
     * 构造后钩子，由 BaseController::__construct() 在 service 初始化完成后调用
     */
    protected function initialize(): void
    {
        /** @var SysRoleService $this->service */
        $this->roleService = $this->service;
    }

    // =========================================================================
    //  查询接口
    // =========================================================================

    /**
     * 角色列表（分页 + 筛选）
     *
     * GET /api/system/role/list?page=1&limit=20&name=&code=&status=
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/list', methods: ['GET'], name: 'role.list')]

    public function list(): BaseJsonResponse
    {
        $params = [
            'page'   => (int) $this->input('page', 1),
            'limit'  => (int) $this->input('limit', 20),
            'name'   => $this->input('name') ?: $this->input('role_name', ''),
            'code'   => $this->input('code') ?: $this->input('role_code', ''),
            'status' => $this->input('status', ''),
        ];

        return $this->success($this->roleService->getList($params));
    }

    /**
     * 所有启用角色（供下拉选择）
     *
     * GET /api/system/role/all
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/all', methods: ['GET'], name: 'role.all')]

    public function all(): BaseJsonResponse
    {
        return $this->success($this->roleService->getAllEnabled());
    }

    /**
     * 可访问角色列表（id + name 扁平结构，供用户编辑弹窗使用）
     *
     * GET /api/system/role/access-role
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/access-role', methods: ['GET'], name: 'role.accessRole')]

    public function accessRole(): BaseJsonResponse
    {
        return $this->success($this->roleService->getAccessRoleList());
    }

    /**
     * 角色树（按 sort/id 排序）
     *
     * GET /api/system/role/tree
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/tree', methods: ['GET'], name: 'role.tree')]

    public function tree(): BaseJsonResponse
    {
        return $this->success($this->roleService->getRoleTree());
    }

    /**
     * 角色详情
     *
     * GET /api/system/role/detail/{id}
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/detail/{id}', methods: ['GET'], name: 'role.detail')]

    public function detail(): BaseJsonResponse
    {
        $id = (int) $this->request->attributes->get('id');

        $result = $this->roleService->getDetail($id);
        if ($result === null) {
            return $this->fail('角色不存在');
        }

        return $this->success($result);
    }

    // =========================================================================
    //  写操作接口
    // =========================================================================

    /**
     * 新增角色
     *
     * POST /api/system/role/create
     * Body JSON: { name, code, level, sort, status, remark, data_scope, menu_ids, dept_ids }
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/create', methods: ['POST'], name: 'role.create')]

    public function create(): BaseJsonResponse
    {
        $body = $this->getJsonBody();

        if (empty($body['name'])) {
            return $this->fail('角色名称不能为空');
        }
        if (empty($body['code'])) {
            return $this->fail('角色编码不能为空');
        }

        $data = [
            'name'       => $body['name'],
            'code'       => $body['code'],
            'level'      => (int) ($body['level']      ?? 0),
            'sort'       => (int) ($body['sort']        ?? 0),
            'status'     => $this->normalizeStatus($body['status'] ?? 1),
            'remark'     => $body['remark']             ?? '',
            'data_scope' => (int) ($body['data_scope'] ?? 1),
            'menu_ids'   => $body['menu_ids']           ?? [],
            'dept_ids'   => $body['dept_ids']           ?? [],
        ];

        try {
            $role = $this->roleService->create($data, $this->getOperatorId());
            return $this->success(['id' => $role->id], '创建成功');
        } catch (\InvalidArgumentException $e) {
            return $this->fail($e->getMessage());
        } catch (\Throwable $e) {
            return $this->error('创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新角色
     *
     * PUT /api/system/role/update/{id}
     * Body JSON: 与 create 相同，所有字段可选
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/update/{id}', methods: ['PUT'], name: 'role.update')]

    public function update(?Request $request = null): BaseJsonResponse
    {
        $id = (int) $this->request->attributes->get('id');

        if ($id === self::SYSTEM_PROTECTED_ROLE_ID) {
            return $this->fail('系统内置角色不允许编辑');
        }

        $body = $this->getJsonBody();

        // 只提取非 null 字段，允许前端只传要改的字段
        $data = array_filter([
            'name'       => $body['name']       ?? null,
            'code'       => $body['code']        ?? null,
            'level'      => isset($body['level'])      ? (int)  $body['level']      : null,
            'sort'       => isset($body['sort'])       ? (int)  $body['sort']        : null,
            'status'     => isset($body['status'])     ? $this->normalizeStatus($body['status']) : null,
            'remark'     => $body['remark']      ?? null,
            'data_scope' => isset($body['data_scope']) ? (int)  $body['data_scope'] : null,
            'menu_ids'   => $body['menu_ids']    ?? null,
            'dept_ids'   => $body['dept_ids']    ?? null,
        ], fn($v) => $v !== null);

        try {
            $this->roleService->update($id, $data, $this->getOperatorId());
            return $this->success([], '更新成功');
        } catch (\InvalidArgumentException $e) {
            return $this->fail($e->getMessage());
        } catch (\Throwable $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除角色
     *
     * DELETE /api/system/role/delete/{id}
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/delete/{id}', methods: ['DELETE'], name: 'role.delete')]

    public function delete(): BaseJsonResponse
    {
        $id = (int) $this->request->attributes->get('id');

        if ($id === self::SYSTEM_PROTECTED_ROLE_ID) {
            return $this->fail('系统内置角色不允许删除');
        }

        try {
            $this->roleService->delete($id);
            return $this->success([], '删除成功');
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage());
        } catch (\Throwable $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 更新角色状态
     *
     * PUT /api/system/role/status/{id}
     * Body JSON: { "status": 1 }
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/status/{id}', methods: ['PUT'], name: 'role.status')]

    public function updateStatus(): BaseJsonResponse
    {
        $id = (int) $this->request->attributes->get('id');

        if ($id === self::SYSTEM_PROTECTED_ROLE_ID) {
            return $this->fail('系统内置角色状态不允许修改');
        }

        $body   = $this->getJsonBody();
        $status = $this->normalizeStatus($body['status'] ?? 1);

        $result = $this->roleService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    // =========================================================================
    //  菜单分配接口
    // =========================================================================

    /**
     * 分配菜单给角色
     *
     * PUT /api/system/role/assign-menus/{id}
     * Body JSON: { "menu_ids": [1, 2, 3] }
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/assign-menus/{id}', methods: ['PUT'], name: 'role.assignMenus')]

    public function assignMenus(): BaseJsonResponse
    {
        $id = (int) $this->request->attributes->get('id');

        if ($id === self::SYSTEM_PROTECTED_ROLE_ID) {
            return $this->fail('系统内置角色菜单权限不允许修改');
        }

        $body    = $this->getJsonBody();
        $menuIds = $body['menu_ids'] ?? [];

        try {
            $this->roleService->assignMenus($id, (array) $menuIds, $this->getOperatorId());
            return $this->success([], '菜单分配成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取角色已分配的菜单 id 列表
     *
     * GET /api/system/role/menu-by-role/{id}
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/menu-by-role/{id}', methods: ['GET'], name: 'role.menuByRole')]

    public function menuByRole(): BaseJsonResponse
    {
        $id      = (int) $this->request->attributes->get('id');
        $menuIds = $this->roleService->getMenuIds($id);

        // 返回 [{id: x}, ...] 结构，与前端 data.menus 对齐
        $menus = array_map(fn(int $menuId) => ['id' => $menuId], $menuIds);

        return $this->success(['menus' => $menus]);
    }

    /**
     * 保存角色菜单权限（与 assignMenus 功能相同，兼容前端调用）
     *
     * PUT /api/system/role/menu-permission/{id}
     * Body JSON: { "menu_ids": [1, 2, 3] }
     *
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/role/menu-permission/{id}', methods: ['PUT'], name: 'role.menuPermission')]

    public function menuPermission(): BaseJsonResponse
    {
        $id = (int) $this->request->attributes->get('id');

        if ($id === self::SYSTEM_PROTECTED_ROLE_ID) {
            return $this->fail('系统内置角色菜单权限不允许修改');
        }

        $body    = $this->getJsonBody();
        $menuIds = $body['menu_ids'] ?? [];

        try {
            $this->roleService->assignMenus($id, (array) $menuIds, $this->getOperatorId());
            return $this->success([], '菜单权限保存成功');
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    // =========================================================================
    //  私有辅助方法
    // =========================================================================

    /**
     * 获取当前操作人 id（从请求上下文中读取 JWT/Session 注入的用户信息）
     *
     * @return int
     */
    private function getOperatorId(): int
    {
        $user = $this->request->attributes->get('user');
        return (int) ($user['id'] ?? 0);
    }

    /**
     * 统一角色状态值到数据库语义：1 = 启用，0 = 禁用
     *
     * 兼容历史字典值 2（停用）→ 0。
     *
     * @param mixed $status  原始状态值
     * @param int   $default 默认值
     * @return int
     */
    private function normalizeStatus(mixed $status, int $default = 1): int
    {
        if ($status === null || $status === '') {
            return $default;
        }

        $value = (int) $status;

        return match ($value) {
            2       => 0,
            1, 0    => $value,
            default => $default,
        };
    }
}
