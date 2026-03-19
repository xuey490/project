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

use Framework\Config\ConfigService;
use Framework\Config\Cache\ConfigCache;
use Framework\Container\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * 配置服务提供者
 *
 * 负责注册和管理框架的配置服务，提供配置文件的加载和缓存功能。
 * 主要功能包括：
 * - 注册 ConfigCache 服务，用于配置的持久化缓存
 * - 注册 ConfigService 服务，提供统一的配置访问接口
 * - 支持排除特定配置文件（如 routes.php、services.php）
 */
final class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册配置服务到依赖注入容器
     *
     * 注册以下服务：
     * - config_cache：配置缓存服务，将配置缓存到文件中以提高性能
     * - config：配置服务别名，便于简洁访问
     * - ConfigService：配置服务类，提供配置加载和访问功能
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();
		
		$config_cache = '%kernel.project_dir%/storage/cache/config_cache.php';
		// 注册 config_cache 服务
        $services->set('config_cache', ConfigCache::class)
            ->args([
                $config_cache ,
				300
            ])
            ->public();		
		
		
        // 注册 ConfigService 服务
        $services->set('config', ConfigService::class)	// $globalConfig = $this->container->get('config')->load();
            ->args([
                '%kernel.project_dir%/config',
                service('config_cache'),
				null , 
				['routes.php', 'services.php']
            ])
            ->public();  // ($this->container->get(ConfigService::class)->load());

        // 注册 ConfigService 业务类
        $services->set(ConfigService::class)
            ->args([
                '%kernel.project_dir%/config',
                service('config_cache'),
				null , 
				['routes.php', 'services.php']
            ])
            ->public();
    }

    /**
     * 启动配置服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
