<?php

declare(strict_types=1);

/**
 * @Filename: PoolServiceProvider.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Providers;

use Framework\Container\ServiceProviderInterface;
use Framework\Pool\MysqlPool;
use Framework\Pool\PoolManager;
use Framework\Pool\RedisPool;
use Framework\Queue\RedisConsumerService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * 连接池与队列服务提供者
 *
 * 职责：
 * 1. register() 阶段：将 RedisPool、MysqlPool、RedisConsumerService 注册到 DI 容器
 * 2. boot() 阶段：在非 Workerman 环境（FPM）下为兼容性做直连降级
 *
 * 注意：真正的池预热（createConnection 等）发生在各 Workerman Worker 的
 * onWorkerStart 中（见 server.php），不在此 Provider boot() 中执行。
 * 原因：Symfony 容器在主进程编译，fork 出的子进程才真正拥有独立连接。
 */
class PoolServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(ContainerConfigurator $container): void
    {
        $services = $container->services();

        // 注册 RedisPool（工厂方式，运行时按配置实例化）
        $services
            ->set(RedisPool::class)
            ->public()
            ->autowire(false);

        // 注册 MysqlPool
        $services
            ->set(MysqlPool::class)
            ->public()
            ->autowire(false);

        // 注册 RedisConsumerService
        $services
            ->set(RedisConsumerService::class)
            ->public()
            ->autowire(false);

        // 注册 PoolManager（静态类，注册为服务以便 IDE 提示）
        $services
            ->set(PoolManager::class)
            ->public()
            ->autowire(false);
    }

    /**
     * {@inheritDoc}
     *
     * FPM 模式下此方法什么也不做（连接池仅在 Workerman 常驻模式下有意义）。
     * Workerman 模式下连接池在 server.php 各 Worker 的 onWorkerStart 中初始化。
     */
    public function boot(ContainerInterface $container): void
    {
        // Workerman 常驻模式下，实际池初始化由 server.php Worker 生命周期管理
        // FPM 模式下，无需连接池，Eloquent/Think ORM 直连即可
        if (!defined('WORKERMAN_ENV')) {
            return;
        }

        // 此处仅做日志占位，实际预热在 server.php onWorkerStart 中
        if (function_exists('log_info')) {
            log_info('[PoolServiceProvider] Workerman 模式已加载，连接池将在 Worker 启动时初始化');
        }
    }
}
