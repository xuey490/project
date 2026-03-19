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

// use Framework\Config\ConfigService;
use Framework\Container\ServiceProviderInterface;
use Framework\Session\FileSessionHandler;
use Framework\Session\RedisGroupSessionHandler;
use Framework\Utils\RedisFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * 会话服务提供者
 *
 * 负责注册和管理框架的会话服务，支持多种存储后端。
 * 主要功能包括：
 * - 注册 Redis 客户端服务，用于 Redis 存储模式
 * - 注册 Session Handler 服务，支持文件、Redis、Redis 分组三种存储模式
 * - 注册 Session Storage 服务，管理会话存储
 * - 注册 Session 服务，提供会话操作接口
 *
 * 支持的存储类型：
 * - file：文件存储模式（默认）
 * - redis：Redis 存储模式
 * - redis_grouped：Redis 分组存储模式
 */
final class SessionServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册会话服务到依赖注入容器
     *
     * 根据配置文件中的 storage_type 设置，注册以下服务：
     * - redis.client / redis：Redis 客户端服务
     * - session.handler：会话处理器，根据存储类型选择不同实现
     * - session.handler.strict：严格会话处理器（文件模式）
     * - session.storage：会话存储服务
     * - session：会话服务实例
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

		$cacheFile = BASE_PATH . '/storage/cache/config_cache.php';
		$cache = new \Framework\Config\Cache\ConfigCache($cacheFile, 300);
		$load = new \Framework\Config\ConfigService( BASE_PATH . '/config', $cache, null , ['routes.php', 'services.php']);

        // === 1. 加载配置 ===
        $redisConfig   = $load->get('redis');  // require \dirname(__DIR__, 2) . '/config/redis.php';
        $sessionConfig = $load->get('session'); // require \dirname(__DIR__, 2) . '/config/session.php';

        $storageType     = $sessionConfig['storage_type']          ?? 'file';
        $sessionOptions  = $sessionConfig['options']               ?? [];
        $fileSavePath    = $sessionConfig['file_save_path']        ?? '%kernel.project_dir%/storage/sessions';
        $groupPrefix     = $sessionConfig['redis']['group_prefix'] ?? 'session:default';
        $ttl             = $sessionConfig['redis']['ttl']          ?? 3600;

        // === 2. 注册 Redis 客户端（只有 Redis 模式需要）===
        // if (in_array($storageType, ['redis', 'redis_grouped'], true)) {
        // 注册 RedisFactory 服务
        $services->set('redis.client', \Redis::class)
            ->factory([RedisFactory::class, 'createRedisClient']) // 工厂方法放在自身
            ->args([$redisConfig])
            ->public();

        // 或者注册封装类别名
        $services
            ->alias('redis', 'redis.client')
            ->public();
        // }

        // === 3. 注册 Session Handler & Storage ===
        switch ($storageType) {
            case 'redis_grouped':
                // ✅ 使用分组 Redis 存储
                $services->set('session.handler', RedisGroupSessionHandler::class)
                    ->args([
                        service('redis.client'),
                        [
                            'group_prefix'   => $groupPrefix,
                            'ttl'            => $ttl,
                            'prefix'         => 'redis_grouped_',         // 普通前缀
                            'locking'        => true,          // 启用显式锁（默认 true）
                            'spin_lock_wait' => 150000,        // 自旋等待 microseconds
                            'lock_ttl'       => 30000,         // 锁过期时间 ms（用于 SET PX）
                        ],
                    ])
                    ->public();

                $services->set('session.storage', NativeSessionStorage::class)
                    ->args([$sessionOptions, service('session.handler')])
                    ->public();
                break;
            case 'redis':
                // ✅ 普通 Redis 存储
                $services->set('session.handler', RedisSessionHandler::class)
                    ->args([
                        service('redis.client'),
                        [
                            'prefix' => 'redis_session_',	// 前缀
                            'ttl'    => $ttl,
                        ],
                    ])
                    ->public();

                $services->set('session.storage', NativeSessionStorage::class)
                    ->args([$sessionOptions, service('session.handler')])
                    ->public();
                break;
            case 'file':
            default:
                // ✅ 文件存储（自定义 FileSessionHandler）
                $services->set('session.handler', FileSessionHandler::class)
                    ->call('setSavePath', [$fileSavePath])
                    ->call('setPrefix', [$sessionOptions['name'] ?? 'sess'])
                    ->public();

                $services->set('session.handler.strict', StrictSessionHandler::class)
                    ->args([service('session.handler')])
                    ->public();

                $services->set('session.storage', NativeSessionStorage::class)
                    ->args([$sessionOptions, service('session.handler.strict')])
                    ->public();
                break;
        }

        // === 4. 注册 Session 服务 ===
        $services->set('session', Session::class)
            ->args([service('session.storage')])
            ->public();
    }

    /**
     * 启动会话服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
