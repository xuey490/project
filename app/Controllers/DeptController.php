<?php

declare(strict_types=1);

/**
 * 部门管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysDeptService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * DeptController 部门管理控制器
 *
 * 处理部门的增删改查等操作
 */
class DeptController extends BaseController
{
    /**
     * 部门服务
     * @var SysDeptService
     */
    protected SysDeptService $deptService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->deptService = new SysDeptService();
    }

    /**
     * 获取部门列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/list', methods: ['GET'], name: 'dept.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'dept_name' => $this->input('dept_name', ''),
            'dept_code' => $this->input('dept_code', ''),
            'status' => $this->input('status', ''),
        ];

        $result = $this->deptService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取部门树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/tree', methods: ['GET'], name: 'dept.tree')]
    #[Auth(required: true)]
    public function tree(Request $request): BaseJsonResponse
    {
        $result = $this->deptService->getDeptTree();

        return $this->success($result);
    }

    /**
     * 获取部门详情
     *
     * @param Request $request 请求对象
     * @param int     $id      部门ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/detail/{id}', methods: ['GET'], name: 'dept.detail')]
    #[Auth(required: true)]
    public function detail(Request $request, int $id): BaseJsonResponse
    {
        $result = $this->deptService->getDetail($id);

        if (!$result) {
            return $this->fail('部门不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建部门
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/create', methods: ['POST'], name: 'dept.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function create(Request $request): BaseJsonResponse
    {
        $data = [
            'parent_id' => (int)$this->input('parent_id', 0),
            'dept_name' => $this->input('dept_name', ''),
            'dept_code' => $this->input('dept_code', ''),
            'leader' => $this->input('leader', ''),
            'phone' => $this->input('phone', ''),
            'email' => $this->input('email', ''),
            'sort' => (int)$this->input('sort', 0),
            'status' => (int)$this->input('status', 1),
            'remark' => $this->input('remark', ''),
        ];

        // 参数验证
        if (empty($data['dept_name'])) {
            return $this->fail('部门名称不能为空');
        }

        if (empty($data['dept_code'])) {
            return $this->fail('部门编码不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $dept = $this->deptService->create($data, $operator);
            return $this->success(['id' => $dept->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新部门
     *
     * @param Request $request 请求对象
     * @param int     $id      部门ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/update/{id}', methods: ['PUT'], name: 'dept.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function update(Request $request, int $id): BaseJsonResponse
    {
        $data = [
            'parent_id' => $this->input('parent_id') !== '' ? (int)$this->input('parent_id') : null,
            'dept_name' => $this->input('dept_name', ''),
            'dept_code' => $this->input('dept_code', ''),
            'leader' => $this->input('leader', ''),
            'phone' => $this->input('phone', ''),
            'email' => $this->input('email', ''),
            'sort' => $this->input('sort') !== '' ? (int)$this->input('sort') : null,
            'status' => $this->input('status') !== '' ? (int)$this->input('status') : null,
            'remark' => $this->input('remark', ''),
        ];

        // 过滤空值
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->deptService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除部门
     *
     * @param Request $request 请求对象
     * @param int     $id      部门ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/delete/{id}', methods: ['DELETE'], name: 'dept.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function delete(Request $request, int $id): BaseJsonResponse
    {
        try {
            $this->deptService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新部门状态
     *
     * @param Request $request 请求对象
     * @param int     $id      部门ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/status/{id}', methods: ['PUT'], name: 'dept.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function updateStatus(Request $request, int $id): BaseJsonResponse
    {
        $status = (int)$this->input('status', 1);

        $result = $this->deptService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取所有子部门ID
     *
     * @param Request $request 请求对象
     * @param int     $id      部门ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dept/children/{id}', methods: ['GET'], name: 'dept.children')]
    #[Auth(required: true)]
    public function getChildren(Request $request, int $id): BaseJsonResponse
    {
        $result = $this->deptService->getAllChildIds($id);

        return $this->success($result);
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
