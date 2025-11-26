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

namespace Framework\Core;

use Framework\Container\Container;
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
     * 从容器中获取一个已注册的服务.
	 *  (可选) 为了让 get 方法能直接从 App 调用，保持 API 友好
     *
     * @param string $id 服务的唯一ID
     *
     * @return object|null
     */
    public static function get(string $id): ?object
    {
        return self::getContainer()->get($id);
    }
	
    /**
     * 从容器解析服务，或在容器外创建实例.
     *
     * @param string $id     服务名或类名
     * @param array  $params 可选构造参数（如果容器支持 make）
     */
    public static function make(string $id, array $params = []): object
    {
        $container = self::getContainer();

        // 1. 优先从容器获取（工厂、单例、服务等）
        if ($container->has($id) && empty($params)) {
            $service = $container->get($id);

            if (! is_object($service)) {
                throw new \RuntimeException("服务 {$id} 返回不是对象类型");
            }

            return $service;
        }
		
        // 如果容器是我们自定义的 Container 类，优先使用其 make 方法
        #if ($container instanceof Container) {
        #    return $container->make($id, $params);
        #}

        // 后备逻辑，与之前版本相同
        if (empty($params) && $container->has($id)) {
            return $container->get($id);
        }

        if ($container instanceof Container) {
            return $container->make($id, $params);
        }

        throw new \RuntimeException("无法解析服务 '{$id}'。");
    }
	
	

    /**
     * 检查容器是否存在指定服务
     */
    public static function has(string $id): bool
    {
        $container = self::$container;
        return $container !== null && $container->has($id);
    }
	
    // =================================================================
    // 以下是新增的、转发到 Container 实例的静态方法
    // =================================================================

    /**
     * 注册一个单例服务.
     *
     * @param string   $id      服务ID
     * @param callable $factory 工厂闭包
     */
    public static function singleton(string $id, callable $factory): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->singleton($id, $factory);
        } else {
            throw new \RuntimeException('当前容器不支持 singleton 方法。');
        }
    }

    /**
     * 绑定一个抽象（接口）到具体实现.
     *
     * @param string $abstract 接口或抽象类名
     * @param string $concrete 具体实现类名
     * @param bool   $shared   是否为单例
     */
    public static function bind(string $abstract, string $concrete, bool $shared = false): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->bind($abstract, $concrete, $shared);
        } else {
            throw new \RuntimeException('当前容器不支持 bind 方法。');
        }
    }

    /**
     * 通过工厂函数注册一个服务.
     *
     * @param string   $id      服务ID
     * @param callable $factory 工厂闭包
     * @param bool     $shared   是否为单例
     */
    public static function factory(string $id, callable $factory, bool $shared = false): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->factory($id, $factory, $shared);
        } else {
            throw new \RuntimeException('当前容器不支持 factory 方法。');
        }
    }

    /**
     * 注册一个已存在的对象实例.
     *
     * @param string $id       服务ID
     * @param object $instance 要注册的对象
     */
    public static function instance(string $id, object $instance): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->instance($id, $instance);
        } else {
            throw new \RuntimeException('当前容器不支持 instance 方法。');
        }
    }

    /**
     * 注册一个容器参数.
     *
     * @param string $name  参数名
     * @param mixed  $value 参数值
     */
    public static function parameter(string $name, array|bool|float|int|string|\UnitEnum|null $value): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->parameter($name, $value);
        } else {
            // Symfony ContainerInterface 原生支持参数设置
            if (method_exists($container, 'setParameter')) {
                $container->setParameter($name, $value);
            } else {
                throw new \RuntimeException('当前容器不支持 parameter 方法。');
            }
        }
    }

    /**
     * 为一个已注册的服务添加标签.
     *
     * @param string $id         服务ID
     * @param string $tag        标签名
     * @param array  $attributes 标签属性
     */
    public static function tag(string $id, string $tag, array $attributes = []): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->tag($id, $tag, $attributes);
        } else {
            throw new \RuntimeException('当前容器不支持 tag 方法。');
        }
    }

    /**
     * 注册一个延迟初始化的服务.
     *
     * @param string $id       服务ID
     * @param string $concrete 具体实现类名
     * @param bool   $shared   是否为单例
     */
    public static function lazy(string $id, string $concrete, bool $shared = true): void
    {
        $container = self::getContainer();
        if ($container instanceof Container) {
            $container->lazy($id, $concrete, $shared);
        } else {
            throw new \RuntimeException('当前容器不支持 lazy 方法。');
        }
    }	
}
