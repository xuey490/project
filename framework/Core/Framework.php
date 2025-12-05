<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Framework.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Framework\Container\Container;
use Framework\Middleware\MiddlewareDispatcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Framework.
 *
 * 框架主入口（单例）
 */
final class Framework
{
    // 核心路径常量（可通过配置覆盖）
    private const CONTROLLER_DIR = BASE_PATH . '/app/Controllers';
    private const CONTROLLER_NAMESPACE = 'App\Controllers';
    private const ROUTE_CACHE_FILE = BASE_PATH . '/storage/cache/routes.php';
    private const DIR_PERMISSION = 0755;

    private static ?Framework $instance = null;

    private ?Request $request = null;
    private ContainerInterface $container;
    private Router $router;
    private MiddlewareDispatcher $middlewareDispatcher;
    private Kernel $kernel;
    
    // 明确定义为 LoggerInterface 或 null
    private ?LoggerInterface $logger = null;
	
    /**
     * 单例模式：禁止外部实例化.
     */
    private function __construct()
    {
        $this->initializeBasePath();
        $this->createRequiredDirs();
        $this->initializeConfigAndContainer();
        $this->initializeDependencies();
    }

    /**
     * 防止克隆单例实例.
     */
    private function __clone(): void {}

    /**
     * 防止反序列化单例实例（修正为 public 可见性）.
     *
     * @throws \RuntimeException
     */
    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize singleton');
    }

    /**
     * 单例模式：获取实例.
     */
    public static function getInstance(): Framework
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * FPM入口：完整调度流程.
     */
    public function run(): void
    {
        $request  = Request::createFromGlobals();
        $response = $this->dispatch($request);
        $response->send();
    }

    /*
     * 由workerman调度
     * 传入的是symfony 的request
     */
    public function handleRequest(Request $request): Response
    {
        return $this->dispatch($request);
    }

    /**
     * 获取容器（对外提供接口）.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 核心统一调度入口（FPM/Workerman/Swoole 都走这里）.
     */
    private function dispatch(Request $request): Response
    {
        $start         = microtime(true);
        $this->request = $request;

        try {
            $route = $this->router->match($this->request);

            if ($route === null || $route === false) {
                $response = $this->handleNotFound();
                $this->logRequestAndResponse($this->request, $response, $start);
                return $response;
            }

            if ($this->isEasterEggRoute($route)) {
                $response = $this->handleEasterEgg($route);
                $this->logRequestAndResponse($this->request, $response, $start);
                return $response;
            }

            $this->request->attributes->set('_route', $route);

            $response = $this->middlewareDispatcher->dispatch(
                $this->request,
                fn (Request $req): Response => $this->callController($route)
            );

            if (! $response instanceof Response) {
                $response = $this->normalizeResponse($response);
            }

            $this->logRequestAndResponse($this->request, $response, $start);
            return $response;

        } catch (\Throwable $e) {
            return $this->handleException($e);
        } finally {
            $this->request = null;
        }
    }

    /**
     * 记录简单错误到 storage/logs/error.log（用于在容器日志不可用时回退）.
     */
    private function logError(string $message): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        if (! is_dir($logDir)) {
            @mkdir($logDir, self::DIR_PERMISSION, true);
        }
        
        $file = $logDir . '/error.log';
        $time = date('Y-m-d H:i:s');
        @file_put_contents($file, "[{$time}] {$message}\n", FILE_APPEND);
    }


    /**
     * 初始化 BASE_PATH.
     */
    private function initializeBasePath(): void
    {
        if (! defined('BASE_PATH')) {
            $base = realpath(dirname(__DIR__, 3));
            define('BASE_PATH', $base === false ? getcwd() : $base);
        }
    }

    /**
     * 创建必需目录（支持权限配置）.
     */
    private function createRequiredDirs(): void
    {
        $dirs = [
            BASE_PATH . '/storage/cache',
            BASE_PATH . '/storage/logs',
            BASE_PATH . '/storage/view',
        ];

        $permission = self::DIR_PERMISSION;
        if (function_exists('config')) {
            $permission = config('app.dir_permission', self::DIR_PERMISSION);
        }

        foreach ($dirs as $dir) {
            if (! is_dir($dir) && ! mkdir($dir, (int) $permission, true) && ! is_dir($dir)) {
                throw new \RuntimeException(sprintf('无法创建目录: %s', $dir));
            }
        }
    }

    /**
     * 优化点：简化了 Logger 的初始化逻辑
     */
    private function initializeConfigAndContainer(): void
    {
        // 1. 初始化容器
        Container::init();
        $this->container = Container::getInstance();

        // 2. 启动内核
        $this->kernel = new Kernel($this->container);
        $this->kernel->boot();

        // 3. 从容器获取日志服务
        try {
            $this->logger = $this->container->get('log');
        } catch (\Throwable $e) {
            // 回退到 null，并在必要时使用 logError
            $this->logger = null;
            // 仅在调试时可能需要知道为什么日志初始化失败
            $this->logError('Logger initialization warning: ' . $e->getMessage());
        }
    }

    /**
     * 初始化路由和中间件.
     */
    private function initializeDependencies(): void
    {
        $allRoutes = $this->loadAllRoutes();

        try {
            if ($this->container->has(MiddlewareDispatcher::class)) {
                $this->middlewareDispatcher = $this->container->get(MiddlewareDispatcher::class);
            } else {
                $this->middlewareDispatcher = new MiddlewareDispatcher($this->container);
            }
        } catch (\Throwable $e) {
            $this->middlewareDispatcher = new MiddlewareDispatcher($this->container);
            $this->logError('MiddlewareDispatcher init failed: ' . $e->getMessage());
        }

        $this->router = new Router(
            $allRoutes,
            $this->container,
            self::CONTROLLER_NAMESPACE
        );
    }

    /**
     * 加载所有路由（手动+注解，支持环境区分的缓存）.
     */
    private function loadAllRoutes(): RouteCollection
    {
        $isProduction = function_exists('config') && (string) config('app.env') === 'prod';

        if ($isProduction && file_exists(self::ROUTE_CACHE_FILE)) {
            $serializedRoutes = @file_get_contents(self::ROUTE_CACHE_FILE);
            if ($serializedRoutes !== false) {
                $routes = @unserialize($serializedRoutes);
                if ($routes instanceof RouteCollection) {
                    $this->logger?->info('Loaded routes from cache');
                    return $routes;
                }
                @unlink(self::ROUTE_CACHE_FILE);
            }
        }

        $allRoutes = new RouteCollection();

        // 1. 手动路由
        $routesFile = BASE_PATH . '/config/routes.php';
        if (file_exists($routesFile)) {
            $manualRoutes = require $routesFile;
            if ($manualRoutes instanceof RouteCollection) {
                $allRoutes->addCollection($manualRoutes);
            }
        }

        // 2. 注解路由
        $attrLoader = new AttributeRouteLoader(self::CONTROLLER_DIR, self::CONTROLLER_NAMESPACE);
        $annotatedRoutes = $attrLoader->loadRoutes();
        if ($annotatedRoutes instanceof RouteCollection) {
            $allRoutes->addCollection($annotatedRoutes);
        }

        if ($isProduction) {
            $this->cacheRoutes($allRoutes);
        }

        // 简化了日志记录内容
        $this->logger?->info('[Route Loaded] Total: ' . $allRoutes->count());

        return $allRoutes;
    }

    /**
     * 缓存路由集合（添加序列化错误处理）.
     */
    private function cacheRoutes(RouteCollection $routes): void
    {
        $serialized = @serialize($routes);
        if ($serialized !== false) {
            @file_put_contents(self::ROUTE_CACHE_FILE, $serialized);
            @chmod(self::ROUTE_CACHE_FILE, 0644);
        }
    }

    /**
     * 调用控制器方法（优化参数解析和返回值处理）.
     *
     * @param array<string,mixed> $route
     */
    private function callController(array $route): Response
    {
        $controllerClass = $route['controller'] ?? '';
        $method          = $route['method']     ?? '';
        
        if ($controllerClass === '' || $method === '') {
            return $this->handleNotFound();
        }

        $controller = $this->container->get($controllerClass);
        $this->processRequestParameters($controllerClass, $method, $route['params'] ?? []);

        $argumentResolver = new ArgumentResolver();
        $arguments        = $argumentResolver->getArguments($this->request, [$controller, $method]);

        $response = $controller->{$method}(...$arguments);

        return $this->normalizeResponse($response);
    }

    /**
     * 处理请求参数类型转换.
     *
     * @param class-string        $controllerClass
     * @param array<string,mixed> $routeParams
     */
    private function processRequestParameters(string $controllerClass, string $method, array $routeParams): void
    {
        try {
            $reflection = new \ReflectionMethod($controllerClass, $method);
        } catch (\Throwable) {
            return;
        }

        foreach ($reflection->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            $value = $routeParams[$name] ?? ($this->request->query->has($name) ? $this->request->query->get($name) : null);

            if ($value !== null && $type !== null && $type->isBuiltin()) {
                 $this->request->attributes->set($name, $this->castValueToType($value, $type->getName()));
            }
        }
    }

    /**
     * 类型转换工具方法.
     */
    private function castValueToType(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int'    => (int) $value,
            'float'  => (float) $value,
            'bool'   => (bool) filter_var((string) $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?: false,
            'string' => (string) $value,
            'array'  => is_array($value) ? $value : explode(',', (string) $value),
            default  => $value,
        };
    }

    /**
     * 标准化响应格式.
     */
    private function normalizeResponse(mixed $response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if ($response === null) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        if (is_array($response) || is_object($response)) {
            $payload = @json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
            return new Response($payload, Response::HTTP_OK, ['Content-Type' => 'application/json']);
        }

        return new Response((string) $response, Response::HTTP_OK);
    }

    /**
     * 处理 404 错误.
     */
    private function handleNotFound(): Response
    {
        $content = '404 Not Found';
        try {
            $content = view('errors/404.html.twig', [
                'status_code' => 404,
                'path'        => $this->request->getPathInfo(),
            ]);
        } catch (\Throwable) {
            // ignore
        }
        return new Response($content, Response::HTTP_NOT_FOUND);
    }

    /**
     * 处理异常.
     */
    private function handleException(\Throwable $e): Response
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($e instanceof HttpExceptionInterface) {
            $statusCode = (int) $e->getStatusCode();
        } elseif ($e->getCode() >= 400 && $e->getCode() <= 599) {
            $statusCode = (int) $e->getCode();
        }

        $isDebug = function_exists('config') && config('app.debug');
        
        try {
            if ($isDebug) {
                // 仅在 Debug 模式下收集详细信息
                $content = view('errors/debug.html.twig', [
                    'exception_class'   => get_class($e),
                    'exception_message' => $e->getMessage(),
                    'trace'             => $e->getTraceAsString(),
					'stack_frames'      => count($e->getTrace()), // 堆栈帧数

					// 请求信息（从当前 request 对象获取）
					'request_method' => $this->request->getMethod(),
					'request_uri'    => $this->request->getUri(),
					'client_ip'      => $this->request->getClientIp() ?: 'unknown',
					'request_time'   => date('Y-m-d H:i:s'),
					'user_agent'     => $this->request->headers->get('User-Agent') ?: 'unknown',

					// 环境信息（从容器或配置获取）
					'php_version' => PHP_VERSION,
					'app_env'     => function_exists('config') ? config('app.env') : 'prod',
					'app_debug'   => function_exists('config') ? config('app.debug') : false,
                ]);
            } else {
                $content = view('errors/500.html.twig', ['status_code' => $statusCode]);
            }
        } catch (\Throwable $renderEx) {
            $this->logError('Render exception failed: ' . $renderEx->getMessage());
            $content = 'Server Error';
        }

        return new Response($content, $statusCode);
    }
	
    /**
     * 彩蛋路由判断.
     *
     * @param array<string,mixed> $route
     */
    private function isEasterEggRoute(array $route): bool
    {
        return isset($route['controller'], $route['method']) && (
            ($route['controller'] === '__FrameworkVersionController__' && $route['method'] === '__showVersion__') ||
            ($route['controller'] === '__FrameworkTeamController__' && $route['method'] === '__showTeam__')
        );
    }

    /**
     * 处理彩蛋响应.
     *
     * @param array<string,mixed> $route
     */
    private function handleEasterEgg(array $route): Response
    {
        if ($route['controller'] === '__FrameworkVersionController__') {
            return EasterEgg::getResponse();
        }
        return EasterEgg::getTeamResponse();
    }

    /**
     * 记录请求和响应日志.
     */
    private function logRequestAndResponse(Request $request, Response $response, float $startTime): void
    {
        if ($this->logger === null) {
            return;
        }
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        try {
            $this->logger->info('[Request]', [
                'method'   => $request->getMethod(),
                'uri'      => $request->getPathInfo(),
                'status'   => $response->getStatusCode(),
                'duration' => round($duration, 2) . 'ms',
                'ip'       => $request->getClientIp(),
            ]);
        } catch (\Throwable) {
            // 日志记录失败忽略，避免死循环
        }
    }
}