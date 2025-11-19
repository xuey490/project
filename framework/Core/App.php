<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Symfony\Component\DependencyInjection\ContainerInterface;

# use Psr\Container\ContainerInterface;

/*
// 框架启动
Container::init();
$container = Container::getInstance();
App::setContainer($container);

// 获取服务
$loggerService = getService(\Framework\Log\LoggerService::class);
$logger = getService(\Framework\Log\Logger::class);
$log = getService('log'); // 别名
$configLoader = app('config');
$configService = app(\Framework\Config\ConfigService::class);

*/
class App
{
    protected static ?ContainerInterface $container = null;

    /**
     * 设置全局应用容器.
     */
    public static function setContainer(ContainerInterface $container): void
    {
        if (! method_exists($container, 'get') || ! method_exists($container, 'has')) {
            throw new \InvalidArgumentException(
                sprintf('容器必须实现 get() 和 has() 方法，当前类型: %s', get_class($container))
            );
        }

        self::$container = $container;
    }

    /**
     * 获取全局容器.
     *
     * @throws \RuntimeException
     */
    public static function getContainer(): ContainerInterface
    {
        if (self::$container === null) {
            throw new \RuntimeException('应用容器尚未初始化，请先调用 App::setContainer()。');
        }

        return self::$container;
    }

    /**
     * 从容器解析服务
     *
     * @param string $id     服务名或类名
     * @param array  $params 可选构造参数（如果容器支持 make）
     */
    public static function make(string $id, array $params = []): object
    {
        $container = self::getContainer();

        if (! empty($params) && method_exists($container, 'make')) {
            return $container->make($id, $params);
        }

        if (! $container->has($id)) {
            throw new \RuntimeException(sprintf('服务 "%s" 未注册于容器。', $id));
        }

        $service = $container->get($id);

        if (! is_object($service)) {
            throw new \RuntimeException(sprintf('服务 "%s" 返回不是对象类型。', $id));
        }

        return $service;
    }

    /**
     * 检查容器是否存在指定服务
     */
    public static function has(string $id): bool
    {
        $container = self::$container;
        return $container !== null && $container->has($id);
    }
}
