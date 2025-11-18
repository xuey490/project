<?php

declare(strict_types=1);

namespace Framework\Core;

use Framework\Attributes\Route;
use Framework\Attributes\Routes\BaseMapping;
use Framework\Attributes\Routes\Prefix;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use ReflectionClass;
use ReflectionMethod;

/**
 * AttributeRouteLoader：
 * - 支持 原生 Route
 * - 支持 Prefix（类级）与 BaseMapping（方法级：GetMapping/PostMapping...）
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

            $refClass = new ReflectionClass($className);
            if ($refClass->isAbstract()) {
                continue;
            }

            // ==== === 类级注解：支持 Route 或 Prefix ====
            $classPrefix     = '';
            $classGroup      = null;
            $classMiddleware = [];
            $classAuth       = null;
            $classRoles      = [];

            // 优先读取 Prefix（Spring 风格）
            $prefixAttrs = $refClass->getAttributes(Prefix::class);
            if (!empty($prefixAttrs)) {
                $prefixInst     = $prefixAttrs[0]->newInstance();
                $classPrefix     = $prefixInst->prefix ?? '';
                $classMiddleware = $prefixInst->middleware ?? [];
                $classAuth       = $prefixInst->auth ?? null;
                $classRoles      = $prefixInst->roles ?? [];
                $classGroup      = null;
            }

            // 兼容你已有的 Route 类级注解（会覆盖 Prefix 的相应值）
            $classRouteAttrs = $refClass->getAttributes(Route::class);
            if (!empty($classRouteAttrs)) {
                $classRoute = $classRouteAttrs[0]->newInstance();
                $classPrefix     = $classRoute->prefix     ?? $classPrefix;
                $classGroup      = $classRoute->group      ?? $classGroup;
                $classMiddleware = $classRoute->middleware ?? $classMiddleware;
                $classAuth       = $classRoute->auth       ?? $classAuth;
                $classRoles      = $classRoute->roles      ?? $classRoles;
            }

            // ==== === 方法级注解：支持 Route 或 BaseMapping (GetMapping/PostMapping...) ====
            foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $methodAttrs = $method->getAttributes();

                // 如果没有任何注解，回退到你的 auto route 逻辑（保持原样）
                if (empty($methodAttrs)) {
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

                // 遍历所有注解（允许重复注解）
                foreach ($methodAttrs as $attr) {
                    $attrClass = $attr->getName();
                    $attrInst  = $attr->newInstance();

                    // ---- 情况A：方法使用你已有的 Route Attribute ----
                    if ($attrClass === Route::class || is_a($attrInst, Route::class)) {
                        $routeAttr = $attrInst;
                        // 直接使用你的 Route 对象转换为 Symfony\Route（和之前一样）
                        $this->addSymfonyRouteFromRouteAttr(
                            $routeCollection,
                            $className,
                            $method->getName(),
                            $classPrefix,
                            $classMiddleware,
                            $classAuth,
                            $classRoles,
                            $routeAttr
                        );
                        continue;
                    }

                    // ---- 情况B：方法使用 BaseMapping 及其子类（GetMapping/PostMapping 等） ----
                    if (is_object($attrInst) && is_subclass_of($attrInst::class, BaseMapping::class)
                        || $attrInst instanceof BaseMapping) {
                        // 将 BaseMapping 规范化为一个临时 Route-like 对象（匿名 stdClass）
                        $mapping = $attrInst;

                        // mapping 包含: path, methods, auth, roles, middleware, defaults, requirements...
                        $routeLike = (object) [
                            'path'         => $mapping->path ?? '',
                            'methods'      => $mapping->methods ?? (property_exists($mapping, 'methods') ? $mapping->methods : []),
                            'middleware'   => $mapping->middleware ?? [],
                            'auth'         => $mapping->auth ?? null,
                            'roles'        => $mapping->roles ?? [],
                            'name'         => null,
                            'defaults'     => [],
                            'requirements' => [],
                            'schemes'      => [],
                            'host'         => null,
                        ];

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
            }
        }

        return $routeCollection;
    }

    /**
     * 将你原始 Route Attribute 转为 Symfony\Route 并加入集合。
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

        $mergedMiddleware = array_values(array_unique(array_merge((array)$classMiddleware, (array)$routeAttr->middleware)));

        $needAuth = $routeAttr->auth ?? $classAuth ?? false;
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
            host: $routeAttr->host ?? '',
            schemes: $routeAttr->schemes ?? [],
            methods: $routeAttr->methods ?: ['GET']
        );

        $name = $routeAttr->name ?? strtolower(str_replace('\\', '_', $className)) . '_' . $methodName;
        $collection->add($name, $sfRoute);
    }

    /**
     * 将一个 Route-like（由 BaseMapping 生成） 转为 Symfony\Route 并加入集合。
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

        $mergedMiddleware = array_values(array_unique(array_merge((array)$classMiddleware, (array)$routeLike->middleware)));

        $needAuth = $routeLike->auth ?? $classAuth ?? false;
        $roles    = $routeLike->roles ?? $classRoles ?? [];

        $sfRoute = new SymfonyRoute(
            path: $finalPath,
            defaults: array_merge(
                $routeLike->defaults ?? [],
                [
                    '_controller' => "{$className}::{$methodName}",
                    '_group'      => $routeLike->group ?? null,
                    '_middleware' => $mergedMiddleware,
                    '_auth'       => $needAuth,
                    '_roles'      => $roles,
                ]
            ),
            requirements: $routeLike->requirements ?? [],
            options: [],
            host: $routeLike->host ?? '',
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
