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

use Framework\Middleware\MiddlewareDispatcher;
use Framework\Middleware\MiddlewareMethodOverride;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
// 引入你的静态容器
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

// 推荐使用 PSR-11 标准接口

class Router
{
    /**
     * 所有路由集合（手动路由 + 注解路由）.
     * @var RouteCollection
     */
    private $allRoutes;

    /**
     * 控制器基础命名空间.
     * @var string
     */
    private $controllerNamespace = 'App\Controllers'; // 默认控制器命名空间

    // 新增：用于存储 DI 容器
    private $container;

    /**
     * 构造函数：仅接收合并后的路由集合（职责单一化）.
     * @param RouteCollection $allRoutes           合并后的所有路由（手动 + 注解）
     * @param string          $controllerNamespace 控制器基础命名空间（可选，默认 App\Controllers）
     */
    public function __construct(
        RouteCollection $allRoutes,
        ContainerInterface $container, // <--- 新增参数 // ← 期望 PSR-11 容器
        string $controllerNamespace = 'App\Controllers'
    ) {
        $this->allRoutes           = $allRoutes;
        $this->container           = $container; // <--- 存储容器
        $this->controllerNamespace = $controllerNamespace;
    }

    /**
     * 核心路由匹配方法
     * 优先级：手动路由 > 注解路由 > 自动解析路由.
     * @return null|array 路由元数据：[controller, method, params, middleware]
     */
    public function match(Request $request): ?array
    {
        // 1. 预处理：处理PUT/DELETE请求、去除URL的.html后缀
        $this->preprocessRequest($request);

        $path    = $request->getPathInfo();
        $context = new RequestContext();
        $context->fromRequest($request);

        // 🔥 检查 版本彩蛋
        if (EasterEgg::isTriggeredVersion($request)) {
            return EasterEgg::getRouteMarker();
        }

        // 🔥 检查 团队彩蛋（团队名单）
        if (EasterEgg::isTriggeredTeam($request)) {
            return EasterEgg::getTeamRouteMarker();
        }

        // 2. 策略1：匹配手动路由 + 注解路由（共用Symfony UrlMatcher）
        $manualOrAnnotationRoute = $this->matchManualAndAnnotationRoutes($path, $context);
        if ($manualOrAnnotationRoute) {
            // $context->setMethod('GET');	//✅ 强制设置方法
            return $manualOrAnnotationRoute;
        }

        // 再尝试自动路由（GET 默认）

        // 3. 策略2：匹配自动解析路由（最低优先级）
        $autoRoute = $this->matchAutoRoute($path, $request);
        if ($autoRoute) {
            return $autoRoute;
        }

        // 4. 未匹配到任何路由
        return null;
    }

    /**
     * 匹配路由.
     */
    private function matchManualAndAnnotationRoutes(string $path, RequestContext $context): ?array
    {
        try {
            $matcher    = new UrlMatcher($this->allRoutes, $context);
            $parameters = $matcher->match($path);

            $routeName      = $parameters['_route'];
            $routeObject    = $this->allRoutes->get($routeName);
            $middlewareList = $routeObject ? $routeObject->getDefault('_middleware', []) : [];

            if (! isset($parameters['_controller'])) {
                return null;
            }

            [$controllerClass, $actionMethod] = explode('::', $parameters['_controller'], 2);

            unset($parameters['_controller'], $parameters['_route']);

            return [
                'controller' => $controllerClass,
                'method'     => $actionMethod,
                'params'     => $parameters,
                'middleware' => $middlewareList,
            ];
        } catch (MethodNotAllowedException|ResourceNotFoundException $e) {
            // ✅ 捕获两种异常，让 POST / PUT / DELETE 自动回退到自动路由逻辑
            return null;
        }
    }

    /**
     * 匹配自动解析路由（支持多级命名空间、自动参数映射）.
     */
    private function matchAutoRoute(string $path, Request $request): ?array
    {
        $path = rtrim($path, '/');
        // 拆分路径为段（过滤空值，确保数组键从0开始）
        $pathSegments  = array_values(array_filter(explode('/', $path)));
        $requestMethod = $request->getMethod();

        // 根路径特殊处理：映射到 HomeController@index
        if (empty($pathSegments)) {
            $homeController = "{$this->controllerNamespace}\\Home";
            if (class_exists($homeController) && method_exists($homeController, 'index')) {
                return [
                    'controller' => $homeController,
                    'method'     => 'index',
                    'params'     => [],
                    'middleware' => [],
                ];
            }
            return null;
        }

        // 核心逻辑：从长到短尝试匹配控制器（支持多级命名空间）
        // 例：/api/v2/user/show/1 → 先试 [api,v2,user] → 再试 [api,v2] → 最后试 [api]
        for ($controllerSegmentLength = count($pathSegments); $controllerSegmentLength >= 1; --$controllerSegmentLength) {
            // 1. 提取控制器路径段，构建控制器类名
            $controllerSegments = array_slice($pathSegments, 0, $controllerSegmentLength);
            $controllerClass    = $this->buildControllerClassName($controllerSegments);

            // 控制器不存在，跳过当前长度，尝试更短的路径段
            if (! class_exists($controllerClass)) {
                continue;
            }

            // 2. 提取动作+参数段，尝试匹配控制器方法
            $actionAndParamSegments = array_slice($pathSegments, $controllerSegmentLength);
            $routeInfo              = $this->matchActionAndParams($controllerClass, $actionAndParamSegments, $requestMethod);

            if ($routeInfo) {
                return array_merge([
                    'controller' => $controllerClass,
                    'middleware' => [], // 自动路由默认无中间件，可按需扩展
                ], $routeInfo);
            }
        }

        // 未匹配到自动路由
        return null;
    }

    /**
     * 构建控制器完整类名（支持多级命名空间）
     * 例：[api, v2, user] → App\Controllers\Api\V2\UserController.
     */
    private function buildControllerClassName(array $segments): string
    {
        if (empty($segments)) {
            // 先尝试 Home，再尝试 HomeController
            $homeClass = "{$this->controllerNamespace}\\Home";
            if (class_exists($homeClass)) {
                return $homeClass;
            }
            return "{$this->controllerNamespace}\\HomeController";
        }

        // 尝试不加后缀的类名
        $namespaceSegments      = array_map('ucfirst', $segments);
        $classNameWithoutSuffix = $this->controllerNamespace . '\\' . implode('\\', $namespaceSegments);

        if (class_exists($classNameWithoutSuffix)) {
            return $classNameWithoutSuffix;
        }

        // 回退：加 Controller 后缀（兼容旧命名）
        $lastSegment = array_pop($namespaceSegments);
        $lastSegment .= 'Controller';
        $namespaceSegments[] = $lastSegment;

        return $this->controllerNamespace . '\\' . implode('\\', $namespaceSegments);
    }

    /**
     * 匹配动作名和参数（自动路由核心）.
     * @return null|array [method, params]
     */
    private function matchActionAndParams(string $controllerClass, array $segments, string $requestMethod): ?array
    {
        $availableMethods = get_class_methods($controllerClass);
        $paramSegments    = [];

        // 1. 无动作段：使用RESTful默认动作（如GET → index/show，POST → store）
        if (empty($segments)) {
            $defaultAction = $this->getRestDefaultAction($requestMethod);
            if (in_array($defaultAction, $availableMethods)) {
                return [
                    'method' => $defaultAction,
                    'params' => [],
                ];
            }
            return null;
        }

        // 2. 有动作段：从短到长尝试匹配动作名（支持多段动作名，如 /user/profile/edit → profileEdit）
        for ($actionSegmentLength = 1; $actionSegmentLength <= count($segments); ++$actionSegmentLength) {
            $actionSegments = array_slice($segments, 0, $actionSegmentLength);
            $paramSegments  = array_slice($segments, $actionSegmentLength);

            // 构建动作名（多段转为驼峰式，如 [show, profile] → showProfile）
            $actionMethod = $this->buildActionName($actionSegments);

            // 动作不存在，跳过当前长度
            if (! in_array($actionMethod, $availableMethods)) {
                continue;
            }

            // 3. 提取参数（单参数默认映射id，多参数映射param1/param2...）
            $params = $this->extractParamsFromSegments($paramSegments);

            return [
                'method' => $actionMethod,
                'params' => $params,
            ];
        }

        // 4. 无匹配动作：尝试REST默认动作（如 /user/1 → GET → show(id=1)）
        $defaultAction = $this->getRestDefaultAction($requestMethod);
        if (in_array($defaultAction, $availableMethods)) {
            $params = $this->extractParamsFromSegments($segments);
            return [
                'method' => $defaultAction,
                'params' => $params,
            ];
        }

        return null;
    }

    /**
     * 构建动作名（多段转为驼峰式）.
     */
    private function buildActionName(array $segments): string
    {
        if (empty($segments)) {
            return 'index';
        }
        // 首字母小写，后续段首字母大写（如 [user, list] → userList）
        return lcfirst(implode('', array_map('ucfirst', $segments)));
    }

    /**
     * 从路径段提取参数.
     */
    private function extractParamsFromSegments(array $segments): array
    {
        $params       = [];
        $segmentCount = count($segments);

        // 单参数：默认映射为id（如 /user/1 → id=1）
        if ($segmentCount === 1) {
            $params['id'] = $segments[0];
        }
        // 多参数：按顺序映射为param1/param2...（如 /user/search/1/admin → param1=1, param2=admin）
        elseif ($segmentCount > 1) {
            foreach ($segments as $key => $value) {
                $params['param' . ($key + 1)] = $value;
            }
        }

        return $params;
    }

    /**
     * 根据HTTP方法获取RESTful默认动作.
     */
    private function getRestDefaultAction(string $method): string
    {
        return match (strtoupper($method)) {
            'GET'    => 'index',
            'POST'   => 'store',
            'PUT'    => 'update',
            'DELETE' => 'destroy',
            default  => 'index'
        };
    }

    /**
     * 请求预处理：中间件+URL后缀处理.
     */
    private function preprocessRequest(Request $request): void
    {
        // 处理PUT/DELETE请求（通过表单隐藏字段_method）
        // $this->applyMethodOverrideMiddleware($request);
        // 去除URL的.html后缀（如 /user/1.html → /user/1）
        $this->removeHtmlSuffix($request);
    }

    /**
     * 应用MethodOverride中间件.
     */
    private function applyMethodOverrideMiddleware(Request $request): void
    {
        // $methodOverride = new MiddlewareMethodOverride();
        $methodOverride = new MiddlewareDispatcher($this->container);
        $methodOverride->dispatch($request, function ($req) {
            return new Response();
        });
    }

    /**
     * 去除URL的.html后缀
     */
    private function removeHtmlSuffix(Request $request): void
    {
        $originalPath = $request->getPathInfo();
        $cleanPath    = preg_replace('/\.html$/', '', $originalPath);

        // 后缀存在时，更新请求的URI
        if ($cleanPath !== $originalPath) {
            $newUri = str_replace($originalPath, $cleanPath, $request->getUri());
            $request->server->set('REQUEST_URI', $newUri);
            // 重新初始化请求（确保路径生效）
            $request->initialize(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
        }
    }
}
