<?php

declare(strict_types=1);

/**
 * @Filename: ArticleService.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Services;

use App\Dao\ArticleDao;
use Framework\Basic\BaseService;
use Framework\DI\Attribute\Autowire;
use Framework\Pool\PoolManager;
use Framework\Queue\RedisConsumerService;
use Predis\Client as PredisClient;

/**
 * 文章业务服务层
 *
 * 职责：
 * - 封装文章业务逻辑，由 Controller 调用
 * - 数据访问通过 ArticleDao 完成（三层架构）
 * - 事务由 BaseService::transaction() 统一管理
 */
class ArticleService extends BaseService
{
    /** @var ArticleDao 文章 DAO，由框架自动注入 */
    #[Autowire]
    protected ArticleDao $articleDao;

    // =========================================================================
    //  查询
    // =========================================================================

    /**
     * 查询所有文章列表（支持分页）
     *
     * @param array $params 查询条件，支持以下键：
     *   - page        : int    页码（默认1）
     *   - limit       : int    每页条数（默认10）
     *   - category_id : int    按分类筛选
     *   - status      : int    状态（1正常 0禁用）
     *   - keyword     : string 标题模糊搜索
     * @return array{items: array, total: int, page: int, limit: int, pages: int}
     */
    public function list(array $params = []): array
    {
        [$page, $limit] = $this->PageParams($params);

        // 构建 DAO 查询条件（框架 CrudQueryTrait 前缀约定）
        $where = [];

        // 状态过滤（默认只查正常状态）
        $where['EQ_status'] = (int) ($params['status'] ?? 1);

        // 可选：按分类过滤
        if (!empty($params['category_id'])) {
            $where['EQ_category_id'] = (int) $params['category_id'];
        }

        // 可选：标题关键字模糊搜索
        if (!empty($params['keyword'])) {
            $where['LIKE_title'] = $params['keyword'];
        }

        // 可选：租户隔离
        if (!empty($params['tenant_id'])) {
            $where['EQ_tenant_id'] = (int) $params['tenant_id'];
        }

        $total = $this->articleDao->count($where, true);
        $items = $this->articleDao->selectList($where, '*', $page, $limit, 'sort asc,id desc', [], true);

        return $this->buildPaginateResult(
            is_array($items) ? $items : $items->toArray(),
            $total,
            $page,
            $limit
        );
    }

    /**
     * 查询单篇文章详情，并累加浏览次数
     *
     * @param int $id 文章id
     * @return array|null
     */
    public function detail(int $id): ?array
    {
        $article = $this->articleDao->get($id);

        if (empty($article)) {
            return null;
        }

        // Eloquent Model → array
        $articleArr = is_array($article) ? $article : $article->toArray();

        // 浏览次数 +1（忽略失败）
        try {
            $this->articleDao->update($id, ['views' => ($articleArr['views'] ?? 0) + 1]);
        } catch (\Throwable) {
            // 浏览量更新失败不影响主流程
        }

        return $articleArr;
    }

    /**
     * 新增文章（带事务）
     *
     * @param array $data 文章数据
     * @return mixed 新记录主键
     */
    public function create(array $data): mixed
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        $data['status']      = $data['status'] ?? 1;
        $data['sort']        = $data['sort']   ?? 100;
        $data['views']       = 0;

        $result = $this->transaction(function () use ($data) {
            return $this->articleDao->save($data);
        });

        // laravelORM 的 save() 返回 Eloquent Model，取主键；thinkORM 返回 int
        if (is_object($result) && method_exists($result, 'getKey')) {
            return $result->getKey();
        }

        return $result;
    }

    /**
     * 更新文章（带事务）
     *
     * @param int   $id   文章id
     * @param array $data 更新数据
     * @return bool
     */
    public function edit(int $id, array $data): bool
    {
        $data['update_time'] = date('Y-m-d H:i:s');

        return $this->transaction(function () use ($id, $data) {
            return $this->articleDao->update($id, $data);
        });
    }

    /**
     * 删除文章（软删除，带事务）
     *
     * @param int $id 文章id
     * @return bool
     */
    public function remove(int $id): bool
    {
        return $this->transaction(function () use ($id) {
            return $this->articleDao->destroy($id);
        });
    }

    // =========================================================================
    //  推送队列消息示例
    // =========================================================================

    /**
     * 文章发布后推送通知消息到队列
     *
     * 在 Controller 调用完 create() 后调用此方法，
     * 队列 Worker 中的 ArticleMessageHandler 会消费并处理实际通知逻辑。
     *
     * @param int    $articleId   文章id
     * @param string $articleTitle 文章标题
     * @param int    $authorId    作者id
     * @return void
     */
    public function dispatchPublishNotice(int $articleId, string $articleTitle, int $authorId): void
    {
        // 推送到 'default' 队列，消息类型 'article_published'
        // 对应 server.php 队列 Worker 中注册的 ArticleMessageHandler
        RedisConsumerService::dispatch('default', 'article_published', [
            'article_id'    => $articleId,
            'article_title' => $articleTitle,
            'author_id'     => $authorId,
            'published_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 推送文章浏览统计到队列（批量异步写入，避免频繁更新 DB）
     *
     * @param int $articleId 文章id
     * @return void
     */
    public function dispatchViewCount(int $articleId): void
    {
        RedisConsumerService::dispatch('default', 'article_view', [
            'article_id' => $articleId,
            'viewed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // =========================================================================
    //  借用 Redis 连接池操作 Redis 示例
    // =========================================================================

    /**
     * 从 Redis 连接池借用连接，缓存文章列表
     *
     * 典型场景：热门文章缓存，直接从 Redis 读取，减少 DB 查询。
     *
     * @param string $cacheKey Redis 缓存 Key
     * @param int    $ttl      缓存有效期（秒）
     * @return array|null 命中缓存返回数据，否则返回 null
     */
    public function getFromCache(string $cacheKey, int $ttl = 300): ?array
    {
        /** @var PredisClient|null $redis */
        $redis = null;

        try {
            // 1. 从连接池借出连接（O(1)，不新建 TCP 连接）
            $redis = PoolManager::borrow('redis.default');

            // 2. 读取缓存
            $cached = $redis->get($cacheKey);
            if ($cached !== null) {
                return json_decode($cached, true);
            }

            return null;

        } catch (\Throwable $e) {
            // Redis 不可用时降级，记录日志但不抛出异常
            if (function_exists('log_info')) {
                log_info('[ArticleService] Redis 缓存读取失败（降级直查DB）：' . $e->getMessage());
            }
            return null;
        } finally {
            // 3. 归还连接（无论成功/异常都要归还，避免连接泄漏）
            if ($redis !== null) {
                PoolManager::release('redis.default', $redis);
            }
        }
    }

    /**
     * 将文章列表写入 Redis 缓存（连接池示例）
     *
     * @param string $cacheKey 缓存 Key
     * @param array  $data     缓存数据
     * @param int    $ttl      有效期（秒）
     * @return void
     */
    public function setToCache(string $cacheKey, array $data, int $ttl = 300): void
    {
        $redis = null;

        try {
            $redis = PoolManager::borrow('redis.default');

            // SET key value EX ttl（设置值并同时设过期时间，原子操作）
            $redis->set($cacheKey, json_encode($data, JSON_UNESCAPED_UNICODE), 'EX', $ttl);

        } catch (\Throwable $e) {
            if (function_exists('log_info')) {
                log_info('[ArticleService] Redis 缓存写入失败：' . $e->getMessage());
            }
        } finally {
            if ($redis !== null) {
                PoolManager::release('redis.default', $redis);
            }
        }
    }

    /**
     * 使用 Redis 实现文章浏览量原子递增（连接池示例：INCR）
     *
     * 相比直接 UPDATE DB，INCR 是原子操作，高并发安全。
     * 可由定时任务或队列定期将 Redis 计数同步回 DB。
     *
     * @param int $articleId 文章id
     * @return int 当前浏览量
     */
    public function incrViewCount(int $articleId): int
    {
        $redis = null;

        try {
            $redis = PoolManager::borrow('redis.default');

            $key   = 'article:views:' . $articleId;
            $count = (int) $redis->incr($key);

            // 设置初始过期时间（7天），防止 Key 永不过期堆积
            if ($count === 1) {
                $redis->expire($key, 7 * 86400);
            }

            return $count;

        } catch (\Throwable $e) {
            if (function_exists('log_info')) {
                log_info('[ArticleService] Redis INCR 失败：' . $e->getMessage());
            }
            return 0;
        } finally {
            if ($redis !== null) {
                PoolManager::release('redis.default', $redis);
            }
        }
    }

    /**
     * Hash 存储文章热度数据（HSET 示例）
     *
     * @param int $articleId 文章id
     * @param array $metrics ['views' => 100, 'likes' => 50]
     * @return void
     */
    public function saveMetrics(int $articleId, array $metrics): void
    {
        $redis = null;

        try {
            $redis  = PoolManager::borrow('redis.default');
            $hashKey = 'article:metrics:' . $articleId;

            // HSET 批量写入 Hash 字段
            foreach ($metrics as $field => $value) {
                $redis->hset($hashKey, $field, (string) $value);
            }
            $redis->expire($hashKey, 86400); // 1天过期

        } catch (\Throwable $e) {
            if (function_exists('log_info')) {
                log_info('[ArticleService] Redis HSET 失败：' . $e->getMessage());
            }
        } finally {
            if ($redis !== null) {
                PoolManager::release('redis.default', $redis);
            }
        }
    }
}
