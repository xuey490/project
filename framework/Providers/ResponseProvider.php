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
use Framework\Utils\ResponseFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Response;

/**
 * 响应服务提供者
 *
 * 负责注册和管理框架的 HTTP 响应服务，提供统一的响应对象创建。
 * 主要功能包括：
 * - 注册 ResponseFactory 工厂类，用于创建响应实例
 * - 注册 Response 服务，封装 HTTP 响应信息
 */
final class ResponseProvider implements ServiceProviderInterface
{
    /**
     * 注册响应服务到依赖注入容器
     *
     * 注册以下服务：
     * - response：响应服务，通过 ResponseFactory 工厂创建
     * - ResponseFactory：响应工厂类，提供响应对象创建功能
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // === 注册 Response 为服务 ===
        // 定义一个工厂服务
        $services->set('response', Response::class)
            ->public()
            ->factory([ResponseFactory::class, 'create']);

        // 定义工厂类
        $services->set(ResponseFactory::class)
            ->public();
        /*
        $services
            ->set('response1' , Response::class)
            ->args(['', Response::HTTP_OK, []])
            ->public();
		
        $services
            ->set('response2' , Response::class)
            ->class(Response::class)
            ->public()
            ->synthetic(false) // 表示容器自己管理
            ->args(['', Response::HTTP_OK, []]);
        */
    }

    /**
     * 启动响应服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
