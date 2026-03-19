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
#use Framework\Security\AuthGuard;
use Framework\Utils\JwtFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * JWT 服务提供者
 *
 * 负责注册和管理框架的 JWT（JSON Web Token）认证服务。
 * 主要功能包括：
 * - 注册 JwtFactory 服务，提供 JWT 令牌的生成和验证功能
 * - 支持用户身份认证和授权
 */
final class JwtServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册 JWT 服务到依赖注入容器
     *
     * 注册以下服务：
     * - jwt：JwtFactory 服务实例，提供 JWT 令牌操作
     *
     * 该服务使用自动装配（autowire）功能，自动注入所需依赖。
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册jwt服务
        $services->set('jwt', JwtFactory::class)
            ->autowire()
            ->public();
		
		/*
        $services->set(AuthGuard::class)
            ->args([
                service('jwt'),
            ])
            ->autowire()
            ->public();

        $services->set('AuthGuard', AuthGuard::class)
            ->args([
                service('jwt'),
            ])
            ->autowire()
            ->public();
		*/
    }

    /**
     * 启动 JWT 服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加启动逻辑。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void
}
