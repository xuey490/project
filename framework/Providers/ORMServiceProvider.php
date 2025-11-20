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

}
