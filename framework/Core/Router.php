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
use Psr\Container\ContainerInterface; // 推荐使用 PSR 接口
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Router
{
    /**
     * 所有路由集合（手动路由 + 注解路由）.
     */
    private RouteCollection $allRoutes;

    /**
     * 控制器基础命名空间.
     */
    private string $controllerNamespace;

    private ContainerInterface $container;

    public function __construct(
        RouteCollection $allRoutes,
        ContainerInterface $container,
        string $controllerNamespace = 'App\Controllers'
    ) {
        $this->allRoutes           = $allRoutes;
        $this->container           = $container;
        $this->controllerNamespace = $controllerNamespace;
    }

    /**
     * 核心路由匹配方法
     * 优先级：手动路由 > 注解路由 > 自动解析路由.
     * @return null|array 路由元数据：[controller, method, params, middleware]
     */
    public function match(Request $request): ?array
    {
        // 1. 预处理：去除URL的.html后缀
        $this->preprocessRequest($request);

        // 2. 准备上下文
        $path    = $request->getPathInfo();
        $context = new RequestContext();
        $context->fromRequest($request);

        // 🔥 彩蛋逻辑保持不变
        if (EasterEgg::isTriggeredVersion($request)) {
            return EasterEgg::getRouteMarker();
        }
        if (EasterEgg::isTriggeredTeam($request)) {
            return EasterEgg::getTeamRouteMarker();
        }

        // 3. 策略1：匹配手动路由 + 注解路由
        // 注意：这里传递 $request 是为了在内部将参数注入到 request attributes 中
        $routeInfo = $this->matchManualAndAnnotationRoutes($path, $context, $request);
        if ($routeInfo) {
            return $routeInfo;
        }

        // 4. 策略2：匹配自动解析路由（最低优先级）
        $autoRoute = $this->matchAutoRoute($path, $request);
        if ($autoRoute) {
            return $autoRoute;
        }

        return null;
    }

    /**
     * 匹配路由并注入 Request 属性.
     */
    private function matchManualAndAnnotationRoutes(string $path, RequestContext $context, Request $request): ?array
    {
        try {
            $matcher = new UrlMatcher($this->allRoutes, $context);
            
            // 匹配结果包含：_route, _controller, 以及 defaults 中的 _middleware, _auth, _roles 等
            $parameters = $matcher->match($path);

            // 🔥 【核心修复】将匹配到的所有参数（路由参数+Defaults）注入到 Request 中
            // 这样 MiddlewareDispatcher 才能通过 $request->attributes->get('_middleware') 拿到数据
            $request->attributes->add($parameters);

            if (!isset($parameters['_controller'])) {
                return null;
            }

            // 解析控制器和方法
            // 格式可能是 "Class::Method" 或 "Class" (__invoke)
            if (str_contains($parameters['_controller'], '::')) {
                [$controllerClass, $actionMethod] = explode('::', $parameters['_controller'], 2);
            } else {
                $controllerClass = $parameters['_controller'];
                $actionMethod = '__invoke';
            }

            // 清理掉不需要返回给 Kernel 的内部参数，但 Request attributes 中保留
            // $paramsToReturn = $parameters;
            // unset($paramsToReturn['_controller'], $paramsToReturn['_route'], $paramsToReturn['_middleware']);

            return [
                'controller' => $controllerClass,
                'method'     => $actionMethod,
                'params'     => $parameters, // 包含 id, slug 等路由参数
                'middleware' => $parameters['_middleware'] ?? [],
            ];

        } catch (MethodNotAllowedException | ResourceNotFoundException $e) {
            return null;
        }
    }

    /**
     * 匹配自动解析路由.
     */
    private function matchAutoRoute(string $path, Request $request): ?array
    {
        // ... (原有逻辑保持不变)
        $path = rtrim($path, '/');
        $pathSegments  = array_values(array_filter(explode('/', $path)));
        $requestMethod = $request->getMethod();

        // 根路径
        if (empty($pathSegments)) {
            $homeController = "{$this->controllerNamespace}\\Home";
            if (class_exists($homeController) && method_exists($homeController, 'index')) {
                return $this->finalizeAutoRoute($request, $homeController, 'index', []);
            }
            return null;
        }

        // 多级控制器匹配
        for ($controllerSegmentLength = count($pathSegments); $controllerSegmentLength >= 1; --$controllerSegmentLength) {
            $controllerSegments = array_slice($pathSegments, 0, $controllerSegmentLength);
            $controllerClass    = $this->buildControllerClassName($controllerSegments);

            if (!class_exists($controllerClass)) {
                continue;
            }

            $actionAndParamSegments = array_slice($pathSegments, $controllerSegmentLength);
            $routeInfo              = $this->matchActionAndParams($controllerClass, $actionAndParamSegments, $requestMethod);

            if ($routeInfo) {
                return $this->finalizeAutoRoute(
                    $request, 
                    $controllerClass, 
                    $routeInfo['method'], 
                    $routeInfo['params']
                );
            }
        }

        return null;
    }

    /**
     * 统一处理自动路由的返回，并注入 Request.
     */
    private function finalizeAutoRoute(Request $request, string $controller, string $method, array $params): array
    {
        // 构造标准的 attributes
        $attributes = array_merge($params, [
            '_controller' => $controller . '::' . $method,
            '_route'      => 'auto_route_' . md5($controller . $method), // 虚拟路由名
            // 自动路由默认没有中间件和权限设置，给予默认空值，防止中间件报错
            '_middleware' => [],
            '_auth'       => false,
            '_roles'      => [],
        ]);

        // 🔥 【核心修复】注入到 Request
        $request->attributes->add($attributes);

        return [
            'controller' => $controller,
            'method'     => $method,
            'params'     => $params,
            'middleware' => [],
        ];
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
