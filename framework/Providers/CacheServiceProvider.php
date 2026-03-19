<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Providers;

use Framework\Cache\CacheFactory;
use Framework\Cache\ThinkAdapter;
use Framework\Cache\ThinkCache;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * 缓存服务提供者
 *
 * 负责注册和管理框架的缓存服务，支持 ThinkPHP 缓存和 Symfony Cache 两种实现。
 * 主要功能包括：
 * - 注册 ThinkCache 服务，用于 ThinkPHP 风格的缓存操作
 * - 注册 ThinkAdapter 适配器，提供统一的缓存接口
 * - 注册 Symfony Cache 的 TagAwareAdapter，支持标签缓存功能
 */
final class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册缓存服务到依赖注入容器
     *
     * 根据配置文件加载缓存配置，并注册以下服务：
     * - ThinkCache：ThinkPHP 缓存核心类
     * - ThinkAdapter：缓存适配器，通过工厂方法创建
     * - cache：缓存服务别名，便于简洁访问
     * - CacheFactory：Symfony 缓存工厂类
     * - sf_cache：Symfony 标签感知缓存适配器
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 定义缓存管理器服务（单例）
        $cacheConfig = require BASE_PATH . '/config/cache.php';

        // 1 注册 ThinkCache 并注入配置
        $services->set(ThinkCache::class)
            // ->arg('$config', require __DIR__ . '/cache.php')
            ->args([$cacheConfig])
            ->public();

        // 2️ 注册 ThinkAdapter（即最终 Cache 服务）
        $services->set(ThinkAdapter::class)
            // 直接调用 ThinkCache::create()
            ->factory([service(ThinkCache::class), 'create'])
            ->public();

        // 3️ 可选：别名方式简化访问
        $services->set('cache', ThinkAdapter::class)
            ->factory([service(ThinkCache::class), 'create'])
            ->public();

        // symfony/cache 注册服务
        $services->set(CacheFactory::class)
            ->args([$cacheConfig])->public();

        // 只注册 TagAwareAdapter 对外使用
        $services->set('sf_cache', TagAwareAdapter::class)
            ->factory([service(CacheFactory::class), 'create'])->public();
    }

    /**
     * 启动缓存服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
