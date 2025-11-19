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

use Framework\Config\ConfigLoader;
use Framework\Container\Container;
use Framework\Core\Exception\Handler;
use Framework\Middleware\MiddlewareDispatcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\RouteCollection;
use think\facade\Db;

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

    private const DATABASE_CONFIG_FILE = BASE_PATH . '/config/database.php';

    private const DIR_PERMISSION = 0755; // 目录默认权限

    private static ?Framework $instance = null;

    private ?Request $request = null;

    private ContainerInterface $container;

    private Router $router;

    private MiddlewareDispatcher $middlewareDispatcher;

    private Kernel $kernel;

    private ?LoggerInterface $logger = null;
    // private mixed $logger;

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
        // 反序列化时抛出异常，彻底禁止重建实例
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
     * 框架入口：完整调度流程.
     */
    public function run(): void
    {
        $start = microtime(true);

        $this->request = Request::createFromGlobals();

        // 保证 $response 在任何异常路径都有值，避免未定义变量问题
        $response = new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);

        try {
            // 1. 路由匹配
            $route = $this->router->match($this->request);

            if ($route === null || $route === false) {
                $response = $this->handleNotFound();
                $this->logRequestAndResponse($this->request, $response, $start);
                $response->send();
                return;
            }

            // 2. 彩蛋处理
            if ($this->isEasterEggRoute($route)) {
                $response = $this->handleEasterEgg($route);
                $this->logRequestAndResponse($this->request, $response, $start);
                $response->send();
                return;
            }

            // 3. 绑定路由到请求
            $this->request->attributes->set('_route', $route);

            // 4. 执行中间件 + 控制器
            // 确保匿名函数具有类型签名，且返回 Response（或可被 normalize）
            $response = $this->middlewareDispatcher->dispatch(
                $this->request,
                fn (Request $req): Response => $this->callController($route)
            );
        } catch (\Throwable $e) {
            // 记录异常并返回错误响应
            // $this->logger?->error('Unhandled exception in run', ['exception' => $e]);
            $this->logger?->info('Logging exception via logger->logException if available');

            // 如果容器中的 logger 实现了 logException 方法 完整记录（建议可选）
            try {
                // @phpstan-ignore-next-line call may not exist on LoggerInterface
                $this->logger?->logException($e, $this->request);
            } catch (\Throwable $_) {
                // 忽略二次异常记录
            }

            $response = $this->handleException($e);
        }

        // 统一日志记录
        $this->logRequestAndResponse($this->request, $response, $start);
        $response->send();
    }

    /*
     * 由workerman调度 ##
     * 传入的是symfony 的request
     */
    public function handleRequest(Request $request): Response
    {
        $start         = microtime(true);
        $this->request = $request;

        // 默认响应，保证在任何异常路径上都有返回
        $response = new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);

        try {
            $route = $this->router->match($this->request);

            // 未匹配路由
            if ($route === null || $route === false) {
                $response = $this->handleNotFound();
                $this->logRequestAndResponse($this->request, $response, $start);

                return $response;
            }

            // 特殊 EasterEgg 路由（如果你有）
            if ($this->isEasterEggRoute($route)) {
                return $this->handleEasterEgg($route);
            }

            // 绑定路由到请求
            $this->request->attributes->set('_route', $route);

            // 通过中间件分发执行控制器
            $response = $this->middlewareDispatcher->dispatch(
                $this->request,
                function (Request $req) use ($route): Response {
                    return $this->callController($route);
                }
            );

            // 若结果不是 Response，转换一下
            if (! $response instanceof Response) {
                $response = $this->normalizeResponse($response);
            }

            // 记录日志
            $this->logRequestAndResponse($this->request, $response, $start);

			$responseToReturn = $response;
			// 断开引用，允许 GC 回收请求相关内存
			$this->request = null;
			return $responseToReturn;

        } catch (\Throwable $e) {
            // 捕获异常，交给 handleException
            try {
                // @phpstan-ignore-next-line: logger may not have logException
                $this->logger?->logException($e, $this->request);
            } catch (\Throwable $_) {
                // ignore
            }

            return $this->handleException($e);
        }
    }

    /**
     * 获取容器（对外提供接口）.
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * 记录简单错误到 storage/logs/error.log（用于在容器日志不可用时回退）.
     */
    private function logError(string $message): void
    {
        $logDir = BASE_PATH . '/storage/logs';

        if (! is_dir($logDir)) {
            // 使用常量权限
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
            // 简化路径计算：基于当前文件位置定位项目根目录
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

        // 从配置获取目录权限（默认 0777）
        $permission = null;

        // config() 工具函数如果可用则使用，否则使用常量
        if (function_exists('config')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            $permission = config('app.dir_permission', self::DIR_PERMISSION);
        }

        $permission = $permission ?? self::DIR_PERMISSION;

        foreach ($dirs as $dir) {
            if (! is_dir($dir) && ! mkdir($dir, (int) $permission, true) && ! is_dir($dir)) {
                throw new \RuntimeException(sprintf('无法创建目录: %s', $dir));
            }
        }
    }

    /**
     * 初始化配置和容器（核心流程）.
     */
    private function initializeConfigAndContainer(): void
    {
        // 1. 加载配置（保留注释，按需打开）
        // $configLoader = new ConfigLoader(BASE_PATH . '/config');
        // $globalConfig = $configLoader->loadAll();

        // 2. 初始化容器并注入配置
        Container::init();
        $this->container = Container::getInstance();

        // 3. 启动内核
        $this->kernel = new Kernel($this->container);
        $this->kernel->boot();

        // 4. 从容器获取日志服务（若容器未提供则为 null）
        try {
            $logger = null;
            if ($this->container->has('log')) {
                $logger = $this->container->get('log');
            } elseif ($this->container->has(LoggerInterface::class)) {
                $logger = $this->container->get(LoggerInterface::class);
            }

            if ($logger instanceof LoggerInterface) {
                $this->logger = $logger;
            } else {
                // 尝试保留原有行为（如果容器返回具有 logException 的对象）
                $this->logger = $logger;
            }
        } catch (\Throwable $e) {
            // 回退到简单文件日志
            $this->logger = null;
            $this->logError('Failed to initialize logger: ' . $e->getMessage());
        }

        /*
        $this->logger->info('Framework initialized successfully', [
            'base_path' => BASE_PATH,
            'env' => config('app.env'),
        ]);
        */
    }

    /**
     * 初始化所有依赖组件.
     */
    private function initializeDependencies(): void
    {


        // 1. 加载路由（支持缓存）
        $allRoutes = $this->loadAllRoutes();

        // 2. 初始化中间件调度器
        // 优先尝试容器获取，否则新建
        try {
            if ($this->container->has(MiddlewareDispatcher::class)) {
                $this->middlewareDispatcher = $this->container->get(MiddlewareDispatcher::class);
            } else {
                $this->middlewareDispatcher = new MiddlewareDispatcher($this->container);
            }
        } catch (\Throwable $e) {
            // 回退
            $this->middlewareDispatcher = new MiddlewareDispatcher($this->container);
            $this->logError('Failed to initialize MiddlewareDispatcher: ' . $e->getMessage());
        }

        // 3. 初始化路由
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
        $isProduction = false;
        if (function_exists('config')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            $isProduction = (string) config('app.env') === 'prod';
        }

        // 生产环境且缓存存在时，直接加载缓存
        if ($isProduction && file_exists(self::ROUTE_CACHE_FILE)) {
            $serializedRoutes = @file_get_contents(self::ROUTE_CACHE_FILE);
            if ($serializedRoutes !== false) {
                $routes = @unserialize($serializedRoutes);
                if ($routes instanceof RouteCollection) {
                    $this->logger?->info('Loaded routes from cache');
                    return $routes;
                }

                $this->logger?->warning('Route cache is invalid, regenerating');
                @unlink(self::ROUTE_CACHE_FILE);
            }
        }

        // 1. 加载手动路由
        $manualRoutes = null;
        $manualCount  = 0;
        $allRoutes    = new RouteCollection();

        $routesFile = BASE_PATH . '/config/routes.php';
        if (file_exists($routesFile)) {
            $manualRoutes = require $routesFile;
            if ($manualRoutes instanceof RouteCollection) {
                $allRoutes->addCollection($manualRoutes);
                $manualCount = $manualRoutes->count();
            }
        }

        // 2. 加载 Attribute 注解路由
        $attrLoader = new AttributeRouteLoader(
            self::CONTROLLER_DIR,
            self::CONTROLLER_NAMESPACE
        );

        $annotatedRoutes = $attrLoader->loadRoutes();
        $annotatedCount  = 0;

        if ($annotatedRoutes instanceof RouteCollection) {
            $allRoutes->addCollection($annotatedRoutes);
            $annotatedCount = $annotatedRoutes->count();
        }

        // 生产环境缓存路由
        if ($isProduction) {
            $this->cacheRoutes($allRoutes);
        }

        $this->logger?->info(sprintf(
            '[Route Loaded]Loaded %d routes (manual: %d, annotated: %d)',
            $allRoutes->count(),
            $manualCount,
            $annotatedCount
        ));

        return $allRoutes;
    }

    /**
     * 缓存路由集合（添加序列化错误处理）.
     */
    private function cacheRoutes(RouteCollection $routes): void
    {
        $serialized = @serialize($routes);
        if ($serialized === false) {
            throw new \RuntimeException('Failed to serialize route collection');
        }

        @file_put_contents(self::ROUTE_CACHE_FILE, $serialized);
        @chmod(self::ROUTE_CACHE_FILE, 0644); // 缓存文件权限只读
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
        $routeParams     = $route['params']     ?? [];

        if ($controllerClass === '' || $method === '') {
            return $this->handleNotFound();
        }

        // 从容器获取控制器实例（支持依赖注入）
        $controller = $this->container->get($controllerClass);

        // 处理路径参数和查询参数的类型转换
        $this->processRequestParameters($controllerClass, $method, $routeParams);

        // 解析控制器方法参数（Symfony ArgumentResolver）
        $argumentResolver = new ArgumentResolver();
        $arguments        = $argumentResolver->getArguments($this->request, [$controller, $method]);

        // 调用控制器方法
        $response = $controller->{$method}(...$arguments);

        // 统一处理返回值
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
        } catch (\Throwable $e) {
            // 如果反射失败则跳过类型转换
            $this->logger?->warning('ReflectionMethod failed', ['exception' => $e]);
            return;
        }

        foreach ($reflection->getParameters() as $param) {
            $paramName = $param->getName();
            $type      = $param->getType();

            // 优先获取路径参数，其次查询参数
            if (array_key_exists($paramName, $routeParams)) {
                $value = $routeParams[$paramName];
            } elseif ($this->request->query->has($paramName)) {
                $value = $this->request->query->get($paramName);
            } else {
                // 无参数值，跳过
                continue;
            }

            // 内置类型转换
            if ($value !== null && $type !== null && $type->isBuiltin()) {
                $typedName = $type->getName();
                $value     = $this->castValueToType($value, $typedName);
                $this->request->attributes->set($paramName, $value);
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
            $payload = @json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $payload = $payload === false ? '' : $payload;

            return new Response(
                $payload,
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );
        }

        return new Response((string) $response, Response::HTTP_OK);
    }

    /**
     * 处理 404 错误.
     */
    private function handleNotFound(): Response
    {
        $path = $this->request->getPathInfo() ?? '';

        $content = '';
        try {
            $content = view('errors/404.html.twig', [
                'status_code' => Response::HTTP_NOT_FOUND,
                'status_text' => 'Not Found',
                'message'     => 'The requested page could not be found.',
                'path'        => $path,
            ]);
        } catch (\Throwable $e) {
            $this->logError('Failed to render 404 view: ' . $e->getMessage());
            $content = '404 Not Found';
        }

        return new Response($content, Response::HTTP_NOT_FOUND);
    }

    /* 遗弃
    500 错误的友好页面
    */
    private function handleException1(\Throwable $e): Response
    {
        // 设置HTTP响应头为500
        http_response_code(500);

        // 渲染Twig模板，并将异常对象传递过去
        // 注意：我们传递的是整个$e对象，而不是print_r的结果
        $html = view('errors/500.html.twig', [
            'exception' => $e,
        ]);

        // 返回一个包含渲染后HTML的Response对象
        return new Response($html, 500);
        // return new Response('500 Server Error', 500);
    }

    /**
     * 处理异常.
     */
    private function handleException(\Throwable $e): Response
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($e instanceof Handler) {
            try {
                $statusCode = $e->getStatusCode();
            } catch (\Throwable $_) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        // 准备模板所需的所有变量（直接传递具体值，不依赖模板函数）
        $templateVars = [
            // 异常信息
            'exception_class'   => get_class($e),
            'exception_code'    => $e->getCode(),
            'exception_message' => $e->getMessage(),
            'exception_file'    => $e->getFile(),
            'exception_line'    => $e->getLine(),
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
        ];

        // 开发环境渲染调试模板
        $content = '';
        try {
            if (function_exists('config') && config('app.debug')) {
                $content = view('errors/debug.html.twig', $templateVars);
            } else {
                $content = view('errors/500.html.twig', [
                    'status_code' => $statusCode,
                    'status_text' => Response::$statusTexts[$statusCode] ?? 'Server Error',
                    'message'     => 'An unexpected error occurred. Please try again later. 程序发生错误，请稍后再试！',
                ]);
            }
        } catch (\Throwable $e2) {
            $this->logError('Failed to render exception view: ' . $e2->getMessage());
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
        if (! isset($route['controller'], $route['method'])) {
            return false;
        }

        return
            ($route['controller'] === '__FrameworkVersionController__' && $route['method'] === '__showVersion__')
            || ($route['controller'] === '__FrameworkTeamController__' && $route['method'] === '__showTeam__');
    }

    /**
     * 处理彩蛋响应.
     *
     * @param array<string,mixed> $route
     */
    private function handleEasterEgg(array $route): Response
    {
        if (isset($route['controller']) && $route['controller'] === '__FrameworkVersionController__') {
            return EasterEgg::getResponse();
        }

        return EasterEgg::getTeamResponse();
    }

    /**
     * 记录请求和响应日志.
     */
    private function logRequestAndResponse(Request $request, Response $response, float $startTime): void
    {
        $duration = microtime(true) - $startTime;

        try {
            $this->logger?->info('[Request processed]', [
                'method'   => $request->getMethod(),
                'path'     => $request->getPathInfo(),
                'status'   => $response->getStatusCode(),
                'duration' => round($duration * 1000, 2) . 'ms', // 转换为毫秒
                'ip'       => $request->getClientIp(),
            ]);
        } catch (\Throwable $e) {
            // 回退到文件日志
            $this->logError('Failed to write structured request log: ' . $e->getMessage());
        }
    }
}
