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
use Framework\Utils\CookieManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Cookie 服务提供者
 *
 * 负责注册和管理框架的 Cookie 服务，提供 Cookie 的读取、写入和管理功能。
 * 主要功能包括：
 * - 注册 CookieManager 服务，提供统一的 Cookie 操作接口
 * - 根据 cookie.php 配置文件初始化 Cookie 管理器
 */
final class CookieServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册 Cookie 服务到依赖注入容器
     *
     * 注册以下服务：
     * - CookieManager：Cookie 管理器类，处理 Cookie 的增删改查操作
     * - cookie：Cookie 服务别名，便于简洁访问
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $cookieConfig = BASE_PATH . '/config/cookie.php';
        // 注册 Cookie 服务，并传入配置
        $services->set(CookieManager::class)
            ->args([
                $cookieConfig,
            ])
            ->public();
        $services->set('cookie', CookieManager::class)
            ->args([
                $cookieConfig,
            ])        
            ->public();
    }

    /**
     * 启动 Cookie 服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
