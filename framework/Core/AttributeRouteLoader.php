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
use Framework\Attributes\MiddlewareProviderInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCollection;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

/**
 * 基于PHP属性的注解路由加载器
 *
 * 该类负责从控制器类中扫描和解析PHP 8的属性(Attribute)注解，
 * 自动生成Symfony路由集合。支持类级别和方法级别的注解配置。
 *
 * 核心功能：
 * 1. 扫描控制器目录下的所有PHP文件
 * 2. 解析PHP属性注解（Route、GetMapping、Auth、Log等）
 * 3. 解析DocBlock注释（@method、@middleware等）
 * 4. 提取实现了MiddlewareProviderInterface接口的中间件
 * 5. 合并所有数据生成Symfony RouteCollection
 *
 * @package Framework\Core
 */
class AttributeRouteLoader
{
    /**
     * 控制器类名后缀
     */
    private const CONTROLLER_SUFFIX = 'Controller';

    /**
     * 路由参数：控制器键名
     */
    private const ROUTE_CONTROLLER_KEY = '_controller';

    /**
     * 路由参数：分组键名
     */
    private const ROUTE_GROUP_KEY = '_group';

    /**
     * 路由参数：中间件键名
     */
    private const ROUTE_MIDDLEWARE_KEY = '_middleware';

    /**
     * 路由参数：认证键名
     */
    private const ROUTE_AUTH_KEY = '_auth';

    /**
     * 路由参数：角色键名
     */
    private const ROUTE_ROLES_KEY = '_roles';

    /**
     * 路由参数：属性键名
     */
    private const ROUTE_ATTRIBUTES_KEY = '_attributes';

    /**
     * 默认HTTP方法
     */
    private const DEFAULT_HTTP_METHOD = 'GET';

    /**
     * 中间件名称允许的字符正则
     */
    private const ALLOWED_MIDDLEWARE_CHARS = '/^[a-zA-Z0-9_\\\-]+$/';

	/**
	 * 路径格式验证正则（支持Symfony风格的{参数名}占位符）
	 */
	private const PATH_REGEX = '/^\/[a-zA-Z0-9_\/{}-]+$/';

	/**
	 * 路由参数占位符格式验证正则
	 */
	private const ROUTE_PARAM_REGEX = '/\{[a-zA-Z0-9_]+\}/';

	/**
	 * 反射结果缓存（键为类名，值为ReflectionClass）
	 *
	 * @var array<string, ReflectionClass>
	 */
	private array $reflection_cache = [];

	/**
	 * 调试模式开关
	 *
	 * @var bool
	 */
	private bool $debug = false;

    /**
     * 路由默认参数配置
     */
    private const DEFAULT_ROUTE_PARAMS = [
        self::ROUTE_CONTROLLER_KEY => '',
        self::ROUTE_GROUP_KEY => null,
        self::ROUTE_MIDDLEWARE_KEY => [],
        self::ROUTE_AUTH_KEY => null,
        self::ROUTE_ROLES_KEY => [],
        self::ROUTE_ATTRIBUTES_KEY => [],
    ];

    /**
     * 控制器目录路径
     *
     * @var string
     */
    private string $controller_dir;

    /**
     * 控制器命名空间
     *
     * @var string
     */
    private string $controller_namespace;

    /**
     * 中间件白名单
     *
     * @var array
     */
    private array $allowed_middleware;

	/**
	 * 扫描白名单（只扫描包含指定字符串的文件）
	 *
	 * @var array
	 */
	private array $scan_whitelist = [];

	/**
	 * 扫描黑名单（排除指定的文件）
	 *
	 * @var array
	 */
	private array $scan_blacklist = ['BaseController.php'];

	/**
	 * 已扫描文件的缓存
	 *
	 * @var array|null
	 */
	private ?array $scanned_files_cache = null;

    /**
     * 构造函数
     *
     * 初始化路由加载器，设置控制器目录、命名空间和中间件白名单。
     *
     * @param string $controller_dir       控制器目录路径
     * @param string $controller_namespace 控制器命名空间
     * @param array  $allowed_middleware   允许的中间件白名单，默认为空数组（允许所有）
     */
    public function __construct(string $controller_dir, string $controller_namespace, array $allowed_middleware = [])
    {
        $this->controller_dir = rtrim($controller_dir, '/');
        $this->controller_namespace = rtrim($controller_namespace, '\\');
        $this->allowed_middleware = $allowed_middleware;
    }

    /**
     * 加载所有路由
     *
     * 扫描控制器目录，解析所有控制器的注解，生成完整的路由集合。
     *
     * @return RouteCollection 生成的路由集合
     */
    public function loadRoutes(): RouteCollection
    {
        $route_collection = new RouteCollection();
        $controller_files = $this->scan_directory($this->controller_dir);

        foreach ($controller_files as $file) {
            $class_name = $this->convert_file_to_class($file);
			// 调用缓存方法获取反射类
			$ref_class = $this->get_reflection_class($class_name);
			// 类不存在/反射失败/抽象类 都直接跳过
			if ($ref_class === null || $ref_class->isAbstract()) {
				continue;
			}

            // 缓存类属性解析结果 - 避免重复反射
            $class_attributes = $ref_class->getAttributes();

            // 1. 类级别处理
            $class_data = $this->process_class_level_data($ref_class, $class_attributes);

            // 2. 方法级别处理
            $this->process_method_level_data($ref_class, $class_data, $route_collection);
        }

        return $route_collection;
    }

    /**
     * 处理类级别数据
     *
     * 解析控制器类级别的注解配置，包括路由前缀、中间件、权限等。
     *
     * @param ReflectionClass $ref_class        类反射对象
     * @param array           $class_attributes 类属性列表
     *
     * @return array 处理后的类级别数据，包含prefix、group、middleware、auth、roles、attributes
     */
    private function process_class_level_data(ReflectionClass $ref_class, array $class_attributes): array
    {
        // 收集类级注解和自动提取的中间件
        $collected_data = $this->collect_attributes_and_middleware($class_attributes);

        // 解析类级基础配置（Prefix/Route/DocBlock）
        $prefix_data = $this->parse_class_prefix_attributes($ref_class);
        $route_data = $this->parse_class_route_attributes($ref_class);
        $doc_block_data = $this->parse_doc_block_annotations($ref_class->getDocComment() ?: null);

        // 合并配置（优先级：DocBlock > Route > Prefix）
        $class_prefix = $doc_block_data['prefix'] ?? $route_data['prefix'] ?? $prefix_data['prefix'] ?? '';
        $class_group = $doc_block_data['group'] ?? $route_data['group'] ?? $prefix_data['group'] ?? null;
        $class_auth = $doc_block_data['auth'] ?? $route_data['auth'] ?? $prefix_data['auth'] ?? null;

        // 合并中间件（类级手动配置 + 注解自动提取）
        $class_middleware = array_merge(
            $prefix_data['middleware'] ?? [],
            $route_data['middleware'] ?? [],
            $doc_block_data['middleware'] ?? [],
            $collected_data['middleware']
        );

        // 合并角色
        $class_roles = array_values(array_unique(array_merge(
            $prefix_data['roles'] ?? [],
            $route_data['roles'] ?? [],
            $doc_block_data['roles'] ?? []
        )));

        return [
            'prefix' => $class_prefix,
            'group' => $class_group,
            'middleware' => $class_middleware,
            'auth' => $class_auth,
            'roles' => $class_roles,
            'attributes' => $collected_data['attributes']
        ];
    }

    /**
     * 处理方法级别数据并生成路由
     *
     * 遍历控制器的所有公共方法，解析方法级注解，生成对应的路由。
     *
     * @param ReflectionClass $ref_class         类反射对象
     * @param array           $class_data        类级别数据
     * @param RouteCollection $route_collection  路由集合，用于添加生成的路由
     *
     * @return void
     */
    private function process_method_level_data(ReflectionClass $ref_class, array $class_data, RouteCollection $route_collection): void
    {
        foreach ($ref_class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $method_name = $method->getName();

            // 跳过魔术方法
            if (str_starts_with($method_name, '__')) {
                continue;
            }

            // 缓存方法属性解析结果
            $method_attributes = $method->getAttributes();

            // 收集方法级注解和自动提取的中间件
            $collected_method_data = $this->collect_attributes_and_middleware($method_attributes);

            // 解析方法级文档注释
            $doc_block_data = $this->parse_doc_block_annotations($method->getDocComment() ?: null);

            // 解析路由定义（显式注解 > 自动生成）
            $route_def = $this->parse_method_route_definition($method, $ref_class, $doc_block_data);

            // 合并所有配置
            $final_data = $this->merge_final_route_data($class_data, $route_def, $doc_block_data, $collected_method_data, $ref_class, $method);

            // 验证并创建路由
            $this->create_and_add_route($final_data, $route_collection, $ref_class, $method);
        }
    }

    /**
     * 解析类级Prefix注解
     *
     * 提取Prefix注解中的配置信息。
     *
     * @param ReflectionClass $ref_class 类反射对象
     *
     * @return array 包含prefix、middleware、auth、roles、group的配置数组
     */
    private function parse_class_prefix_attributes(ReflectionClass $ref_class): array
    {
        $prefix_attrs = $ref_class->getAttributes(Prefix::class);
        if (empty($prefix_attrs)) {
            return ['prefix' => '', 'middleware' => [], 'auth' => null, 'roles' => [], 'group' => null];
        }

        $inst = $prefix_attrs[0]->newInstance();
        return [
            'prefix' => $inst->prefix ?? '',
            'middleware' => $inst->middleware ?? [],
            'auth' => $inst->auth ?? null,
            'roles' => $inst->roles ?? [],
            'group' => null
        ];
    }

    /**
     * 解析类级Route注解
     *
     * 提取Route注解中的配置信息。
     *
     * @param ReflectionClass $ref_class 类反射对象
     *
     * @return array 包含prefix、middleware、auth、roles、group的配置数组
     */
    private function parse_class_route_attributes(ReflectionClass $ref_class): array
    {
        $route_attrs = $ref_class->getAttributes(Route::class);
        if (empty($route_attrs)) {
            return ['prefix' => '', 'middleware' => [], 'auth' => null, 'roles' => [], 'group' => null];
        }

        $inst = $route_attrs[0]->newInstance();
        return [
            'prefix' => $inst->prefix ?? '',
            'middleware' => $inst->middleware ?? [],
            'auth' => $inst->auth ?? null,
            'roles' => $inst->roles ?? [],
            'group' => $inst->group ?? null
        ];
    }

    /**
     * 解析方法级路由定义
     *
     * 从方法的注解中提取路由定义。优先使用显式注解，若无则自动生成。
     *
     * @param ReflectionMethod $method         方法反射对象
     * @param ReflectionClass  $ref_class      类反射对象
     * @param array            $doc_block_data DocBlock解析数据
     *
     * @return object 路由定义对象，包含path、methods、middleware等属性
     */
    private function parse_method_route_definition(ReflectionMethod $method, ReflectionClass $ref_class, array $doc_block_data): object
    {
        // 查找显式路由注解
        foreach ($method->getAttributes() as $attr) {
            $inst = $attr->newInstance();

            if ($inst instanceof Route) {
                return $inst;
            }

            if ($inst instanceof BaseMapping) {
                return (object)[
                    'path' => $inst->path,
                    'methods' => $inst->methods ?? [],
                    'middleware' => $inst->middleware ?? [],
                    'defaults' => [],
                    'host' => null,
                    'schemes' => [],
                    'name' => null,
                    'group' => null,
                    'auth' => $inst->auth ?? null,
                    'roles' => $inst->roles ?? [],
                    'requirements' => []
                ];
            }
        }

        // 自动生成路由（兜底）
        $auto_path = $doc_block_data['path'] ?? $this->generate_auto_path($ref_class, $method);

        return (object)[
            'path' => $auto_path,
            'methods' => $doc_block_data['methods'] ?? [self::DEFAULT_HTTP_METHOD],
            'middleware' => [],
            'defaults' => [],
            'host' => null,
            'schemes' => [],
            'name' => $doc_block_data['name'] ?? null,
            'group' => $doc_block_data['group'] ?? null,
            'auth' => $doc_block_data['auth'] ?? null,
            'roles' => $doc_block_data['roles'] ?? [],
            'requirements' => []
        ];
    }

    /**
     * 生成自动路由路径
     *
     * 根据控制器名和方法名自动生成路由路径。
     * 格式：/控制器名(去除Controller后缀)/方法名
     *
     * @param ReflectionClass  $ref_class 类反射对象
     * @param ReflectionMethod $method    方法反射对象
     *
     * @return string 生成的路由路径
     */
    private function generate_auto_path(ReflectionClass $ref_class, ReflectionMethod $method): string
    {
        $controller_name = str_replace(self::CONTROLLER_SUFFIX, '', $ref_class->getShortName());
        return '/' . strtolower($controller_name) . '/' . $method->getName();
    }

    /**
     * 合并最终路由数据
     *
     * 将类级别、方法级别、DocBlock的配置合并，生成最终的路由数据。
     *
     * @param array            $class_data           类级别数据
     * @param object           $route_def            路由定义对象
     * @param array            $doc_block_data       DocBlock数据
     * @param array            $collected_method_data 收集的方法数据
     * @param ReflectionClass  $ref_class            类反射对象
     * @param ReflectionMethod $method               方法反射对象
     *
     * @return array 合并后的最终路由数据
     */
    private function merge_final_route_data(array $class_data, object $route_def, array $doc_block_data, array $collected_method_data, ReflectionClass $ref_class, ReflectionMethod $method): array
    {
        // 路径规范化
        $final_path = $this->normalize_path(
            $class_data['prefix'] . '/' . ltrim($route_def->path, '/')
        );

        // 验证路径合法性
        $this->validate_route_path($final_path);

        // 合并基础配置
        $final_group = $doc_block_data['group'] ?? $route_def->group ?? $class_data['group'];
        $final_auth = $doc_block_data['auth'] ?? $route_def->auth ?? $class_data['auth'];

        // 合并角色（去重）
        $final_roles = array_values(array_unique(array_merge(
            $class_data['roles'],
            $route_def->roles ?? [],
            $doc_block_data['roles'] ?? []
        )));

        // 合并中间件（去重 + 过滤 + 白名单校验）
        $raw_middleware = array_merge(
            $class_data['middleware'],
            $route_def->middleware ?? [],
            $collected_method_data['middleware'],
            $doc_block_data['middleware'] ?? []
        );

        $final_middleware = $this->process_middleware_list($raw_middleware);

        // 合并注解（方法覆盖类）
        $merged_attributes = array_merge($class_data['attributes'], $collected_method_data['attributes']);

        // 路由名称
        $route_name = $route_def->name ??
                      $doc_block_data['name'] ??
                      strtolower(str_replace('\\', '_', $ref_class->getName())) . '_' . $method->getName();

        return [
            'path' => $final_path,
            'methods' => $route_def->methods ?: [self::DEFAULT_HTTP_METHOD],
            'middleware' => $final_middleware,
            'group' => $final_group,
            'auth' => $final_auth,
            'roles' => $final_roles,
            'attributes' => $merged_attributes,
            'route_name' => $route_name,
            'controller' => $ref_class->getName() . '::' . $method->getName(),
            'requirements' => $route_def->requirements ?? [],
            'host' => $route_def->host ?? '',
            'schemes' => $route_def->schemes ?? []
        ];
    }

    /**
     * 创建并添加路由到集合
     *
     * 根据最终数据创建Symfony路由对象并添加到路由集合中。
     *
     * @param array            $final_data        最终路由数据
     * @param RouteCollection  $route_collection  路由集合
     * @param ReflectionClass  $ref_class         类反射对象
     * @param ReflectionMethod $method            方法反射对象
     *
     * @return void
     */
    private function create_and_add_route(array $final_data, RouteCollection $route_collection): void
    {
        // 构建默认参数
        $defaults = array_merge(self::DEFAULT_ROUTE_PARAMS, [
            self::ROUTE_CONTROLLER_KEY => $final_data['controller'],
            self::ROUTE_GROUP_KEY => $final_data['group'],
            self::ROUTE_MIDDLEWARE_KEY => $final_data['middleware'],
            self::ROUTE_AUTH_KEY => $final_data['auth'],
            self::ROUTE_ROLES_KEY => $final_data['roles'],
            self::ROUTE_ATTRIBUTES_KEY => $final_data['attributes'],
        ]);

        // 创建 Symfony 路由
        $sf_route = new SymfonyRoute(
            path: $final_data['path'],
            defaults: $defaults,
            requirements: $final_data['requirements'],
            options: [],
            host: $final_data['host'],
            schemes: $final_data['schemes'],
            methods: $final_data['methods']
        );

		$route_name = $final_data['route_name'];
		// 检查路由名称重复
		$suffix = 1;
		while ($route_collection->get($route_name)) {
			$route_name = $final_data['route_name'] . '_' . $suffix++;
		}
		$route_collection->add($route_name, $sf_route);
    }

    /**
     * 收集注解对象并从接口自动提取中间件
     *
     * 遍历属性列表，收集非基础路由注解，并提取实现了MiddlewareProviderInterface的中间件。
     *
     * @param array $attributes 反射属性数组
     *
     * @return array 包含attributes（注解映射）和middleware（中间件列表）的数组
     */
    private function collect_attributes_and_middleware(array $attributes): array
    {
        $attributes_map = [];
        $middleware_list = [];

        foreach ($attributes as $attr) {
            $attr_name = $attr->getName();

            // 排除基础路由注解
            if (in_array($attr_name, [Route::class, Prefix::class, BaseMapping::class]) || is_subclass_of($attr_name, BaseMapping::class)) {
                continue;
            }

            try {
                $inst = $attr->newInstance();
                $attributes_map[$attr_name] = $inst;

                // 提取中间件（实现接口的注解）
                if ($inst instanceof MiddlewareProviderInterface) {
                    $provided = $inst->getMiddleware();
                    $candidates = is_array($provided) ? $provided : [$provided];

                    foreach ($candidates as $mid) {
                        if (is_string($mid) && !empty($mid)) {
                            $middleware_list[] = $mid;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // 记录异常日志
                error_log(sprintf(
                    'Error parsing attribute %s: %s (File: %s, Line: %d)',
                    $attr_name,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ));
                continue;
            }
        }

        // 去重优化（使用 array_flip 比 array_unique 更高效）
        $middleware_list = array_keys(array_flip($middleware_list));

        return [
            'attributes' => $attributes_map,
            'middleware' => $middleware_list
        ];
    }

    /**
     * 从DocBlock解析注解数据
     *
     * 解析PHPDoc注释中的自定义注解，如@method、@auth、@middleware等。
     *
     * @param string|null $doc_comment DocBlock注释内容
     *
     * @return array 解析后的注解数据数组
     */
    private function parse_doc_block_annotations(?string $doc_comment): array
    {
        if ($doc_comment === null || trim($doc_comment) === '') {
            return [];
        }

        $annotations = [];

        // 通用注解提取方法
        $extract_single = fn(string $prefix) => $this->extract_single_annotation($doc_comment, $prefix);
        $extract_list = fn(string $prefix) => $this->extract_list_annotation($doc_comment, $prefix);

        // 解析各类注解
        $annotations['methods'] = $extract_list('method');
        $annotations['auth'] = $extract_single('auth') !== null ? filter_var($extract_single('auth'), FILTER_VALIDATE_BOOLEAN) : null;
        $annotations['roles'] = $extract_list('role');
        $annotations['middleware'] = $extract_list('middleware');
        $annotations['prefix'] = $extract_single('prefix');
        $annotations['group'] = $extract_single('group');
        $annotations['name'] = $extract_single('name');
        $annotations['path'] = $extract_single('path');

        // 过滤空值
        return array_filter($annotations, fn($v) => $v !== null && $v !== []);
    }

    /**
     * 提取单个值注解
     *
     * 从DocBlock中提取指定前缀的单值注解。
     *
     * @param string $doc_comment DocBlock注释内容
     * @param string $prefix      注解前缀（如'auth'、'prefix'）
     *
     * @return string|null 注解值，如果不存在则返回null
     */
    private function extract_single_annotation(string $doc_comment, string $prefix): ?string
    {
        if (preg_match("/@{$prefix}\s+([^\r\n]+)/i", $doc_comment, $matches)) {
            $value = trim($matches[1]);
            return $value !== '' ? $value : null;
        }
        return null;
    }

    /**
     * 提取列表型注解
     *
     * 从DocBlock中提取逗号分隔的多值注解。
     *
     * @param string $doc_comment DocBlock注释内容
     * @param string $prefix      注解前缀（如'method'、'middleware'）
     *
     * @return array 注解值数组
     */
    private function extract_list_annotation(string $doc_comment, string $prefix): array
    {
        $value = $this->extract_single_annotation($doc_comment, $prefix);
        if ($value === null) {
            return [];
        }

        $list = array_map('trim', explode(',', $value));
        return array_filter($list, fn($v) => $v !== '');
    }

    /**
     * 规范化路径格式
     *
     * 确保路径以单个斜杠开头，去除多余的斜杠。
     *
     * @param string $path 原始路径
     *
     * @return string 规范化后的路径
     */
    private function normalize_path(string $path): string
    {
        return '/' . trim($path, '/');
    }

    /**
     * 验证路由路径合法性
     *
     * 检查路径格式是否符合规范，包括字符合法性和参数占位符格式。
     *
     * @param string $path 路由路径
     *
     * @return void
     *
     * @throws InvalidArgumentException 当路径格式不合法时抛出
     */
	private function validate_route_path(string $path): void
	{
		// 1. 过滤非法字符（仅保留字母、数字、下划线、斜杠、短横线、{}）
		$sanitized_path = preg_replace('/[^a-zA-Z0-9_\/{}-]/', '', $path);
		if ($sanitized_path !== $path) {
			throw new InvalidArgumentException("Invalid characters in route path: {$path}");
		}

		// 2. 验证基础路径格式
		if (!preg_match(self::PATH_REGEX, $path)) {
			throw new InvalidArgumentException("Invalid route path format: {$path}");
		}

		// 3. 校验参数占位符的合法性
		$open_brace = substr_count($path, '{');
		$close_brace = substr_count($path, '}');

		// 检查 {} 是否成对
		if ($open_brace !== $close_brace) {
			throw new InvalidArgumentException("Unmatched '{' and '}' in path: {$path}");
		}

		// 检查所有 {} 都是合法的参数格式
		if ($open_brace > 0) {
			$invalid_params = [];
			preg_match_all('/\{([^}]+)\}/', $path, $matches);
			foreach ($matches[1] as $param) {
				if (!preg_match('/^[a-zA-Z0-9_]+$/', $param)) {
					$invalid_params[] = $param;
				}
			}
			if (!empty($invalid_params)) {
				throw new InvalidArgumentException("Invalid parameter name(s) in path: " . implode(', ', $invalid_params));
			}
		}
	}


    /**
     * 处理中间件列表
     *
     * 对中间件列表进行去重、过滤和白名单校验。
     *
     * @param array $middleware_list 原始中间件列表
     *
     * @return array 处理后的中间件列表
     */
    private function process_middleware_list(array $middleware_list): array
    {
        // 过滤空值和非字符串
        $filtered = array_filter($middleware_list, fn($v) => is_string($v) && !empty($v));

        // 校验中间件名称格式
        $validated = array_filter($filtered, fn($mid) => preg_match(self::ALLOWED_MIDDLEWARE_CHARS, $mid));

        // 白名单校验（如果配置了白名单）
        if (!empty($this->allowed_middleware)) {
            $validated = array_filter($validated, fn($mid) => in_array($mid, $this->allowed_middleware));
        }

        // 高效去重
        return array_keys(array_flip($validated));
    }

    /**
     * 扫描目录获取所有PHP文件
     *
     * 递归扫描指定目录，获取所有PHP文件的路径。
     * 支持白名单和黑名单过滤。
     *
     * @param string $dir 要扫描的目录路径
     *
     * @return array PHP文件路径数组
     */
    private function scan_directory(string $dir): array
    {
				if ($this->scanned_files_cache !== null) {
					return $this->scanned_files_cache;
				}

        if (!is_dir($dir)) {
            return [];
        }

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];

        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }
            if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'php') {
                $files[] = $file->getPathname();
            }
        }

				/*新增黑白名单功能*/
				// 原有扫描逻辑 + 白名单/黑名单过滤
				$files = array_filter($files, function($file) {
					$filename = basename($file);
					$is_whitelist = empty($this->scan_whitelist) || preg_match('/('.implode('|', $this->scan_whitelist).')/', $filename);
					$is_blacklist = !empty($this->scan_blacklist) && preg_match('/('.implode('|', $this->scan_blacklist).')/', $filename);
					return $is_whitelist && !$is_blacklist;
				});
				$this->scanned_files_cache = $files;
				return $files;
    }


    /**
     * 将文件路径转换为类名
     *
     * 根据文件路径和控制器命名空间生成完整的类名。
     *
     * @param string $file 文件路径
     *
     * @return string 完整的类名（包含命名空间）
     *
     * @throws InvalidArgumentException 当类名不符合命名空间规范时抛出
     */
	private function convert_file_to_class(string $file): string
	{
		$relative = str_replace($this->controller_dir, '', $file);
		$relative = trim(str_replace(['/', '.php'], ['\\', ''], $relative), '\\');
		$class_name = "{$this->controller_namespace}\\{$relative}";

		// 仅校验命名空间归属，类存在性交给 get_reflection_class 处理
		if (strpos($class_name, $this->controller_namespace) !== 0) {
			throw new InvalidArgumentException("Invalid class name: {$class_name}");
		}

		return $class_name;
	}

	/**
	 * 获取反射类并缓存结果
	 *
	 * 避免重复反射同一个类，提高性能。
	 *
	 * @param string $class_name 类名（带命名空间）
	 *
	 * @return ReflectionClass|null 反射类对象，类不存在则返回null
	 */
	private function get_reflection_class(string $class_name): ?ReflectionClass
	{
		// 1. 优先从缓存获取，避免重复创建ReflectionClass
		if (isset($this->reflection_cache[$class_name])) {
			return $this->reflection_cache[$class_name];
		}

		// 2. 类不存在则返回null
		if (!class_exists($class_name)) {
			return null;
		}

		// 3. 创建反射类并缓存
		try {
			$ref_class = new ReflectionClass($class_name);
			$this->reflection_cache[$class_name] = $ref_class;
			return $ref_class;
		} catch (ReflectionException $e) {
			error_log(sprintf('反射类 %s 失败: %s', $class_name, $e->getMessage()));
			return null;
		}
	}

	/**
	 * 清空反射缓存
	 *
	 * 用于热重载场景，清空所有缓存的反射结果。
	 *
	 * @return void
	 */
	public function clear_reflection_cache(): void
	{
		$this->reflection_cache = [];
	}

	/**
	 * 清空指定类的反射缓存
	 *
	 * 用于热重载场景，清空特定类的反射缓存。
	 *
	 * @param string $class_name 类名
	 *
	 * @return void
	 */
	public function clear_reflection_cache_for_class(string $class_name): void
	{
		unset($this->reflection_cache[$class_name]);
	}

	/**
	 * 设置调试模式
	 *
	 * 开启调试模式后，会输出详细的日志信息。
	 *
	 * @param bool $debug 是否开启调试模式
	 *
	 * @return void
	 */
	public function set_debug(bool $debug): void
	{
		$this->debug = $debug;
	}
}
