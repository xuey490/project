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

use Framework\Container\ServiceProviderInterface;
use Framework\Utils\LoggerCache;
use Framework\Log\LoggerService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * 日志服务提供者
 *
 * 负责注册和管理框架的日志服务，提供统一的日志记录功能。
 * 主要功能包括：
 * - 注册 LoggerService 服务，支持多种日志处理器（文件、数据库等）
 * - 注册 LoggerCache 服务，用于日志缓存管理
 * - 根据 log.php 配置文件初始化日志系统
 */
final class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册日志服务到依赖注入容器
     *
     * 注册以下服务：
     * - LoggerService：日志服务类，提供日志记录功能
     * - log：日志服务别名，便于简洁访问
     * - log_cache：日志缓存服务，用于优化日志性能
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        // 注册 log 服务
		
		$logConfig = require BASE_PATH . '/config/log.php';
		
        $services->set(LoggerService::class)
            ->args([$logConfig])
            ->public();

        $services->set('log', LoggerService::class)
            ->args([$logConfig])
            ->public();

        $services->set('log_cache', LoggerCache::class)
            ->args([$logConfig])
            ->public();
    }

    /**
     * 启动日志服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
