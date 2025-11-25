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
use Framework\Database\ORMFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class ORMServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $dbConfig = require BASE_PATH . '/config/database.php';
        $ormType  = $dbConfig['engine'] ?? 'thinkORM';

        // 注册 ORMFactory
        $services->set(ORMFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service(LoggerInterface::class)->nullOnInvalid(),
            ])
            ->public();

        // 别名 "orm"
        $services->set('orm', ORMFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service(LoggerInterface::class)->nullOnInvalid(),
            ])
            ->public();
    }

    /*
    模型基类的别名，暂时不可用
    */
    public function boot(ContainerInterface $container): void {}
}
