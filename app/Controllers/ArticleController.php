<?php

declare(strict_types=1);

/**
 * @Filename: ArticleController.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Controllers;

use App\Services\ArticleService;
use Framework\Attributes\Route;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Pool\PoolManager;
use Framework\Queue\RedisConsumerService;
use Predis\Client as PredisClient;
use Throwable;

/**
 * 文章控制器
 *
 * 演示四类能力：
 * 1. 查询所有文章列表（带分页、筛选）
 * 2. 推送消息到 Redis 队列
 * 3. 借用 Redis 连接池直接操作 Redis
 * 4. 服务注册、业务消息处理（见 ArticleMessageHandler）
 */
class ArticleController extends BaseController
{
    /**
     * 绑定服务类，框架自动实例化并赋值到 $this->service
     *
     * @var string
     */
    protected string $serviceClass = ArticleService::class;

    /**
     * 明确类型，便于 IDE 提示；由 initialize() 从 $this->service 赋值
     *
     * @var ArticleService
     */
    protected ArticleService $ArticleService;

    /**
     * 构造后钩子，由 BaseController::__construct() 在 service 初始化完成后调用
     */
    protected function initialize(): void
    {
        $this->ArticleService = $this->service;
    }

    // =========================================================================
    //  1. 查询所有文章列表
    // =========================================================================

    /**
     * 文章列表接口
     *
     * GET /api/article/list
     *
     * 支持查询参数：
     * - page        : 页码（默认1）
     * - limit       : 每页条数（默认10，最大100）
     * - category_id : 按分类筛选
     * - status      : 状态（1正常 0禁用，默认1）
     * - keyword     : 标题关键字模糊搜索
     * - tenant_id   : 租户id
     * - use_cache   : 是否优先读 Redis 缓存（1=是，默认1）
     */
    #[Route(path: '/api/article/list', methods: ['GET'], name: 'article.list')]
    public function list(): BaseJsonResponse
    {
        try {
            $params = [
                'page'        => (int) $this->input('page', 1),
                'limit'       => min((int) $this->input('limit', 10), 100),
                'category_id' => $this->input('category_id'),
                'status'      => $this->input('status', 1),
                'keyword'     => $this->input('keyword'),
                'tenant_id'   => $this->input('tenant_id'),
            ];

            // --- 缓存优先策略（借用 Redis 连接池）---
            $useCache = (bool) $this->input('use_cache', 1);
            if ($useCache) {
                $cacheKey = 'article:list:' . md5(serialize($params));
                $cached   = $this->ArticleService->getFromCache($cacheKey);
                if ($cached !== null) {
                    return $this->success($cached, '来自缓存');
                }
            }

            // --- 查询数据库 ---
            $result = $this->ArticleService->list($params);

            // --- 写入缓存（300秒）---
            if ($useCache) {
                $this->ArticleService->setToCache($cacheKey, $result, 300);
            }

            return $this->success($result);

        } catch (Throwable $e) {
            return $this->error('查询失败：' . $e->getMessage());
        }
    }

    /**
     * 文章详情接口（浏览量原子递增示例）
     *
     * GET /api/article/detail?id=1
     */
    #[Route(path: '/api/article/detail', methods: ['GET'], name: 'article.detail')]
    public function detail(): BaseJsonResponse
    {
        $id = (int) $this->input('id', 0);

        if ($id <= 0) {
            return $this->fail('参数错误：id 不能为空');
        }

        try {
            $article = $this->ArticleService->detail($id);

            if ($article === null) {
                return $this->fail('文章不存在'.$id);
            }

            // 使用 Redis 连接池原子递增浏览量（高并发安全）
            $viewCount = $this->ArticleService->incrViewCount($id);
            $article['redis_views'] = $viewCount; // 附带实时 Redis 计数（可选）

            return $this->success($article);

        } catch (Throwable $e) {
            return $this->error('查询失败：' . $e->getMessage());
        }
    }

    // =========================================================================
    //  2. 推送消息到 Redis 队列（在 Controller 层调用示例）
    // =========================================================================

    /**
     * 新增文章并推送队列消息
     *
     * POST /api/article/create
     * Body (JSON): { "title": "标题", "category_id": 1, "describe": "简介", "content": "正文" }
     */
    #[Route(path: '/api/article/create', methods: ['POST'], name: 'article.create')]
    public function create(): BaseJsonResponse
    {
        $data = $this->inputAll();

        // 简单必填校验
        if (empty($data['title'])) {
            return $this->fail('文章标题不能为空');
        }
        if (empty($data['category_id'])) {
            return $this->fail('分类不能为空');
        }
        if (empty($data['content'])) {
            return $this->fail('文章内容不能为空');
        }

        try {
            // 1. 写入数据库（带事务）
            $newId = $this->ArticleService->create($data);

            // 2. 推送队列消息（异步通知，不阻塞响应）
            //    队列 Worker 中的 ArticleMessageHandler 会处理通知逻辑
            $this->ArticleService->dispatchPublishNotice(
                (int) $newId,
                $data['title'],
                (int) ($data['created_by'] ?? 0)
            );

            // 3. 更新 Redis 热度指标
            $this->ArticleService->saveMetrics((int) $newId, [
                'views'  => 0,
                'likes'  => 0,
                'shares' => 0,
            ]);

            return $this->success(['id' => $newId], '发布成功，通知已异步发送');

        } catch (Throwable $e) {
            return $this->error('发布失败：' . $e->getMessage());
        }
    }

    // =========================================================================
    //  3. 直接借用 Redis 连接池操作 Redis（在 Controller 层演示）
    // =========================================================================

    /**
     * Redis 连接池操作演示接口
     *
     * GET /api/article/redis-demo
     *
     * 演示：借出连接 → 执行命令 → 归还连接
     * 实际业务中此类操作应封装在 Service 层，此处仅做演示。
     */
    #[Route(path: '/api/article/redis-demo', methods: ['GET'], name: 'article.redis_demo')]
    public function redisDemo(): BaseJsonResponse
    {
        /** @var PredisClient|null $redis */
        $redis = null;

        try {
            // ① 从连接池借出 Redis 连接（不新建 TCP，O(1) 栈操作）
            $redis = PoolManager::borrow('redis.default');

            // ② 执行各类 Redis 命令
            $demoKey = 'demo:article:' . time();

            // STRING 操作
            $redis->set($demoKey, 'hello fssphp', 'EX', 60);
            $stringVal = $redis->get($demoKey);

            // INCR 原子计数
            $redis->set('demo:counter', 0);
            $redis->incr('demo:counter');
            $redis->incr('demo:counter');
            $counter = (int) $redis->get('demo:counter');

            // HASH 操作
            $hashKey = 'demo:hash:article';
            $redis->hset($hashKey, 'title', '测试文章');
            $redis->hset($hashKey, 'views', '0');
            $redis->expire($hashKey, 60);
            $hashData = $redis->hgetall($hashKey);

            // LIST 操作
            $listKey = 'demo:list:ids';
            $redis->rpush($listKey, [1, 2, 3, 4, 5]);
            $redis->expire($listKey, 60);
            $listData = $redis->lrange($listKey, 0, -1);

            // 清理演示数据
            $redis->del([$demoKey, 'demo:counter', $hashKey, $listKey]);

            $result = [
                'string_get'  => $stringVal,
                'incr_result' => $counter,
                'hash_data'   => $hashData,
                'list_data'   => $listData,
                'pool_stats'  => PoolManager::stats(),
            ];

            return $this->success($result, 'Redis 连接池操作演示成功');

        } catch (Throwable $e) {
            return $this->error('Redis 操作失败：' . $e->getMessage());
        } finally {
            // ③ 归还连接（finally 确保任何情况下都归还，避免连接泄漏）
            if ($redis !== null) {
                PoolManager::release('redis.default', $redis);
            }
        }
    }

    /**
     * 直接向队列推送任意消息（调试/测试用）
     *
     * POST /api/article/dispatch
     * Body: { "type": "article_published", "payload": { ... } }
     */
    #[Route(path: '/api/article/dispatch', methods: ['POST'], name: 'article.dispatch')]
    public function dispatch(): BaseJsonResponse
    {
        $type    = $this->input('type', 'article_published');
        $payload = $this->inputAll()['payload'] ?? [];

        if (empty($payload)) {
            $payload = ['test' => true, 'time' => date('Y-m-d H:i:s')];
        }

        try {
            // 推送消息到 'default' 队列
            RedisConsumerService::dispatch('default', $type, $payload);

            return $this->success([], sprintf('消息已推送到队列，type=%s', $type));

        } catch (Throwable $e) {
            return $this->error('推送失败：' . $e->getMessage());
        }
    }
}
