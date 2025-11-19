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

use Framework\Cache\CacheFactory;
use Framework\Config\Config;
use Framework\Core\Exception\Handler as ExceptionHandler;
use Framework\Event\Dispatcher;
use Framework\Event\ListenerScanner;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Kernel
{
    private ContainerInterface $container;

    private bool $booted = false;

    public function __construct(ContainerInterface $container)
    {
        // 确保容器是可编译的（Symfony ContainerBuilder）
        if (! $container instanceof ContainerInterface) {
            throw new \InvalidArgumentException('容器必须是 ContainerInterface 实例');
        }
        $this->container = $container;
    }

    /**
     * 启动内核：初始化核心服务、注册事件、设置异常处理.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return; // 防止重复启动
        }

        // 1. 设置全局容器入口（供助手函数使用）
        App::setContainer($this->container);

        $sessionConfig = require $this->getProjectDir() . '/config/session.php';
        if (($sessionConfig['storage_type'] ?? 'redis') === 'file') {
            $savePath = $sessionConfig['file_save_path'];
            if (! is_dir($savePath)) {
                mkdir($savePath, 0755, true);
            }
            ini_set('session.save_path', $savePath);
        }

        // $debug = app('config')->get('app.debug', false);
        // dump(app()->getServiceIds()); // 查看所有服务 ID

        // 2. 初始化时区（从配置获取）
        $timezone = app('config')->get('app.time_zone', 'UTC');
        date_default_timezone_set($timezone);



        // 3. 注册事件监听器
        $this->registerEventListeners();

        // 4. 设置异常处理机制
        $this->setupExceptionHandling();

        $this->booted = true;
    }

    /**
     * 获取容器（修正返回类型）.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 检查内核是否已启动.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * 获取项目根目录.
     */
    public function getProjectDir(): string
    {
        return dirname(__DIR__, 2); // 从 framework/Core 到项目根
    }


    /**
     * 注册事件监听器（基于扫描的方式）.
     */
    private function registerEventListeners(): void
    {
        $dispatcher = $this->container->get(Dispatcher::class);
        $cache      = $this->container->get(CacheFactory::class); // 从容器获取缓存服务

        // 扫描并注册所有事件订阅者
        $scanner = new ListenerScanner($cache);
        foreach ($scanner->getSubscribers() as $subscriberClass) {
            // 从容器获取订阅者实例（支持依赖注入）
            $subscriber = $this->container->get($subscriberClass);
            $dispatcher->addSubscriber($subscriber);
        }
    }

    /**
     * 设置异常处理机制（统一接管错误与异常）.
     */
    private function setupExceptionHandling(): void
    {
        // 1. 注册异常处理器
        $exceptionHandler = $this->container->get(ExceptionHandler::class);
        set_exception_handler(function (\Throwable $e) use ($exceptionHandler) {
            $exceptionHandler->report($e);
            $exceptionHandler->render($e); // ->send();
            //return ;
			exit(1); // 异常后终止程序
        });

        // 2. 注册错误处理器（将错误转为异常）
        set_error_handler(function ($severity, $message, $file, $line) {
            // 忽略非用户级错误（如E_STRICT）
            if (! (error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // 3. 注册致命错误处理器
        register_shutdown_function(function () use ($exceptionHandler) {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $e = new \ErrorException(
                    $error['message'] ?? '致命错误',
                    0,
                    $error['type'] ?? E_ERROR,
                    $error['file'] ?? '未知文件',
                    $error['line'] ?? 0
                );
                $exceptionHandler->report($e);
                $exceptionHandler->render($e)->send();
                //return ;
				exit(1);
            }
        });
    }
}
