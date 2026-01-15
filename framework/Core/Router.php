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

use Framework\Attributes\MiddlewareProviderInterface; 
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
    private RouteCollection $allRoutes;
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

    public function match(Request $request): ?array
    {
        $this->preprocessRequest($request);
        $path    = $request->getPathInfo();
        $context = new RequestContext();
        $context->fromRequest($request);

        // 彩蛋逻辑略...

        // 1. 尝试匹配手动/注解路由
        $routeInfo = $this->matchManualAndAnnotationRoutes($path, $context, $request);
        if ($routeInfo) {
            return $routeInfo;
        }

        // 2. 尝试自动解析路由 (Fallback)
        $autoRoute = $this->matchAutoRoute($path, $request);
        if ($autoRoute) {
            return $autoRoute;
        }

        return null;
    }

    private function matchManualAndAnnotationRoutes(string $path, RequestContext $context, Request $request): ?array
    {
        try {
            $matcher = new UrlMatcher($this->allRoutes, $context);
            $parameters = $matcher->match($path);

            $request->attributes->add($parameters);

            if (!isset($parameters['_controller'])) {
                return null;
            }

            if (str_contains($parameters['_controller'], '::')) {
                [$controllerClass, $actionMethod] = explode('::', $parameters['_controller'], 2);
            } else {
                $controllerClass = $parameters['_controller'];
                $actionMethod = '__invoke';
            }

            return [
                'controller' => $controllerClass,
                'method'     => $actionMethod,
                'params'     => $parameters,
                'middleware' => $parameters['_middleware'] ?? [],
            ];
        } catch (MethodNotAllowedException | ResourceNotFoundException $e) {
            return null;
        }
    }

    private function matchAutoRoute(string $path, Request $request): ?array
    {
        $path = rtrim($path, '/');
        $pathSegments  = array_values(array_filter(explode('/', $path)));
        $requestMethod = $request->getMethod();

        // 根路径 -> Home::index
        if (empty($pathSegments)) {
            $homeController = "{$this->controllerNamespace}\\Home";
            if (class_exists($homeController) && method_exists($homeController, 'index')) {
                // 🔥 这里也要调用反射扫描
                return $this->finalizeAutoRoute($request, $homeController, 'index', []);
            }
            // 兼容旧命名 HomeController
            $homeControllerOld = "{$this->controllerNamespace}\\HomeController";
            if (class_exists($homeControllerOld) && method_exists($homeControllerOld, 'index')) {
                 return $this->finalizeAutoRoute($request, $homeControllerOld, 'index', []);
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
     * 🔥 核心修复：在自动路由确认后，现场扫描注解
     */
    private function finalizeAutoRoute(Request $request, string $controller, string $method, array $params): array
    {
        // 1. 进行反射扫描
        $scannedData = $this->scanForMiddlewareAndAttributes($controller, $method);
		
        // 2. 构造标准的 attributes
        $attributes = array_merge($params, [
            '_controller' => $controller . '::' . $method,
            '_route'      => 'auto_route_' . md5($controller . $method),
            
            // 🔥 这里不再是空数组，而是填入扫描到的结果
            '_middleware' => $scannedData['middleware'],
            '_auth'       => $scannedData['auth'],
            '_roles'      => $scannedData['roles'],
            '_attributes' => $scannedData['attributesMap'], // 透传 Auth 对象
        ]);

        $request->attributes->add($attributes);

        return [
            'controller' => $controller,
            'method'     => $method,
            'params'     => $params,
            'middleware' => $scannedData['middleware'],
        ];
    }

    /**
     * 🔥 新增方法：反射扫描控制器和方法上的注解
     * 用于在“自动路由”模式下动态提取 #[Auth] 等信息
     */
    private function scanForMiddlewareAndAttributes(string $controller, string $method): array
    {
        $middleware = [];
        $attributesMap = [];
        $auth = null;
        $roles = [];

        try {
            $refClass = new \ReflectionClass($controller);
            $refMethod = $refClass->getMethod($method);

            // 合并类级和方法级的 Attributes
            $allAttributes = array_merge($refClass->getAttributes(), $refMethod->getAttributes());

            foreach ($allAttributes as $attr) {
                // 排除路由注解 (自动路由模式下不需要处理它们)
                if (in_array($attr->getName(), [
                    'Framework\Attributes\Route', 
                    'Framework\Attributes\Routes\Prefix', 
                    'Framework\Attributes\Routes\BaseMapping'
                ])) {
                    continue;
                }

                try {
                    $inst = $attr->newInstance();
                    $attributesMap[$attr->getName()] = $inst;

                    // 1. 提取中间件 (检查接口)
                    if ($inst instanceof MiddlewareProviderInterface) {
                        $provided = $inst->getMiddleware();
                        $candidates = is_array($provided) ? $provided : [$provided];
                        foreach ($candidates as $mid) {
                            if (is_string($mid) && !empty($mid)) {
                                $middleware[] = $mid;
                            }
                        }
                    }

                    // 2. 兼容 Auth/Roles 数据提取 (如果需要兼容旧逻辑)
                    // 如果你的 Auth 注解有 public $required 属性
                    if ($inst instanceof \Framework\Attributes\Auth) {
                        $auth = $inst->required ?? true;
                    }
                    if ($inst instanceof \Framework\Attributes\Role) {
                        $roles = array_merge($roles, $inst->roles ?? []);
                    }

                } catch (\Throwable $e) {
                    continue;
                }
            }
        } catch (\ReflectionException $e) {
            // 方法不存在等情况，忽略
        }

        return [
            'middleware'    => array_values(array_unique($middleware)),
            'attributesMap' => $attributesMap,
            'auth'          => $auth,
            'roles'         => array_values(array_unique($roles)),
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
