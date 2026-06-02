<?php

declare(strict_types=1);

/**
 * @Filename: ArticleMessageHandler.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Queue\Handlers;

use Framework\Queue\MessageHandlerInterface;

/**
 * 文章业务消息处理器
 *
 * 在 server.php 队列 Worker 的 onWorkerStart 中注册：
 * ```php
 * $consumer->registerHandler('article_published', new ArticleMessageHandler());
 * $consumer->registerHandler('article_view',      new ArticleMessageHandler());
 * ```
 *
 * 消息入队方式（在 Controller / Service 中）：
 * ```php
 * use Framework\Queue\RedisConsumerService;
 *
 * // 文章发布通知
 * RedisConsumerService::dispatch('default', 'article_published', [
 *     'article_id'    => 1,
 *     'article_title' => '标题',
 *     'author_id'     => 100,
 *     'published_at'  => '2026-06-02 23:00:00',
 * ]);
 *
 * // 浏览量统计
 * RedisConsumerService::dispatch('default', 'article_view', [
 *     'article_id' => 1,
 *     'viewed_at'  => '2026-06-02 23:00:00',
 * ]);
 * ```
 *
 * 消息结构（由 RedisConsumerService::dispatch 封装）：
 * ```json
 * {
 *   "type":       "article_published",
 *   "payload":    { "article_id": 1, ... },
 *   "_retries":   0,
 *   "_queued_at": 1748872800
 * }
 * ```
 */
class ArticleMessageHandler implements MessageHandlerInterface
{
    /**
     * 处理文章相关消息
     *
     * payload 来自 RedisConsumerService 解包后的消息体（envelope['payload']）。
     * type 字段在 RedisConsumerService 中路由到此 Handler 之前已被消费，
     * 若同一 Handler 需区分多种 type，可在注册时用不同 key 注册不同 Handler，
     * 或在此处通过 $payload['_type'] 自行分发。
     *
     * @param array|string $payload 消息内容（框架已 json_decode）
     * @throws \RuntimeException 业务失败时抛出，框架会推入重试队列
     */
    public function handle(array|string $payload): void
    {
        $data = is_string($payload)
            ? json_decode($payload, true, 512, JSON_THROW_ON_ERROR)
            : $payload;

        $articleId = (int) ($data['article_id'] ?? 0);

        if ($articleId <= 0) {
            // 非法消息直接丢弃（不抛异常，避免死信队列堆积无效数据）
            $this->log('[ArticleMessageHandler] 无效消息（article_id 为空），已丢弃：' . json_encode($data, JSON_UNESCAPED_UNICODE));
            return;
        }

        // 根据消息内容判断处理分支
        if (isset($data['published_at'])) {
            $this->handlePublished($data);
        } elseif (isset($data['viewed_at'])) {
            $this->handleView($data);
        } else {
            $this->log('[ArticleMessageHandler] 未知消息类型，已记录：' . json_encode($data, JSON_UNESCAPED_UNICODE));
        }
    }

    // =========================================================================
    //  私有业务处理方法
    // =========================================================================

    /**
     * 处理文章发布通知
     *
     * 实际场景可替换为：
     * - 发送站内消息
     * - 调用微信模板消息推送
     * - 更新 Elasticsearch 索引
     * - 清除文章列表缓存
     *
     * @param array<string, mixed> $data
     */
    private function handlePublished(array $data): void
    {
        $articleId    = (int)    $data['article_id'];
        $articleTitle = (string) ($data['article_title'] ?? '');
        $authorId     = (int)    ($data['author_id'] ?? 0);
        $publishedAt  = (string) ($data['published_at'] ?? '');

        // TODO: 替换为真实通知逻辑，例如：
        // app(NotificationService::class)->sendPublishNotice($authorId, $articleId, $articleTitle);
        // app(SearchIndexService::class)->upsert('article', $articleId);
        // app(CacheService::class)->invalidateArticleList($articleId);

        $this->log(sprintf(
            '[ArticleMessageHandler] 文章发布通知已处理 | id=%d title=%s author=%d time=%s',
            $articleId,
            $articleTitle,
            $authorId,
            $publishedAt
        ));
    }

    /**
     * 处理文章浏览统计
     *
     * 实际场景可替换为：
     * - 批量归并 Redis 计数到 MySQL
     * - 更新实时统计报表
     *
     * @param array<string, mixed> $data
     * @throws \RuntimeException 模拟业务失败，触发框架重试机制
     */
    private function handleView(array $data): void
    {
        $articleId = (int)    $data['article_id'];
        $viewedAt  = (string) ($data['viewed_at'] ?? '');

        // TODO: 替换为真实逻辑，例如：
        // $viewCount = RedisFactory::incr('article:views:' . $articleId);
        // if ($viewCount % 100 === 0) {
        //     // 每 100 次同步一次到 DB
        //     app(ArticleDao::class)->update($articleId, ['views' => $viewCount]);
        // }

        $this->log(sprintf(
            '[ArticleMessageHandler] 文章浏览统计已记录 | id=%d time=%s',
            $articleId,
            $viewedAt
        ));
    }

    /**
     * 写日志
     */
    private function log(string $message): void
    {
        if (function_exists('log_info')) {
            log_info($message);
        } else {
            error_log($message);
        }
    }
}
