<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-27
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Framework\Attributes\Route;
use Framework\Attributes\Routes\BaseMapping;
use Framework\Attributes\Routes\Prefix;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * AttributeRouteLoader：
 * - 支持 原生 Route
 * - 支持 Prefix（类级）与 BaseMapping（方法级：GetMapping/PostMapping...）
 * - 新增：支持 DocBlock 风格注解解析
 */
class AttributeRouteLoader
{
    private string $controllerDir;

    private string $controllerNamespace;

    public function __construct(string $controllerDir, string $controllerNamespace)
    {
        $this->controllerDir       = rtrim($controllerDir, '/');
        $this->controllerNamespace = rtrim($controllerNamespace, '\\');
    }

    public function loadRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $controllerFiles = $this->scanDirectory($this->controllerDir);

        foreach ($controllerFiles as $file) {
            $className = $this->convertFileToClass($file);
            if (! class_exists($className)) {
                continue;
            }

            $refClass = new \ReflectionClass($className);
            if ($refClass->isAbstract()) {
                continue;
            }

            // ==== === 类级注解：支持 Route、Prefix 和 DocBlock ====
            $classPrefix     = '';
            $classGroup      = null;
            $classMiddleware = [];
            $classAuth       = null;
            $classRoles      = [];

            // 优先读取 Prefix（Spring 风格）
            $prefixAttrs = $refClass->getAttributes(Prefix::class);
            if (! empty($prefixAttrs)) {
                $prefixInst      = $prefixAttrs[0]->newInstance();
                $classPrefix     = $prefixInst->prefix     ?? '';
                $classMiddleware = $prefixInst->middleware ?? [];
                $classAuth       = $prefixInst->auth       ?? null;
                $classRoles      = $prefixInst->roles      ?? [];
                $classGroup      = null;
            }

            // 兼容你已有的 Route 类级注解（会覆盖 Prefix 的相应值）
            $classRouteAttrs = $refClass->getAttributes(Route::class);
            if (! empty($classRouteAttrs)) {
                $classRoute      = $classRouteAttrs[0]->newInstance();
                $classPrefix     = $classRoute->prefix     ?? $classPrefix;
                $classGroup      = $classRoute->group      ?? $classGroup;
                $classMiddleware = $classRoute->middleware ?? $classMiddleware;
                $classAuth       = $classRoute->auth       ?? $classAuth;
                $classRoles      = $classRoute->roles      ?? $classRoles;
            }

            // 修复点1：将 getDocComment() 返回的 false 转为 null
            $classDocComment = $refClass->getDocComment();
            $classDocBlockData = $this->parseDocBlockAnnotations($classDocComment === false ? null : $classDocComment);
            $classPrefix = $classDocBlockData['prefix'] ?? $classPrefix;
            $classGroup = $classDocBlockData['group'] ?? $classGroup;
            $classMiddleware = array_merge($classMiddleware, $classDocBlockData['middleware'] ?? []);
            $classAuth = $classDocBlockData['auth'] ?? $classAuth;
            $classRoles = array_merge($classRoles, $classDocBlockData['roles'] ?? []);

            // ==== === 方法级注解：支持 Route、BaseMapping 和 DocBlock ====
            foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $methodAttrs = $method->getAttributes();

                // 修复点2：统一处理方法级 DocComment，将 false 转为 null
                $methodDocComment = $method->getDocComment();
                $docBlockData = $this->parseDocBlockAnnotations($methodDocComment === false ? null : $methodDocComment);

                // 如果没有任何属性注解，回退到 auto route 逻辑（保持原样）
                if (empty($methodAttrs) && empty($docBlockData)) {
                    $autoPath = '/' . strtolower(str_replace('Controller', '', $refClass->getShortName()))
                        . '/' . $method->getName();

                    $route = new SymfonyRoute(
                        $autoPath,
                        defaults: [
                            '_controller' => "{$className}::{$method->getName()}",
                            '_group'      => $classGroup,
                            '_middleware' => $classMiddleware,
                            '_auth'       => $classAuth,
                            '_roles'      => $classRoles,
                        ],
                        methods: ['GET']
                    );

                    $autoName = strtolower(str_replace('\\', '_', $className)) . '_' . $method->getName();
                    $routeCollection->add($autoName, $route);
                    continue;
                }

                // 如果只有 DocBlock 注解
                if (empty($methodAttrs) && !empty($docBlockData)) {
                    $this->addSymfonyRouteFromDocBlock(
                        $routeCollection,
                        $className,
                        $method->getName(),
                        $classPrefix,
                        $classMiddleware,
                        $classAuth,
                        $classRoles,
                        $docBlockData
                    );
                    continue;
                }

                // 遍历所有属性注解（允许重复注解）
                foreach ($methodAttrs as $attr) {
                    $attrClass = $attr->getName();
                    $attrInst  = $attr->newInstance();

                    // ---- 情况A：方法使用你已有的 Route Attribute ----
                    if ($attrClass === Route::class || is_a($attrInst, Route::class)) {
                        $routeAttr = $attrInst;
                        
                        // 合并 DocBlock 数据到 Route 属性
                        $mergedRouteAttr = $this->mergeWithDocBlockData($routeAttr, $docBlockData);
                        
                        // 直接使用你的 Route 对象转换为 Symfony\Route（和之前一样）
                        $this->addSymfonyRouteFromRouteAttr(
                            $routeCollection,
                            $className,
                            $method->getName(),
                            $classPrefix,
                            $classMiddleware,
                            $classAuth,
                            $classRoles,
                            $mergedRouteAttr
                        );
                        continue;
                    }

                    // ---- 情况B：方法使用 BaseMapping 及其子类（GetMapping/PostMapping 等） ----
                    if (is_object($attrInst) && is_subclass_of($attrInst::class, BaseMapping::class)
                        || $attrInst instanceof BaseMapping) {
                        // 将 BaseMapping 规范化为一个临时 Route-like 对象（匿名 stdClass）
                        $mapping = $attrInst;

                        // 修复点3：显式添加 group 属性（初始值为 null），避免未定义属性报错
                        $routeLike = (object) [
                            'path'         => $mapping->path       ?? '',
                            'methods'      => $mapping->methods    ?? (property_exists($mapping, 'methods') ? $mapping->methods : []),
                            'middleware'   => $mapping->middleware ?? [],
                            'auth'         => $mapping->auth       ?? null,
                            'roles'        => $mapping->roles      ?? [],
                            'name'         => null,
                            'group'        => null, // 新增：显式定义 group 属性
                            'defaults'     => [],
                            'requirements' => [],
                            'schemes'      => [],
                            'host'         => null,
                        ];

                        // 合并 DocBlock 数据
                        $routeLike = $this->mergeRouteLikeWithDocBlock($routeLike, $docBlockData);

                        $this->addSymfonyRouteFromRouteLike(
                            $routeCollection,
                            $className,
                            $method->getName(),
                            $classPrefix,
                            $classMiddleware,
                            $classAuth,
                            $classRoles,
                            $routeLike
                        );

                        continue;
                    }

                    // 其他自定义 attribute：如果其有 path/methods/middleware 等属性，也可以按需兼容（可选扩展点）
                }
                
                // 如果同时有属性注解和 DocBlock 注解，上面的循环已经处理了合并
                // 如果只有 DocBlock 注解，前面的条件已经处理了
            }
        }

        return $routeCollection;
    }

    /**
     * 从 DocBlock 解析注解数据
     * @param string|null $docComment 文档注释（确保仅为 string 或 null）
     */
    private function parseDocBlockAnnotations(?string $docComment): array
    {
        // 修复点3：简化校验逻辑，只判断 null/空字符串
        if ($docComment === null || trim($docComment) === '') {
            return [];
        }

        $annotations = [];
        
        // 匹配 @method 注解
        if (preg_match_all('/@method\s+([^\r\n]+)/i', $docComment, $matches)) {
            $methods = [];
            foreach ($matches[1] as $match) {
                $method = trim($match);
                if (!empty($method)) {
                    $methods[] = strtoupper($method); // 转换为大写
                }
            }
            if (!empty($methods)) {
                $annotations['methods'] = $methods;
            }
        }

        // 匹配 @auth 注解
        if (preg_match('/@auth\s+(true|false)/i', $docComment, $matches)) {
            $annotations['auth'] = $matches[1] === 'true';
        }

        // 匹配 @role 注解（支持逗号分隔的多个角色）
        if (preg_match('/@role\s+([^\r\n]+)/i', $docComment, $matches)) {
            $roles = array_map('trim', explode(',', trim($matches[1])));
            $annotations['roles'] = $roles;
        }

        // 匹配 @middleware 注解（支持逗号分隔的多个中间件）
        if (preg_match('/@middleware\s+([^\r\n]+)/i', $docComment, $matches)) {
            $middlewares = array_map('trim', explode(',', trim($matches[1])));
            $annotations['middleware'] = $middlewares;
        }

        // 匹配 @prefix 注解
        if (preg_match('/@prefix\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['prefix'] = trim($matches[1]);
        }

        // 匹配 @group 注解
        if (preg_match('/@group\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['group'] = trim($matches[1]);
        }

        // 匹配 @name 注解
        if (preg_match('/@name\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['name'] = trim($matches[1]);
        }

        // 匹配 @path 注解
        if (preg_match('/@path\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['path'] = trim($matches[1]);
        }

        return $annotations;
    }

    /**
     * 将 DocBlock 数据合并到 Route 对象
     */
    private function mergeWithDocBlockData(Route $routeAttr, array $docBlockData): Route
    {
        // 创建一个新的 Route 对象，合并 DocBlock 数据
        $newRoute = new Route(
            path: $docBlockData['path'] ?? $routeAttr->path,
            methods: !empty($docBlockData['methods']) ? $docBlockData['methods'] : $routeAttr->methods,
            name: $docBlockData['name'] ?? $routeAttr->name,
            defaults: $routeAttr->defaults, // defaults 不从 DocBlock 合并
            requirements: $routeAttr->requirements, // requirements 不从 DocBlock 合并
            schemes: $routeAttr->schemes, // schemes 不从 DocBlock 合并
            host: $routeAttr->host, // host 不从 DocBlock 合并
            prefix: $docBlockData['prefix'] ?? $routeAttr->prefix,
            group: $docBlockData['group'] ?? $routeAttr->group,
            middleware: array_merge($routeAttr->middleware, $docBlockData['middleware'] ?? []),
            auth: $docBlockData['auth'] ?? $routeAttr->auth,
            roles: array_merge($routeAttr->roles, $docBlockData['roles'] ?? [])
        );

        return $newRoute;
    }

    /**
     * 将 DocBlock 数据合并到 Route-like 对象
     */
    private function mergeRouteLikeWithDocBlock(object $routeLike, array $docBlockData): object
    {
        // 修复点4：确保所有属性访问都有兜底，避免未定义属性
        //$routeLike->methods = !empty($docBlockData['methods']) ? array_merge($routeLike->methods, $docBlockData['methods']) : $routeLike->methods;
		// 如果 DocBlock 写了 @method POST，通常意味着只想允许 POST。
		if (!empty($docBlockData['methods'])) {
			$routeLike->methods = $docBlockData['methods']; 
		}
		// 确保唯一并大写
		$routeLike->methods = array_unique(array_map('strtoupper', $routeLike->methods));
		
        $routeLike->middleware = array_merge($routeLike->middleware ?? [], $docBlockData['middleware'] ?? []);
        $routeLike->auth = $docBlockData['auth'] ?? ($routeLike->auth ?? null);
        $routeLike->roles = array_merge($routeLike->roles ?? [], $docBlockData['roles'] ?? []);
        $routeLike->path = $docBlockData['path'] ?? ($routeLike->path ?? '');
        $routeLike->name = $docBlockData['name'] ?? ($routeLike->name ?? null);
        $routeLike->group = $docBlockData['group'] ?? ($routeLike->group ?? null); // 现在属性已定义，不会报错

        return $routeLike;
    }

    /**
     * 从 DocBlock 数据创建 Symfony 路由
     */
    private function addSymfonyRouteFromDocBlock(
        RouteCollection $collection,
        string $className,
        string $methodName,
        string $classPrefix,
        array $classMiddleware,
        $classAuth,
        array $classRoles,
        array $docBlockData
    ): void {
        // 使用 DocBlock 中的 path，如果没有则使用默认路径
        $path = $docBlockData['path'] ?? $methodName;
        $prefix = trim($classPrefix, '/');
        $finalPath = '/' . trim($prefix . '/' . trim($path, '/'), '/');

        // 合并中间件
        $mergedMiddleware = array_values(array_unique(array_merge(
            $classMiddleware, 
            $docBlockData['middleware'] ?? []
        )));

        // 认证和角色
        $needAuth = $docBlockData['auth'] ?? $classAuth ?? false;
        $roles = array_merge($classRoles, $docBlockData['roles'] ?? []);

        // HTTP 方法，默认 GET
        $methods = $docBlockData['methods'] ?? ['GET'];

        $sfRoute = new SymfonyRoute(
            path: $finalPath,
            defaults: [
                '_controller' => "{$className}::{$methodName}",
                '_group'      => $docBlockData['group'] ?? null,
                '_middleware' => $mergedMiddleware,
                '_auth'       => $needAuth,
                '_roles'      => $roles,
            ],
            requirements: [],
            options: [],
            host: '',
            schemes: [],
            methods: $methods
        );

        $name = $docBlockData['name'] ?? strtolower(str_replace('\\', '_', $className)) . '_' . $methodName;
        $collection->add($name, $sfRoute);
    }

    /**
     * 将你原始 Route Attribute 转为 Symfony\Route 并加入集合。
     * @param mixed $classAuth
     */
    private function addSymfonyRouteFromRouteAttr(
        RouteCollection $collection,
        string $className,
        string $methodName,
        string $classPrefix,
        array $classMiddleware,
        $classAuth,
        array $classRoles,
        Route $routeAttr
    ): void {
        $prefix    = trim($classPrefix, '/');
        $path      = trim($routeAttr->path ?? '', '/');
        $finalPath = '/' . trim($prefix . '/' . $path, '/');

        $mergedMiddleware = array_values(array_unique(array_merge((array) $classMiddleware, (array) $routeAttr->middleware)));

        $needAuth = $routeAttr->auth  ?? $classAuth ?? false;
        $roles    = $routeAttr->roles ?? $classRoles ?? [];

        $sfRoute = new SymfonyRoute(
            path: $finalPath,
            defaults: array_merge(
                $routeAttr->defaults ?? [],
                [
                    '_controller' => "{$className}::{$methodName}",
                    '_group'      => $routeAttr->group ?? null,
                    '_middleware' => $mergedMiddleware,
                    '_auth'       => $needAuth,
                    '_roles'      => $roles,
                ]
            ),
            requirements: $routeAttr->requirements ?? [],
            options: [],
            host: $routeAttr->host       ?? '',
            schemes: $routeAttr->schemes ?? [],
            methods: $routeAttr->methods ?: ['GET']
        );

        $name = $routeAttr->name ?? strtolower(str_replace('\\', '_', $className)) . '_' . $methodName;
        $collection->add($name, $sfRoute);
    }

    /**
     * 将一个 Route-like（由 BaseMapping 生成） 转为 Symfony\Route 并加入集合。
     * @param mixed $classAuth
     */
    private function addSymfonyRouteFromRouteLike(
        RouteCollection $collection,
        string $className,
        string $methodName,
        string $classPrefix,
        array $classMiddleware,
        $classAuth,
        array $classRoles,
        object $routeLike
    ): void {
        $prefix    = trim($classPrefix, '/');
        $path      = trim($routeLike->path ?? '', '/');
        $finalPath = '/' . trim($prefix . '/' . $path, '/');

        $mergedMiddleware = array_values(array_unique(array_merge((array) $classMiddleware, (array) $routeLike->middleware)));

        $needAuth = $routeLike->auth  ?? $classAuth ?? false;
        $roles    = $routeLike->roles ?? $classRoles ?? [];

        $sfRoute = new SymfonyRoute(
            path: $finalPath,
            defaults: array_merge(
                $routeLike->defaults ?? [],
                [
                    '_controller' => "{$className}::{$methodName}",
                    '_group'      => $routeLike->group ?? null, // 现在属性已定义
                    '_middleware' => $mergedMiddleware,
                    '_auth'       => $needAuth,
                    '_roles'      => $roles,
                ]
            ),
            requirements: $routeLike->requirements ?? [],
            options: [],
            host: $routeLike->host       ?? '',
            schemes: $routeLike->schemes ?? [],
            methods: $routeLike->methods ?: ['GET']
        );

        $name = $routeLike->name ?? strtolower(str_replace('\\', '_', $className)) . '_' . $methodName;
        $collection->add($name, $sfRoute);
    }

    private function scanDirectory(string $dir): array
    {
        $rii   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'php') {
                $files[] = $file->getPathname();
            }
        }
        return $files;
    }

    private function convertFileToClass(string $file): string
    {
        $relative = str_replace($this->controllerDir, '', $file);
        $relative = trim(str_replace(['/', '.php'], ['\\', ''], $relative), '\\');
        return "{$this->controllerNamespace}\\{$relative}";
    }
}