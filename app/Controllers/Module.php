<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repository\ModuleRepository;
use App\Repository\UserRepository;
use Framework\Database\DatabaseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class Module
{
    protected ModuleRepository $moduleRepo;
	
    protected UserRepository $userRepo;

    // 可以在构造函数中注入 DatabaseFactory 或 ModuleRepository
    public function __construct(DatabaseFactory $dbFactory)
    {
		//dump(app('orm'));
        $this->moduleRepo = new ModuleRepository($dbFactory);
        $this->userRepo = new UserRepository($dbFactory);
    }

    /**
     * 1. 获取列表 (GET)
     * 路由示例: GET /users?keyword=abc&status=1&page=1
     */
    public function index(Request $request): Response
    {
        // 从 URL 查询字符串获取参数 ($_GET)
        // all() 获取所有参数数组，get() 获取单个
        $params = $request->query->all(); 
        $page   = $request->query->getInt('page', 1);
        $limit  = $request->query->getInt('limit', 10);
		
        // 调用 Repository 业务逻辑
        $paginator = $this->moduleRepo->getList($params, $page, $limit);


        // 处理分页数据的格式差异 (Think vs Laravel)
        $data = [
            'list'  => method_exists($paginator, 'items') ? $paginator->items() : $paginator,
            'total' => method_exists($paginator, 'total') ? $paginator->total() : $paginator->total(),
            'page'  => $page,
            'limit' => $limit,
        ];

		//dump($this->userRepo->checkLog());
		//return new Response('aa');


        return new JsonResponse([
            'code' => Response::HTTP_OK, // 200
            'msg'  => 'success',
            'data' => $data
        ]);
    }

    /**
     * 2. 创建用户 (POST)
     * 路由示例: POST /users
     * 接收 Content-Type: application/json
     */
    public function store(Request $request): Response
    {
        try {
            // 获取 JSON 请求体数据
            // 如果是 application/json，toArray() 会自动解析
            // 如果是 application/x-www-form-urlencoded，请用 $request->request->all()
            $input = $request->toArray(); 
        } catch (\Throwable $e) {
            return new JsonResponse(['code' => 400, 'msg' => 'Invalid JSON body'], 400);
        }

        // 基础验证
        if (empty($input['email']) || empty($input['username'])) {
            return $this->error('Missing required parameters: email or username', Response::HTTP_BAD_REQUEST);
        }

        // 业务验证
        if ($this->moduleRepo->emailExists($input['email'])) {
            return $this->error('Email already taken', Response::HTTP_CONFLICT);
        }

        try {
            // 创建数据
            $user = $this->moduleRepo->create([
                'username' => $input['username'],
                'email'    => $input['email'],
                'balance'  => 0,
                'status'   => 1,
                'group_id' => $input['group_id'] ?? null,
            ]);

            // 兼容返回 ID (数组或对象)
            $id = is_array($user) ? $user['id'] : $user->id;

            return $this->success(['id' => $id], 'User created', Response::HTTP_CREATED); // 201

        } catch (\Throwable $e) {
            // 记录日志 $e->getMessage()
            return $this->error('Server Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 3. 更新用户 (PUT/PATCH)
     * 路由示例: PUT /users/{id}
     */
    public function update(int $id, Request $request): Response
    {
        try {
            $input = $request->toArray();
        } catch (\Throwable $e) {
            return $this->error('Invalid JSON', 400);
        }

        // 检查是否存在
        $user = $this->moduleRepo->findById($id);
        if (!$user) {
            return $this->error('User not found', Response::HTTP_NOT_FOUND);
        }

        // 验证邮箱唯一性 (排除当前ID)
        if (isset($input['email']) && $this->moduleRepo->emailExists($input['email'], $id)) {
            return $this->error('Email exists', 400);
        }

        // 执行更新
        $updated = $this->moduleRepo->update($id, $input);

        if ($updated) {
            return $this->success(null, 'User updated');
        }

        return $this->error('Update failed', 500);
    }

    /**
     * 4. 删除用户 (DELETE)
     * 路由示例: DELETE /users/{id}
     */
    public function delete(int $id): Response
    {
        $deleted = $this->moduleRepo->delete($id);

        if ($deleted) {
            return $this->success(null, 'User deleted');
        }

        return $this->error('User not found or delete failed', Response::HTTP_NOT_FOUND);
    }

    /**
     * 5. 用户充值 (POST)
     * 路由示例: POST /users/{id}/recharge
     */
    public function recharge(int $id, Request $request): Response
    {
        $amount = $request->toArray()['amount'] ?? 0;

        if (!is_numeric($amount) || $amount <= 0) {
            return $this->error('Invalid amount', 400);
        }

        // 检查用户
        if (!$this->moduleRepo->findById($id)) {
            return $this->error('User not found', 404);
        }

        // 调用 Repo 的 increment 逻辑
        $success = $this->moduleRepo->recharge($id, (float) $amount);

        if ($success) {
            return $this->success(null, 'Recharge successful');
        }

        return $this->error('Recharge failed', 500);
    }

    // --- 辅助方法：统一响应格式 ---

    /**
     * 成功响应
     */
    protected function success(mixed $data = null, string $msg = 'success', int $status = 200): JsonResponse
    {
        return new JsonResponse([
            'code' => $status,
            'msg'  => $msg,
            'data' => $data
        ], $status);
    }

    /**
     * 错误响应
     */
    protected function error(string $msg, int $status = 400): JsonResponse
    {
        return new JsonResponse([
            'code' => $status,
            'msg'  => $msg,
            'data' => null
        ], $status); // HTTP 状态码也同步设置
    }
}