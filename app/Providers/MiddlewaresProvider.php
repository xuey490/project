<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: MiddlewaresProvider.php
 * @Date: 2025-11-13
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Providers;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Framework\Container\ServiceProviderInterface;

/*
* 批量注册路由中间件
*/
final class MiddlewaresProvider implements ServiceProviderInterface
{
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

		$services->load('App\\Middlewares\\', '../app/Middlewares/**/*.php')
			->autowire()      // 支持中间件的依赖自动注入（如注入UserService）
			->autoconfigure() // 支持中间件添加标签（如后续需要事件监听）
			->public(); // 关键：标记为公开，因为中间件需要通过容器动态获取（如从注解解析后）
    }
	
	public function boot(ContainerInterface $container): void
    #public function boot(ContainerConfigurator $container): void
    {

    }	
	
}
