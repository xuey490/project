<?php

declare(strict_types=1);

namespace Framework\Providers;

use Framework\Container\ServiceProviderInterface;
use Framework\Utils\ORMFactory;


use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;


use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Psr\Log\LoggerInterface;

final class ORMServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $dbConfig = require BASE_PATH . '/config/database.php';
        $ormType  = $dbConfig['engine'] ?? 'think';

        // 注册 ORMFactory
        $services->set(ORMFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service(LoggerInterface::class)->nullOnInvalid()
            ])
            ->public();

        // 别名 "orm"
        $services->set('orm', ORMFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service(LoggerInterface::class)->nullOnInvalid()
            ])
            ->public();
    }

    public function boot(ContainerInterface $container): void
    {

    }

    /**
     * 工厂方法：创建并配置一个 Capsule 实例
     * @param IlluminateContainer $container
     * @param array $dbConfig
     * @return Capsule
     */
    public static function createCapsule(IlluminateContainer $container, array $dbConfig): Capsule
    {
        $capsule = new Capsule($container);

        $connName = $dbConfig['default'];
        $connConfig = $dbConfig['connections'][$connName] ?? [];

        // 添加数据库连接配置
        $capsule->addConnection([
            'driver'    => $connConfig['driver'] ?? 'mysql',
            'host'      => $connConfig['host'] ?? '127.0.0.1',
            'port'      => $connConfig['port'] ?? 3306,
            'database'  => $connConfig['database'] ?? '',
            'username'  => $connConfig['username'] ?? '',
            'password'  => $connConfig['password'] ?? '',
            'charset'   => $connConfig['charset'] ?? 'utf8mb4',
            'collation' => $connConfig['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => $connConfig['prefix'] ?? '',
        ], $connName); // 指定连接名称

        // 注册事件调度器
		$container->instance('db', $capsule->getDatabaseManager());

        $container->instance('events', new Dispatcher($container));

		Facade::setFacadeApplication($container);

		$capsule->setAsGlobal();
		
        // 启动 Eloquent ORM
        $capsule->bootEloquent();

        return $capsule;
    }


    /**
     * Eloquent 启动器（不注册为服务）
     */
    private function bootstrapEloquent(array $dbConfig): void
    {
        $illuminate = new IlluminateContainer();
        $capsule    = new Capsule($illuminate);

        $conn = $dbConfig['connections'][$dbConfig['default']] ?? [];
		
        $capsule->addConnection([
            'driver'    => $conn['driver'] ?? 'mysql',
            'host'      => $conn['host'] ?? '127.0.0.1',
            'port'      => $conn['port'] ?? 3306,
            'database'  => $conn['database'] ?? '',
            'username'  => $conn['username'] ?? '',
            'password'  => $conn['password'] ?? '',
            'charset'   => $conn['charset'] ?? 'utf8mb4',
            'collation' => $conn['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => $conn['prefix'] ?? '',
        ]);

        // 注册事件
        $illuminate->instance('events', new Dispatcher($illuminate));

        // 绑定 Facade
        Facade::setFacadeApplication($illuminate);

        // 设置全局
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
