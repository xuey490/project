<?php

declare(strict_types=1);

/**
 * 岗位管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Controllers;

use App\Services\SysPostService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * PostController 岗位管理控制器
 *
 * 处理岗位的增删改查等操作
 */
class PostController extends BaseController
{
    /**
     * 岗位服务
     * @var SysPostService
     */
    protected SysPostService $postService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->postService = new SysPostService();
    }

    /**
     * 获取岗位列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/list', methods: ['GET'], name: 'post.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'post_code' => $this->input('post_code', ''),
            'post_name' => $this->input('post_name', ''),
            'enabled' => $this->input('enabled', ''),
        ];

        $result = $this->postService->getList($params);

        return $this->success($result);
    }

    /**
     * 获取岗位详情
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/detail/{id}', methods: ['GET'], name: 'post.detail')]
    #[Auth(required: true)]
    public function detail(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $result = $this->postService->getDetail($id);

        if (!$result) {
            return $this->fail('岗位不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/create', methods: ['POST'], name: 'post.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function create(Request $request): BaseJsonResponse
    {
        $data = [
            'post_code' => $this->input('post_code', ''),
            'post_name' => $this->input('post_name', ''),
            'post_sort' => (int)$this->input('post_sort', 0),
            'enabled' => (int)$this->input('enabled', 1),
            'remark' => $this->input('remark', ''),
        ];

        // 参数验证
        if (empty($data['post_name'])) {
            return $this->fail('岗位名称不能为空');
        }

        if (empty($data['post_code'])) {
            return $this->fail('岗位编码不能为空');
        }

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $post = $this->postService->create($data, $operator);
            return $this->success(['id' => $post->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/update/{id}', methods: ['PUT'], name: 'post.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function update(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        
        $data = [
            'post_code' => $this->input('post_code', ''),
            'post_name' => $this->input('post_name', ''),
            'post_sort' => $this->input('post_sort') !== '' ? (int)$this->input('post_sort') : null,
            'enabled' => $this->input('enabled') !== '' ? (int)$this->input('enabled') : null,
            'remark' => $this->input('remark', ''),
        ];

        // 过滤空值
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        // 获取操作人ID
        $operator = $this->getOperatorId($request);

        try {
            $this->postService->update($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/delete/{id}', methods: ['DELETE'], name: 'post.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function delete(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        try {
            $this->postService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新岗位状态
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/status/{id}', methods: ['PUT'], name: 'post.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function updateStatus(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $enabled = (int)$this->input('enabled', 1);

        $result = $this->postService->updateEnabled($id, $enabled);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    /**
     * 获取所有启用的岗位
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/post/enabled', methods: ['GET'], name: 'post.enabled')]
    #[Auth(required: true)]
    public function getEnabled(Request $request): BaseJsonResponse
    {
        $result = $this->postService->getAllEnabled();

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
