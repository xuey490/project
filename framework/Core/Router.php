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
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

/**
 * 路由核心处理类
 * 负责匹配请求路径到对应的控制器和方法，支持手动路由、注解路由和自动路由
 */
class Router
{
    // 常量定义 - 替代魔法值
    private const AUTO_ROUTE_PREFIX = 'auto_route_';
    private const DEFAULT_CONTROLLER_NAMESPACE = 'App\Controllers';
    private const ALLOWED_ATTRIBUTES = [
        'Framework\Attributes\Auth',
        'Framework\Attributes\Role',
        'Framework\Attributes\Middleware'
    ];
    private const PARAM_SINGLE_KEY = 'id';
    private const PARAM_MULTI_PREFIX = 'param';
    
    // 缓存属性
    private array $reflectionCache = [];      // 反射结果缓存
    private array $classMethodCache = [];     // 类方法有效性缓存
    
    // 核心属性
    private RouteCollection $allRoutes;
    private string $controllerNamespace;
    private ContainerInterface $container;

    /**
     * 构造函数
     *
     * @param RouteCollection $allRoutes 路由集合
     * @param ContainerInterface $container 容器实例
     * @param string $controllerNamespace 控制器命名空间
     */
    public function __construct(
        RouteCollection $allRoutes,
        ContainerInterface $container,
        string $controllerNamespace = self::DEFAULT_CONTROLLER_NAMESPACE
    ) {
        $this->allRoutes           = $allRoutes;
        $this->container           = $container;
        // 标准化命名空间，确保末尾无反斜杠
        $this->controllerNamespace = rtrim($controllerNamespace, '\\');
    }

    /**
     * 匹配请求到对应的路由信息
     *
     * @param Request $request 请求对象
     * @return array|null 路由信息数组 [controller, method, params, middleware] 或 null
     */
    public function match(Request $request): ?array
    {
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

    /**
     * 匹配手动配置和注解定义的路由
     *
     * @param string $path 请求路径
     * @param RequestContext $context 请求上下文
     * @param Request $request 请求对象
     * @return array|null 路由信息数组或 null
     */
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

            // 验证控制器方法有效性
            if (!$this->isControllerMethodValid($controllerClass, $actionMethod)) {
                return null;
            }

            return [
                'controller' => $controllerClass,
                'method'     => $actionMethod,
                'params'     => $parameters,
                'middleware' => $parameters['_middleware'] ?? [],
            ];
        } catch (MethodNotAllowedException | ResourceNotFoundException $e) {
            $this->logException($e, "Route matching failed for path: {$path}");
            return null;
        }
    }

    /**
     * 匹配自动路由（基于路径自动解析控制器和方法）
     *
     * @param string $path 请求路径
     * @param Request $request 请求对象
     * @return array|null 路由信息数组或 null
     */
    private function matchAutoRoute(string $path, Request $request): ?array
    {
        $path = rtrim($path, '/');
        $pathSegments  = array_values(array_filter(explode('/', $path)));
        $requestMethod = $request->getMethod();

        // 根路径 -> Home::index
        if (empty($pathSegments)) {
            $homeController = "{$this->controllerNamespace}\\Home";
            if ($this->isControllerMethodValid($homeController, 'index')) {
                return $this->finalizeAutoRoute($request, $homeController, 'index', []);
            }
            // 兼容旧命名 HomeController
            $homeControllerOld = "{$this->controllerNamespace}\\HomeController";
            if ($this->isControllerMethodValid($homeControllerOld, 'index')) {
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
     * 完成自动路由的最终处理（反射扫描注解、构造路由属性）
     *
     * @param Request $request 请求对象
     * @param string $controller 控制器类名
     * @param string $method 方法名
     * @param array $params 参数数组
     * @return array 标准化的路由信息数组
     */
    private function finalizeAutoRoute(Request $request, string $controller, string $method, array $params): array
    {
        // 1. 进行反射扫描（带缓存）
        $scannedData = $this->scanForMiddlewareAndAttributes($controller, $method);

        // 2. 仅透传安全的注解实例
        $safeAttributes = [];
        foreach ($scannedData['attributesMap'] as $attrName => $attrInst) {
            if (in_array($attrName, self::ALLOWED_ATTRIBUTES)) {
                $safeAttributes[$attrName] = $attrInst;
            }
        }

        // 3. 构造标准的 attributes
        $attributes = array_merge($params, [
            '_controller' => $controller . '::' . $method,
            '_route'      => self::AUTO_ROUTE_PREFIX . md5($controller . $method),
            '_middleware' => $scannedData['middleware'],
            '_auth'       => $scannedData['auth'],
            '_roles'      => $scannedData['roles'],
            '_attributes' => $safeAttributes, // 仅透传安全的注解
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
     * 反射扫描控制器和方法上的注解，提取中间件、权限等信息（带缓存）
     *
     * @param string $controller 控制器完整类名
     * @param string $method 方法名
     * @return array {
     *     @var array $middleware 中间件列表
     *     @var array $attributesMap 安全的注解实例映射
     *     @var bool|null $auth 是否需要认证
     *     @var array $roles 所需角色列表
     * }
     */
    private function scanForMiddlewareAndAttributes(string $controller, string $method): array
    {
        // 生成缓存键
        $cacheKey = md5($controller . '::' . $method);
        if (isset($this->reflectionCache[$cacheKey])) {
            return $this->reflectionCache[$cacheKey];
        }

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

                    // 2. 兼容 Auth/Roles 数据提取
                    if ($inst instanceof \Framework\Attributes\Auth) {
                        $auth = $inst->required ?? true;
                    }
                    if ($inst instanceof \Framework\Attributes\Role) {
                        $roles = array_merge($roles, $inst->roles ?? []);
                    }

                } catch (\Throwable $e) {
                    $this->logException($e, "Annotation instantiation failed for {$attr->getName()}");
                    continue;
                }
            }
        } catch (\ReflectionException $e) {
            $this->logException($e, "Reflection failed for {$controller}::{$method}");
        }

        $result = [
            'middleware'    => array_values(array_unique($middleware)),
            'attributesMap' => $attributesMap,
            'auth'          => $auth,
            'roles'         => array_values(array_unique($roles)),
        ];
        
        // 存入缓存
        $this->reflectionCache[$cacheKey] = $result;
        return $result;
    }
	
    /**
     * 构建控制器完整类名（支持多级命名空间，过滤危险字符）
     * 例：[api, v2, user] → App\Controllers\Api\V2\UserController
     *
     * @param array $segments 路径段数组
     * @return string 控制器完整类名
     */
    private function buildControllerClassName(array $segments): string
    {
        // 过滤路径段中的危险字符（如 ..、/、\ 等），仅保留字母数字和下划线 $segment = preg_replace('/[^a-zA-Z0-9_]/', '', $segment);
        $segments = array_map(function ($segment) {
            return preg_replace('/[^a-zA-Z0-9_]/', '', $segment);
        }, $segments);
        $segments = array_filter($segments); // 移除空段

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
		
		$className = $this->controllerNamespace . '\\' . ucwords(implode('\\', $segments));
		if (!class_exists($className)) {
			return '';
		}
		return $className;
        #return $this->controllerNamespace . '\\' . implode('\\', $namespaceSegments);
    }

    /**
     * 匹配动作名和参数（自动路由核心）
     *
     * @param string $controllerClass 控制器类名
     * @param array $segments 路径段
     * @param string $requestMethod 请求方法
     * @return array|null [method, params] 或 null
     */
    private function matchActionAndParams(string $controllerClass, array $segments, string $requestMethod): ?array
    {
        // 获取控制器的有效公共方法（排除魔术方法和构造方法）
        if (!$this->getValidControllerMethods($controllerClass)) {
            return null;
        }
        $availableMethods = $this->getValidControllerMethods($controllerClass);
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

        // 2. 有动作段：从短到长尝试匹配动作名（支持多段动作名）
        for ($actionSegmentLength = 1; $actionSegmentLength <= count($segments); ++$actionSegmentLength) {
            $actionSegments = array_slice($segments, 0, $actionSegmentLength);
            $paramSegments  = array_slice($segments, $actionSegmentLength);

            // 构建动作名（多段转为驼峰式，如 [show, profile] → showProfile）
            $actionMethod = $this->buildActionName($actionSegments);

            // 动作不存在，跳过当前长度
            if (!in_array($actionMethod, $availableMethods)) {
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
	 * 获取控制器的有效公共方法（排除魔术方法和构造方法）
	 *
	 * @param string $controllerClass 控制器类名
	 * @return array|null 有效方法名数组或 null
	 */
	private function getValidControllerMethods(string $controllerClass): ?array
	{
		$cacheKey = md5("valid_methods_{$controllerClass}");
		if (isset($this->classMethodCache[$cacheKey])) {
			return $this->classMethodCache[$cacheKey];
		}

		if (!class_exists($controllerClass)) {
			$this->classMethodCache[$cacheKey] = null;
			return null;
		}

		$refClass = new \ReflectionClass($controllerClass);
		$methods = array_filter(
			$refClass->getMethods(\ReflectionMethod::IS_PUBLIC),
			function (\ReflectionMethod $method) {
				$methodName = $method->getName();
				// 排除魔术方法（以 __ 开头）和构造方法
				return !(str_starts_with($methodName, '__') && $methodName !== '__construct') 
					   && $methodName !== '__construct';
			}
		);

		$methodNames = array_map(fn(\ReflectionMethod $m) => $m->getName(), $methods);
		$this->classMethodCache[$cacheKey] = $methodNames;

		return $methodNames;
	}

    /**
     * 构建动作名（多段转为驼峰式）
     *
     * @param array $segments 路径段数组
     * @return string 驼峰式动作名
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
     * 从路径段提取参数
     *
     * @param array $segments 路径段数组
     * @return array 参数数组
     */
    private function extractParamsFromSegments(array $segments): array
    {
        $params       = [];
        $segmentCount = count($segments);

        // 单参数：默认映射为id（如 /user/1 → id=1）
        if ($segmentCount === 1) {
            $params[self::PARAM_SINGLE_KEY] = $segments[0];
        }
        // 多参数：按顺序映射为param1/param2...（如 /user/search/1/admin → param1=1, param2=admin）
        elseif ($segmentCount > 1) {
            foreach ($segments as $key => $value) {
                $params[self::PARAM_MULTI_PREFIX . ($key + 1)] = $value;
            }
        }

        return $params;
    }

    /**
     * 根据HTTP方法获取RESTful默认动作
     *
     * @param string $method HTTP请求方法
     * @return string 对应的默认动作名
     */
    private function getRestDefaultAction(string $method): string
    {
        return match (strtoupper($method)) {
            'GET'    => 'index',
            'POST'   => 'store',
            'PUT'    => 'update',
            'PATCH'  => 'update', // 支持PATCH方法（HTTP标准更新方法）
            'DELETE' => 'destroy',
            default  => 'index'
        };
    }

    /**
     * 请求预处理：中间件+URL后缀处理
     *
     * @param Request $request 请求对象
     */
    private function preprocessRequest(Request $request): void
    {
        // 处理PUT/DELETE请求（通过表单隐藏字段_method）
        // $this->applyMethodOverrideMiddleware($request);
        // 去除URL的.html后缀（如 /user/1.html → /user/1）
        $this->removeHtmlSuffix($request);
    }

    /**
     * 应用MethodOverride中间件
     *
     * @param Request $request 请求对象
     */
    private function applyMethodOverrideMiddleware(Request $request): void
    {
        $methodOverride = new MiddlewareDispatcher($this->container);
        $methodOverride->dispatch($request, function ($req) {
            return new \Symfony\Component\HttpFoundation\Response();
        });
    }

    /**
     * 安全移除URL的.html后缀
     *
     * @param Request $request 请求对象
     */
    private function removeHtmlSuffix(Request $request): void
    {
        $originalPath = $request->getPathInfo();
        // 仅匹配末尾的 .html 后缀，避免误处理含多个点的路径
        if (str_ends_with($originalPath, '.html')) {
            $cleanPath = substr($originalPath, 0, -5); // 移除末尾的 .html
            // 安全验证：确保新路径仅包含合法字符
            if (preg_match('/^[a-zA-Z0-9_\/-]+$/', $cleanPath)) {
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

    /**
     * 验证控制器和方法是否有效
     *
     * @param string $controller 控制器类名
     * @param string $method 方法名
     * @return bool 有效返回true，否则返回false
     */
    private function isControllerMethodValid(string $controller, string $method): bool
    {
        $cacheKey = md5("valid_{$controller}::{$method}");
        if (isset($this->classMethodCache[$cacheKey])) {
            return $this->classMethodCache[$cacheKey];
        }

        $isValid = false;
        if (class_exists($controller)) {
            $validMethods = $this->getValidControllerMethods($controller);
            $isValid = $validMethods && in_array($method, $validMethods);
        }

        $this->classMethodCache[$cacheKey] = $isValid;
        return $isValid;
    }

    /**
     * 记录异常日志
     * 可替换为框架的日志组件（如 Monolog）
     *
     * @param \Throwable $e 异常对象
     * @param string $context 上下文描述
     */
    private function logException(\Throwable $e, string $context): void
    {
        error_log(sprintf(
            '[Router] %s: %s (File: %s, Line: %d, Trace: %s)',
            $context,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
    }
}