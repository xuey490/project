<?php

declare(strict_types=1);

/**
 * 用户管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysUserService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * UserController 用户管理控制器
 *
 * 处理用户的增删改查等操作
 */
class UserController extends BaseController
{
    /**
     * 用户服务
     * @var SysUserService
     */
    protected SysUserService $userService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->userService = new SysUserService();
    }

    /**
     * 获取用户列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/list', methods: ['GET'], name: 'user.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1),
            'limit' => (int)$this->input('limit', 20),
            'username' => $this->input('username', ''),
            'status' => $this->input('status', ''),
            'dept_id' => $this->input('dept_id', ''),
        ];

        $result = $this->userService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取用户详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/detail/{id}', methods: ['GET'], name: 'user.detail')]
    #[Auth(required: true)]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $result = $this->userService->getDetail($id);

        if (!$result) {
            return $this->fail('用户不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建用户
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/create', methods: ['POST'], name: 'user.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function create(Request $request): BaseJsonResponse
    {
        $data = [
            'username' => $this->input('username', ''),
            'password' => $this->input('password', ''),
            'nickname' => $this->input('nickname', ''),
            'email' => $this->input('email', ''),
            'mobile' => $this->input('mobile', ''),
            'avatar' => $this->input('avatar', ''),
            'dept_id' => (int)$this->input('dept_id', 0),
            'status' => (int)$this->input('status', 1),
            'remark' => $this->input('remark', ''),
            'role_ids' => $this->input('role_ids', []),
            'menu_ids' => $this->input('menu_ids', []),
        ];

        // 参数验证
        if (empty($data['username'])) {
            return $this->fail('用户名不能为空');
        }

        if (empty($data['password'])) {
            return $this->fail('密码不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $user = $this->userService->create($data, $operator);
            return $this->success(['id' => $user->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新用户
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/update/{id}', methods: ['PUT'], name: 'user.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        
        $data = [
            'nickname' => $this->input('nickname', ''),
            'email' => $this->input('email', ''),
            'mobile' => $this->input('mobile', ''),
            'avatar' => $this->input('avatar', ''),
            'dept_id' => (int)$this->input('dept_id', 0),
            'status' => $this->input('status') !== '' ? (int)$this->input('status') : null,
            'remark' => $this->input('remark', ''),
            'password' => $this->input('password', ''),
            'role_ids' => $this->input('role_ids'),
            'menu_ids' => $this->input('menu_ids'),
        ];

        // 过滤空值
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->userService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除用户
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/delete/{id}', methods: ['DELETE'], name: 'user.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        
        // 不能删除自己
        $operatorId = $this->getOperatorId($request);
        if ($id === $operatorId) {
            return $this->fail('不能删除自己');
        }

        try {
            $this->userService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新用户状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/status/{id}', methods: ['PUT'], name: 'user.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $status = (int)$this->input('status', 1);

        // 不能禁用自己
        $operatorId = $this->getOperatorId($request);
        if ($id === $operatorId) {
            return $this->fail('不能禁用自己');
        }

        $result = $this->userService->updateStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 重置密码
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/user/reset-password/{id}', methods: ['PUT'], name: 'user.resetPassword')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function resetPassword(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $password = $this->input('password', '123456');

        if (strlen($password) < 6) {
            return $this->fail('密码长度不能少于6位');
        }

        $result = $this->userService->resetPassword($id, $password);

        return $result
            ? $this->success([], '密码重置成功')
            : $this->fail('密码重置失败');
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
