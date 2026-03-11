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
use Framework\Database\DatabaseFactory;
//use Psr\Log\LoggerInterface;
use Framework\Log\LoggerService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * 数据库服务提供者
 *
 * 负责注册和管理框架的数据库服务，支持多种 ORM 引擎（如 ThinkORM）。
 * 主要功能包括：
 * - 注册 DatabaseFactory 服务，用于创建数据库连接
 * - 根据 database.php 配置文件确定 ORM 引擎类型
 * - 注入日志服务，支持数据库操作日志记录
 */
final class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * 注册数据库服务到依赖注入容器
     *
     * 注册以下服务：
     * - DatabaseFactory：数据库工厂类，根据配置创建数据库连接
     * - db：数据库服务别名，便于简洁访问
     *
     * 服务参数包括：
     * - 数据库配置数组
     * - ORM 引擎类型（默认为 thinkORM）
     * - 日志服务实例
     *
     * @param ContainerConfigurator $configurator 容器配置器，用于注册服务定义
     * @return void
     */
    public function register(ContainerConfigurator $configurator): void
    {
        $services = $configurator->services();

        $dbConfig = require BASE_PATH . '/config/database.php';
        $ormType  = $dbConfig['engine'] ?? 'thinkORM';
		
        // 注册 DatabaseFactory ，引入log服务
        $services->set(DatabaseFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service('log') //->nullOnInvalid(),
            ])
            ->public();

        // 别名 "db" ，引入log服务
        $services->set('db', DatabaseFactory::class)
            ->args([
                $dbConfig,
                $ormType,
                service('log'), //service(LoggerInterface::class)->nullOnInvalid(),
            ])
            ->public();
    }

    /**
     * 启动数据库服务
     *
     * 该方法在服务注册后调用，用于执行额外的初始化操作。
     * 当前实现为空，可根据需要添加模型基类别名等功能。
     *
     * @param ContainerInterface $container 依赖注入容器实例
     * @return void
     */
    public function boot(ContainerInterface $container): void {}
}
