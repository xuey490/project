<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Core;

use Framework\Attributes\Route;
use Framework\Attributes\Routes\BaseMapping;
use Framework\Attributes\Routes\Prefix;
// 引入接口，这是识别中间件的关键
use Framework\Attributes\MiddlewareProviderInterface; 
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;

/**
 * AttributeRouteLoader
 * 
 * 核心逻辑：
 * 1. 扫描控制器目录
 * 2. 解析 PHP Attributes (Route, GetMapping, Auth, Log...)
 * 3. 解析 DocBlock (@method, @middleware...)
 * 4. 提取实现了 MiddlewareProviderInterface 接口的中间件
 * 5. 合并所有数据生成 Symfony RouteCollection
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

	/**
     * 加载所有路由
     */
    public function loadRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $controllerFiles = $this->scanDirectory($this->controllerDir);

        foreach ($controllerFiles as $file) {
            $className = $this->convertFileToClass($file);
            if (!class_exists($className)) continue;
            
            $refClass = new \ReflectionClass($className);
            if ($refClass->isAbstract()) continue;

            // =========================================================
            // 1. 类级别处理 (Class Level)
            // =========================================================

            // A. 收集类级业务注解 (Auth, Log...) & 提取中间件
            [$classAttributesMap, $classExtraMiddleware] = $this->collectAttributesAndMiddleware($refClass->getAttributes());

            // B. 解析基础配置 (Prefix / Route / DocBlock)
            $classPrefix     = '';
            $classGroup      = null;
            $classMiddleware = []; // 这里存放 Prefix/Route/DocBlock 定义的手动中间件
            $classAuth       = null;
            $classRoles      = [];

            // Prefix (Spring Style)
            $prefixAttrs = $refClass->getAttributes(Prefix::class);
            if (!empty($prefixAttrs)) {
                $inst = $prefixAttrs[0]->newInstance();
                $classPrefix     = $inst->prefix     ?? '';
                $classMiddleware = $inst->middleware ?? [];
                $classAuth       = $inst->auth       ?? null;
                $classRoles      = $inst->roles      ?? [];
            }

            // Route (Symfony Style - 覆盖 Prefix)
            $routeAttrs = $refClass->getAttributes(Route::class);
            if (!empty($routeAttrs)) {
                $inst = $routeAttrs[0]->newInstance();
                $classPrefix     = $inst->prefix     ?? $classPrefix;
                $classGroup      = $inst->group      ?? $classGroup;
                $classMiddleware = $inst->middleware ?? $classMiddleware;
                $classAuth       = $inst->auth       ?? $classAuth;
                $classRoles      = $inst->roles      ?? $classRoles;
            }

            // DocBlock
            $classDocData = $this->parseDocBlockAnnotations($refClass->getDocComment() ?: null);
            $classPrefix     = $classDocData['prefix']     ?? $classPrefix;
            $classGroup      = $classDocData['group']      ?? $classGroup;
            $classMiddleware = array_merge($classMiddleware, $classDocData['middleware'] ?? []);
            $classAuth       = $classDocData['auth']       ?? $classAuth;
            $classRoles      = array_merge($classRoles, $classDocData['roles'] ?? []);
						
						 //dump($classExtraMiddleware);
						
            // 🔥 C. 合并类级自动提取的中间件 (来自 #[Auth] 等)
            $classMiddleware = array_merge($classMiddleware, $classExtraMiddleware);


            // =========================================================
            // 2. 方法级别处理 (Method Level)
            // =========================================================
            foreach ($refClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if (str_starts_with($method->getName(), '__')) continue;

                // A. 收集方法级业务注解 (如 #[Auth]) & 提取中间件
                // 无论有没有 #[Route]，这一步都会运行
                [$methodAttributesMap, $methodExtraMiddleware] = $this->collectAttributesAndMiddleware($method->getAttributes());
                
                // 合并注解对象 (Method 覆盖 Class)
                $mergedAttributesMap = array_merge($classAttributesMap, $methodAttributesMap);

                // B. 解析 DocBlock
                $docBlockData = $this->parseDocBlockAnnotations($method->getDocComment() ?: null);

                // C. 寻找显式路由定义 (Route 或 BaseMapping)
                $routeDef = null;
                foreach ($method->getAttributes() as $attr) {
                    $inst = $attr->newInstance();
                    
                    if ($inst instanceof Route) {
                        $routeDef = $inst;
                        break;
                    }
                    
                    if ($inst instanceof BaseMapping) {
                        // 兼容 BaseMapping 转为通用对象
                        $routeDef = (object)[
                            'path' => $inst->path,
                            'methods' => $inst->methods ?? (property_exists($inst, 'methods') ? $inst->methods : []),
                            'middleware' => $inst->middleware ?? [],
                            'defaults' => [], 'host' => null, 'schemes' => [], 'name' => null, 'group' => null,
                            'auth' => $inst->auth ?? null, 'roles' => $inst->roles ?? []
                        ];
                        break;
                    }
                }

                // D. 🔥 自动路由兜底 (Auto Route Fallback)
                // 如果没有显式 Route 注解，根据方法名生成路由
                // 这样即使只有 #[Auth] 注解，也能生成路由并生效
                if (!$routeDef) {
                    // 确定路径：DocBlock > 自动生成
                    $autoPath = !empty($docBlockData['path']) 
                        ? $docBlockData['path'] 
                        : '/' . strtolower(str_replace('Controller', '', $refClass->getShortName())) . '/' . $method->getName();

                    $routeDef = (object)[
                        'path'       => $autoPath,
                        'methods'    => $docBlockData['methods'] ?? ['GET'],
                        'middleware' => [], // 初始为空，稍后会合并 AuthMiddleware
                        'defaults'   => [],
                        'host'       => null, 'schemes' => [], 
                        'name'       => $docBlockData['name'] ?? null, 
                        'group'      => $docBlockData['group'] ?? null,
                        'auth'       => $docBlockData['auth'] ?? null, 
                        'roles'      => $docBlockData['roles'] ?? []
                    ];
                }

                // =========================================================
                // 3. 数据合并与生成
                // =========================================================

                // 路径
                $finalPath = '/' . trim(trim($classPrefix, '/') . '/' . trim($routeDef->path, '/'), '/');
                $finalGroup = $docBlockData['group'] ?? $routeDef->group ?? $classGroup;
                
                // Auth & Roles
                $finalAuth = $docBlockData['auth'] ?? $routeDef->auth ?? $classAuth ?? null;
                $finalRoles = array_values(array_unique(array_merge($classRoles, $routeDef->roles ?? [], $docBlockData['roles'] ?? [])));

                // 🔥 中间件合并
                $rawMergedMiddleware = array_merge(
                    $classMiddleware,            // 类级所有
                    $routeDef->middleware ?? [], // 显式路由参数定义的
                    $methodExtraMiddleware,      // 方法级注解提取的 (这里包含 AuthMiddleware)
                    $docBlockData['middleware'] ?? []
                );

                // 🔥 清洗：去重 + 去除空值
                $finalMiddleware = array_values(array_unique(array_filter($rawMergedMiddleware, function($v) {
                    return !empty($v) && is_string($v);
                })));

                // 构建参数
                $defaults = array_merge($routeDef->defaults ?? [], [
                    '_controller' => "{$className}::{$method->getName()}",
                    '_group'      => $finalGroup,
                    '_middleware' => $finalMiddleware,
                    '_auth'       => $finalAuth,
                    '_roles'      => $finalRoles,
                    '_attributes' => $mergedAttributesMap, // 透传
                ]);

                // 创建 Symfony Route
                $sfRoute = new SymfonyRoute(
                    path: $finalPath,
                    defaults: $defaults,
                    requirements: $routeDef->requirements ?? [],
                    options: [],
                    host: $routeDef->host ?? '',
                    schemes: $routeDef->schemes ?? [],
                    methods: $routeDef->methods ?: ['GET']
                );

                $routeName = $routeDef->name ?? 
                             ($docBlockData['name'] ?? strtolower(str_replace('\\', '_', $className)) . '_' . $method->getName());
                
                $routeCollection->add($routeName, $sfRoute);
            }
        }

        return $routeCollection;
    }

    /**
     * 辅助方法：收集注解对象 & 从接口自动提取中间件
     */
    private function collectAttributesAndMiddleware(array $attributes): array
    {
        $map = [];
        $middlewareList = [];

        foreach ($attributes as $attr) {
            $name = $attr->getName();
            
            // 排除基础路由注解
            if ($name === Route::class || $name === Prefix::class || 
                $name === BaseMapping::class || is_subclass_of($name, BaseMapping::class)) {
                continue;
            }

            try {
                $inst = $attr->newInstance();
                $map[$name] = $inst;

                // 检查是否实现了 MiddlewareProviderInterface 接口
                if ($inst instanceof MiddlewareProviderInterface) {
                    $provided = $inst->getMiddleware();
                    $candidates = is_array($provided) ? $provided : [$provided];
                    
                    foreach ($candidates as $mid) {
                        if (is_string($mid) && !empty($mid)) {
                            $middlewareList[] = $mid;
                        }
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return [$map, array_values(array_unique($middlewareList))];
    }
	
    /**
     * 从 DocBlock 解析注解数据 (保持原有正则逻辑)
     */
    private function parseDocBlockAnnotations(?string $docComment): array
    {
        if ($docComment === null || trim($docComment) === '') {
            return [];
        }

        $annotations = [];
        
        // @method
        if (preg_match_all('/@method\s+([^\r\n]+)/i', $docComment, $matches)) {
            $methods = [];
            foreach ($matches[1] as $match) {
                $m = trim($match);
                if (!empty($m)) $methods[] = strtoupper($m);
            }
            if (!empty($methods)) $annotations['methods'] = $methods;
        }

        // @auth
        if (preg_match('/@auth\s+(true|false)/i', $docComment, $matches)) {
            $annotations['auth'] = strtolower($matches[1]) === 'true';
        }

        // @role
        if (preg_match('/@role\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['roles'] = array_map('trim', explode(',', trim($matches[1])));
        }

        // @middleware
        if (preg_match('/@middleware\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['middleware'] = array_map('trim', explode(',', trim($matches[1])));
        }

        // @prefix
        if (preg_match('/@prefix\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['prefix'] = trim($matches[1]);
        }

        // @group
        if (preg_match('/@group\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['group'] = trim($matches[1]);
        }

        // @name
        if (preg_match('/@name\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['name'] = trim($matches[1]);
        }

        // @path
        if (preg_match('/@path\s+([^\r\n]+)/i', $docComment, $matches)) {
            $annotations['path'] = trim($matches[1]);
        }

        return $annotations;
    }

    private function scanDirectory(string $dir): array
    {
        if (!is_dir($dir)) return [];
        $rii   = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
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