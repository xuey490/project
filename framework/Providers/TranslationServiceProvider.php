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
use Framework\Translation\TranslationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\RequestStack;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * 翻译服务提供者
 *
 * 负责注册和管理框架的多语言翻译服务，支持国际化功能。
 * 主要功能包括：
 * - 注册 TranslationService 服务，提供多语言翻译功能
 * - 支持从请求中获取语言设置
 * - 支持从翻译文件加载语言资源
 */
final class TranslationServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册翻译服务到依赖注入容器
     *
     * 注册以下服务：
     * - translator：翻译服务实例，提供多语言翻译功能
     *
     * 服务参数包括：
     * - RequestStack：请求栈服务，用于获取当前请求的语言设置
     * - 翻译文件目录路径
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 多国语言翻译
        // 注册 Translator 服务（不设 locale，延迟设置）
        $services->set('translator', TranslationService::class)
            ->args([
                service(RequestStack::class), // 或 RequestStack::class
                '%kernel.project_dir%/resource/translations',
            ])
            ->public();
    }

    /**
     * 启动翻译服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
