<?php

declare(strict_types=1);

/**
 * 文章控制器（数据权限示例）
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Controllers;

use App\Services\ArticleService;
use App\Models\Article;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Tenant\TenantContext;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * ArticleController 文章控制器
 *
 * 演示数据权限功能的完整实现，包括：
 * - 文章的增删改查
 * - 数据权限控制
 * - 批量操作
 * - 统计功能
 */
class ArticleController extends BaseController
{
    /**
     * 文章服务
     * @var ArticleService
     */
    protected ArticleService $articleService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->articleService = new ArticleService();
    }

    /**
     * 前置操作 - 初始化数据权限
     *
     * @param Request $request
     * @return void
     */
    protected function beforeAction(Request $request): void
    {
        parent::beforeAction($request);

        // 获取当前用户信息
        $userId = $this->getCurrentUserId($request);
        $deptId = $this->getCurrentUserDeptId($request);
        $tenantId = TenantContext::getTenantId();

        // 初始化数据权限上下文
        if ($userId) {
            $this->articleService->initDataScope($userId, $deptId, $tenantId);
        }
    }

    /**
     * 后置操作 - 清理数据权限
     *
     * @param Request $request
     * @param mixed $response
     * @return void
     */
    protected function afterAction(Request $request, $response): void
    {
        // 清理数据权限上下文
        $this->articleService->clearDataScope();

        parent::afterAction($request, $response);
    }

    // ==================== 基础 CRUD ====================

    /**
     * 获取文章列表
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/list', methods: ['GET'], name: 'article.list')]
    #[Auth(required: true)]
    public function index(Request $request): BaseJsonResponse
    {
        // 查询参数
        $params = [
            'keyword' => $this->input('keyword', ''),
            'status' => $this->input('status', ''),
            'category_id' => $this->input('category_id', ''),
            'dept_id' => $this->input('dept_id', ''),
            'created_by' => $this->input('created_by', ''),
            'start_date' => $this->input('start_date', ''),
            'end_date' => $this->input('end_date', ''),
            'order_by' => $this->input('order_by', 'created_at'),
            'order' => $this->input('order', 'desc'),
        ];

        // 分页参数
        $page = (int) $this->input('page', 1);
        $pageSize = (int) $this->input('page_size', 10);

        // 获取列表（自动应用数据权限）
        $result = $this->articleService->getList($params, $page, $pageSize);

        return $this->success($result);
    }

    /**
     * 获取文章详情
     *
     * @param Request $request
     * @param int $id 文章ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/detail/{id}', methods: ['GET'], name: 'article.detail')]
    #[Auth(required: true)]
    public function show(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
		
		$id = intval($this->input('id', ''));

        // 检查权限
        if (!$this->articleService->checkPermission($id, $userId, 'view')) {
            return $this->fail('无权查看该文章', 403);
        }

        $article = $this->articleService->getDetail($id);

        if (!$article) {
            return $this->fail('文章不存在', 404);
        }

        // 增加浏览次数
        $article->incrementViewCount();

        return $this->success($article);
    }

    /**
     * 创建文章
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/create', methods: ['POST'], name: 'article.create')]
    #[Auth(required: true)]
    public function store(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
        $deptId = $this->getCurrentUserDeptId($request);
        $tenantId = TenantContext::getTenantId();

        // 获取请求数据
        $data = [
            'title' => $this->input('title', ''),
            'content' => $this->input('content', ''),
            'summary' => $this->input('summary', ''),
            'cover_image' => $this->input('cover_image', ''),
            'category_id' => $this->input('category_id', 0),
            'status' => $this->input('status', Article::STATUS_DRAFT),
            'sort' => $this->input('sort', 0),
        ];

        try {
            $article = $this->articleService->create($data, $userId, $deptId, $tenantId);
            return $this->success($article, '创建成功', 201);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新文章
     *
     * @param Request $request
     * @param int $id 文章ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/update/{id}', methods: ['PUT'], name: 'article.update')]
    #[Auth(required: true)]
    public function update(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
		
		$id = intval($this->input('id', ''));

        // 检查权限
        if (!$this->articleService->checkPermission($id, $userId, 'edit')) {
            return $this->fail('无权编辑该文章', 403);
        }

        // 获取请求数据
        $data = [
            'title' => $this->input('title', ''),
            'content' => $this->input('content', ''),
            'summary' => $this->input('summary', ''),
            'cover_image' => $this->input('cover_image', ''),
            'category_id' => $this->input('category_id', 0),
            'status' => $this->input('status', ''),
            'sort' => $this->input('sort', ''),
        ];

        // 过滤空值
        $data = array_filter($data, fn($v) => $v !== '');

        try {
            $this->articleService->update($id, $data, $userId);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除文章
     *
     * @param Request $request
     * @param int $id 文章ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/delete/{id}', methods: ['DELETE'], name: 'article.delete')]
    #[Auth(required: true)]
    public function destroy(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);
		
		$id = intval($this->input('id', ''));

        // 检查权限
        if (!$this->articleService->checkPermission($id, $userId, 'delete')) {
            return $this->fail('无权删除该文章', 403);
        }

        try {
            $this->articleService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 状态管理 ====================

    /**
     * 发布文章
     *
     * @param Request $request
     * @param int $id 文章ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/publish/{id}', methods: ['PUT'], name: 'article.publish')]
    #[Auth(required: true)]
    public function publish(Request $request): BaseJsonResponse
    {
        $userId = $this->getCurrentUserId($request);

		$id = intval($this->input('id', ''));
        // 检查权限
        if (!$this->articleService->checkPermission($id, $userId, 'edit')) {
            return $this->fail('无权发布该文章', 403);
        }

        try {
            $this->articleService->publish($id);
            return $this->success([], '发布成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 下架文章
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/offline/{id}', methods: ['PUT'], name: 'article.offline')]
    #[Auth(required: true)]
    public function offline(Request $request): BaseJsonResponse
    {
        $id = (int) $request->attributes->get('id');
        $userId = $this->getCurrentUserId($request);

        // 检查权限
        if (!$this->articleService->checkPermission($id, $userId, 'edit')) {
            return $this->fail('无权下架该文章', 403);
        }

        try {
            $this->articleService->offline($id);
            return $this->success([], '下架成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    // ==================== 批量操作 ====================

    /**
     * 批量发布
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/batch-publish', methods: ['POST'], name: 'article.batchPublish')]
    #[Auth(required: true)]
    public function batchPublish(Request $request): BaseJsonResponse
    {
        $ids = $this->input('ids', []);

        if (empty($ids)) {
            return $this->fail('请选择要发布的文章');
        }

        $count = $this->articleService->batchPublish($ids);
        return $this->success(['count' => $count], "成功发布 {$count} 篇文章");
    }

    /**
     * 批量下架
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/batch-offline', methods: ['POST'], name: 'article.batchOffline')]
    #[Auth(required: true)]
    public function batchOffline(Request $request): BaseJsonResponse
    {
        $ids = $this->input('ids', []);

        if (empty($ids)) {
            return $this->fail('请选择要下架的文章');
        }

        $count = $this->articleService->batchOffline($ids);
        return $this->success(['count' => $count], "成功下架 {$count} 篇文章");
    }

    /**
     * 批量删除
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/batch-delete', methods: ['POST'], name: 'article.batchDelete')]
    #[Auth(required: true)]
    public function batchDelete(Request $request): BaseJsonResponse
    {
        $ids = $this->input('ids', []);

        if (empty($ids)) {
            return $this->fail('请选择要删除的文章');
        }

        $count = $this->articleService->batchDelete($ids);
        return $this->success(['count' => $count], "成功删除 {$count} 篇文章");
    }

    // ==================== 统计功能 ====================

    /**
     * 获取文章统计
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/statistics', methods: ['GET'], name: 'article.statistics')]
    #[Auth(required: true)]
    public function statistics(Request $request): BaseJsonResponse
    {
        $stats = $this->articleService->getDashboardStatistics();
        return $this->success($stats);
    }

    /**
     * 获取数据权限选项
     *
     * @param Request $request
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/article/data-scope-options', methods: ['GET'], name: 'article.dataScopeOptions')]
    #[Auth(required: true)]
    public function dataScopeOptions(Request $request): BaseJsonResponse
    {
        $options = $this->articleService->getDataScopeOptions();
        return $this->success($options);
    }

    // ==================== 辅助方法 ====================

    /**
     * 获取当前用户ID
     *
     * @param Request $request
     * @return int
     */
    protected function getCurrentUserId(Request $request): int
    {
        // 从 Request 属性获取
        $user = $request->attributes->get('user');
        if ($user && isset($user['id'])) {
            return (int) $user['id'];
        }

        // 从 TenantContext 获取
        $userId = TenantContext::getUserId();
        if ($userId) {
            return $userId;
        }

        // 默认返回 0（未登录）
        return 0;
    }

    /**
     * 获取当前用户部门ID
     *
     * @param Request $request
     * @return int
     */
    protected function getCurrentUserDeptId(Request $request): int
    {
        $userId = $this->getCurrentUserId($request);

        if (!$userId) {
            return 0;
        }

        $user = \App\Models\SysUser::find($userId);
        return $user?->dept_id ?? 0;
    }
}
