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
use Framework\Security\CsrfTokenManager;
use Framework\View\AppTwigExtension;
use Framework\View\MarkdownExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * Twig 模板服务提供者
 *
 * 负责注册和管理框架的 Twig 模板引擎服务。
 * 主要功能包括：
 * - 注册 FilesystemLoader 服务，加载模板文件
 * - 注册 AppTwigExtension 扩展，提供自定义 Twig 函数
 * - 注册 Markdown 服务，支持 Markdown 渲染
 * - 注册 MarkdownExtension 扩展，在 Twig 中使用 Markdown
 * - 注册 Twig Environment 服务，提供模板渲染功能
 */
final class TwigServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册 Twig 模板服务到依赖注入容器
     *
     * 注册以下服务：
     * - FilesystemLoader：Twig 文件系统加载器
     * - AppTwigExtension：自定义 Twig 扩展（含 CSRF 令牌生成功能）
     * - CommonMarkCoreExtension：Markdown 核心扩展
     * - Environment（CommonMark）：Markdown 解析环境
     * - MarkdownConverter：Markdown 转换器
     * - MarkdownExtension：Markdown Twig 扩展
     * - Environment（Twig）：Twig 模板引擎环境
     * - view：Twig 服务别名
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // TWIG配置加载
        $TempConfig = require BASE_PATH . '/config/view.php';
        $viewConfig = $TempConfig['Twig'];
        $services->set(FilesystemLoader::class)->args([$viewConfig['paths']])->public();

        // 注册 AppTwigExtension 扩展
        $services->set(AppTwigExtension::class)
            ->args([
                service(CsrfTokenManager::class),
                '_token', // 👈 显式传入字段名
            ])
            ->public();

        // 注册 markdown 服务开始
        $services->set(CommonMarkCoreExtension::class)
            ->public();

        // 注册 markdown Environment
        $services->set(\League\CommonMark\Environment\Environment::class)
            ->args([
                [
                    // 这是传递给 Environment 构造函数的配置数组
                    'html_input'         => 'strip',
                    'allow_unsafe_links' => false,
                ],
            ])->call('addExtension', [service(CommonMarkCoreExtension::class)])
            ->public();    // Environment 对象需要加载核心扩展才能工作

        // 注册 MarkdownConverter 服务
        // 它依赖于上面 Environment 服务。
        $services->set(MarkdownConverter::class)
            ->args([
                service(\League\CommonMark\Environment\Environment::class),
            ])
            ->public();

        // 注册自定义 Markdown Twig 扩展
        // 它依赖于上面 MarkdownConverter 服务
        $services->set(MarkdownExtension::class)
            ->args([
                service(MarkdownConverter::class), // 注入 MarkdownConverter
            ])
            ->public();
        // Markdown Twig 扩展结束

        $services->set(Environment::class) // ✅ 显式指定类
            ->args([
                service(FilesystemLoader::class),
                [
                    'cache'            => $viewConfig['cache_path'], // ✅ 字符串 或 false
                    'debug'            => $viewConfig['debug'],
                    'auto_reload'      => $viewConfig['debug'],
                    'strict_variables' => $viewConfig['strict_variables'],
                ],
            ])
            ->call('addExtension', [service(AppTwigExtension::class)])
            ->call('addExtension', [service(MarkdownExtension::class)]) // ✅ 添加新的 Markdown 扩展
            ->public();

        // 别名
        $services->alias('view', Environment::class)->public();
    }

    /**
     * 启动 Twig 模板服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
