<?php

declare(strict_types=1);

/**
 * @Filename: DefaultMessageHandler.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Queue\Handlers;

use Framework\Queue\MessageHandlerInterface;

/**
 * 默认消息处理器（示例）
 *
 * 用于演示如何实现 MessageHandlerInterface，实际业务中请创建具体的处理器类。
 *
 * 推送消息示例：
 * ```php
 * use Framework\Queue\RedisConsumerService;
 *
 * RedisConsumerService::dispatch('default', 'log_event', [
 *     'level'   => 'info',
 *     'message' => '用户登录成功',
 *     'user_id' => 123,
 * ]);
 * ```
 */
class DefaultMessageHandler implements MessageHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(array|string $payload): void
    {
        $data = is_string($payload) ? json_decode($payload, true) : $payload;

        // 示例：仅打印日志，实际业务替换为真实逻辑
        $message = sprintf(
            '[DefaultMessageHandler] 收到消息：%s',
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );

        if (function_exists('log_info')) {
            log_info($message);
        } else {
            error_log($message);
        }
    }
}
