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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * 请求服务提供者
 *
 * 负责注册和管理框架的 HTTP 请求服务，提供全局请求对象访问。
 * 主要功能包括：
 * - 注册 RequestStack 服务，用于在请求周期中管理请求对象
 * - 注册 Request 服务，封装 HTTP 请求信息
 * - 使用工厂方法从全局变量创建请求实例
 */
final class RequestProvider implements ServiceProviderInterface
{
    /**
     * 注册请求服务到依赖注入容器
     *
     * 注册以下服务：
     * - RequestStack：请求栈服务，用于在应用程序中获取当前请求
     * - Request：HTTP 请求类，通过 createFromGlobals 工厂方法创建
     * - request：请求服务别名，便于简洁访问
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册 RequestStack（用于在工厂中获取当前请求）
        $services->set(RequestStack::class);


        // 1. 注册 Request 服务（确保全局使用同一个请求实例）
        $services->set(\Symfony\Component\HttpFoundation\Request::class)
            ->factory([\Symfony\Component\HttpFoundation\Request::class, 'createFromGlobals'])->public(); // 通过工厂方法创建请求实例

        // 1. 注册 Request 服务（确保全局使用同一个请求实例）
        $services->set('request', Request::class)
            ->factory([Request::class, 'createFromGlobals'])->public(); // 通过工厂方法创建请求实例
    }

    /**
     * 启动请求服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
