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
use Framework\View\ThinkTemplateFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use think\Template;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * ThinkPHP 模板服务提供者
 *
 * 负责注册和管理框架的 ThinkPHP 模板引擎服务。
 * 主要功能包括：
 * - 注册 ThinkTemplateFactory 工厂类，用于创建模板实例
 * - 注册 thinkTemp 服务，提供 ThinkPHP 模板渲染功能
 * - 根据 view.php 配置文件初始化模板引擎
 */
final class ThinkTempServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册 ThinkPHP 模板服务到依赖注入容器
     *
     * 注册以下服务：
     * - ThinkTemplateFactory：模板工厂类，用于创建模板实例
     * - think_template.config：模板配置参数
     * - thinkTemp：模板服务实例，通过工厂方法创建
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // ThinkPHP View配置加载
        $TempConfig       = require \dirname(__DIR__) . '/../config/view.php';
        $tpTemplateConfig = $TempConfig['Think'];

        // 0 注册参数类
        $parameters = $configurator->parameters();

        // 1 注册模板工厂类 ，可以这样注册
        $services->set(ThinkTemplateFactory::class)
            ->args([$tpTemplateConfig])
            ->public();

        // 1. 将 ThinkPHP 模板配置定义为一个容器参数
        // 这是一种更 Symfony 的做法，便于管理
        $parameters->set('think_template.config', $tpTemplateConfig);

        // 2. 注册 'thinkTemp' 服务 ，也可以这样注册
        $services->set('thinkTemp', Template::class)
            // 使用 factory() 方法，并指向我们的工厂类
            // ->factory(service(\Framework\View\ThinkTemplateFactory::class))
            ->factory([service(ThinkTemplateFactory::class), 'create'])
            // 为工厂方法注入配置参数
            ->args([
                // 使用 param() 来引用上面定义的参数
                param('think_template.config'),
            ])
            ->public(); // 允许从容器外部获取
    }

    /**
     * 启动 ThinkPHP 模板服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
