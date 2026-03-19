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
use Framework\Validation\ThinkValidatorFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use think\Validate;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * 验证服务提供者
 *
 * 负责注册和管理框架的数据验证服务，基于 ThinkPHP 验证器实现。
 * 主要功能包括：
 * - 注册 ThinkValidatorFactory 工厂类，用于创建验证器实例
 * - 注册 validate 服务，提供数据验证功能
 */
final class ValidateServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册验证服务到依赖注入容器
     *
     * 注册以下服务：
     * - ThinkValidatorFactory：验证器工厂类，用于创建验证器实例
     * - validate：ThinkPHP 验证器实例，通过工厂方法创建
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        // 注册ThinkValidator工厂类
        $services->set(ThinkValidatorFactory::class)
            ->public();

        // 注册thinkphp validate
        $services->set('validate', Validate::class)
            // 使用 factory() 方法，并指向工厂类
            ->factory([service(ThinkValidatorFactory::class), 'create'])
            ->public(); // 允许从容器外部获取
    }

    public function boot(ContainerInterface $container): void
    # public function boot(ContainerConfigurator $container): void
    {}
}
