<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: ModelsProvider.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Framework\Container\ServiceProviderInterface;

/*
* 注册所有模型类
*/
final class ModelsProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $services->load('App\\Models\\', \dirname(__DIR__) . '/Models/**/*.php')
            ->autowire()
            ->autoconfigure()
            ->public();
    }
	
	public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $container): void
    {

    }	
	
}
