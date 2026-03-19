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
use Framework\Core\Exception\Handler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * 异常处理服务提供者
 *
 * 负责注册框架的异常处理器服务，提供统一的异常捕获和处理机制。
 * 主要功能包括：
 * - 注册 Handler 服务，处理应用程序中的异常和错误
 * - 支持自动依赖注入
 */
final class HandlerServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册异常处理服务到依赖注入容器
     *
     * 注册以下服务：
     * - exception：异常处理器服务别名，便于简洁访问
     * - Handler：异常处理器类，负责捕获和处理异常
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
        // 注册 exception 服务
        $services->set('exception', Handler::class)
            ->autowire()
            ->public();
        $services->set(Handler::class)
            ->autowire()
            ->public();
    }

    /**
     * 启动异常处理服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
