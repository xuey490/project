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

namespace Framework\Container;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Dotenv\Dotenv;

class Container implements SymfonyContainerInterface
{
    private const CACHE_FILE = BASE_PATH . '/storage/cache/container.php';

    private static ?SymfonyContainerInterface $container = null;

    private static ?ContainerProviders $providers = null;

    /**
     * 初始化容器.
     *
     * @param  array             $parameters 全局参数
     * @throws \RuntimeException
     */
    public static function init(array $parameters = []): void
    {
        if (self::$container !== null) {
            return;
        }

        // 加载 .env 文件
        $envFile = BASE_PATH . '/.env';
        if (file_exists($envFile)) {
            (new Dotenv())->load($envFile);
        }

        $env    = env('APP_ENV') ?: 'local';
        $isProd = $env === 'prod';

        $projectRoot = BASE_PATH;
        $configDir   = $projectRoot . '/config';

        if (! is_dir($configDir)) {
            throw new \RuntimeException("配置目录不存在: {$configDir}");
        }

        $servicesFile = $configDir . '/services.php';
        if (! file_exists($servicesFile)) {
            throw new \RuntimeException("服务配置文件不存在: {$servicesFile}");
        }

        // 创建 Provider 管理器
        $providersManager = new ContainerProviders();

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectRoot);
        $containerBuilder->setParameter('kernel.debug', (bool) getenv('APP_DEBUG'));
        $containerBuilder->setParameter('kernel.environment', $env);

        if (! empty($parameters)) {
            $containerBuilder->setParameter('config', $parameters);
        }

        $loader = new PhpFileLoader($containerBuilder, new FileLocator($configDir));
        $loader->load('services.php');

        $containerBuilder->compile();

        // ***在容器编译后真正执行所有 Provider 的 boot()***
        $providersManager->bootProviders($containerBuilder);

        if ($isProd) {
            @mkdir(dirname(self::CACHE_FILE), 0777, true);
            $dumper       = new PhpDumper($containerBuilder);
            $cacheContent = $dumper->dump(['class' => 'ProjectServiceContainer']);
            file_put_contents(self::CACHE_FILE, $cacheContent);

            $loadedContainer = require self::CACHE_FILE;
            /*self::$container = new \ProjectServiceContainer();
            */
            self::$container = $loadedContainer instanceof SymfonyContainerInterface
                ? $loadedContainer
                : $containerBuilder;
        } else {
            self::$container = $containerBuilder;
        }

        // ✅ 编译完成后再执行 bootProviders
        if (isset(self::$providers)) {
            self::$providers->bootProviders(self::$container);
        }
    }
	
    /**
     * 内部助手：获取 ContainerBuilder，如果当前不是 Builder 则抛出异常
     * 用于解决 IDE 警告和运行时逻辑错误
     */
    private function getBuilder(): ContainerBuilder
    {
        if (self::$container instanceof ContainerBuilder) {
            return self::$container;
        }
        throw new \RuntimeException('Current container is not an instance of ContainerBuilder (it might be compiled or cached).');
    }
	
    /**
     * 内部助手：获取安全的容器实例
     */
    private static function getContainer(): SymfonyContainerInterface
    {
        if (self::$container === null) {
            self::init();
        }
        return self::$container; // @phpstan-ignore-line
    }

	
    /**
     * 1. 简单的 make 实现，用于模拟 Laravel/Webman 的构建行为.
     * @param string $abstract   类名
     * @param array  $parameters 构造函数参数 ['paramName' => value]
     */
    public function make(string $abstract, array $parameters = []): object
    {
        // 1. 如果没有参数且容器里有该服务，直接返回（单例/服务）
        // 只有当参数为空时才尝试 get，因为如果传了参数，说明用户想要一个新的带参实例
        if (empty($parameters) && self::$container->has($abstract)) {
            return self::$container->get($abstract);
        }

        // 2. 使用反射动态创建实例
        try {
            $reflector = new \ReflectionClass($abstract);

            if (! $reflector->isInstantiable()) {
                throw new \RuntimeException("Class [{$abstract}] is not instantiable.");
            }

            $constructor = $reflector->getConstructor();

            if (is_null($constructor)) {
                return new $abstract();
            }

            $dependencies = [];
            foreach ($constructor->getParameters() as $parameter) {
                $name = $parameter->getName();

                // 优先使用传入的参数
                if (array_key_exists($name, $parameters)) {
                    $dependencies[] = $parameters[$name];
                    continue;
                }

                // 尝试从容器获取依赖
                $type = $parameter->getType();
                // [优化] 增加对 UnionType 的简单处理或忽略，防止 PHP8+ 报错
                if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                    $dependencyClassName = $type->getName();

                    // 递归：如果容器有，get；如果容器没有，尝试自动 make (递归解决依赖)
                    // 你的原代码只做了 has() check，如果依赖也是未注册的类（如 Service），这里会失败
                    if (self::$container->has($dependencyClassName)) {
                        $dependencies[] = self::$container->get($dependencyClassName);
                        continue;
                    }

                    // [新增] 尝试递归 make 依赖对象
                    // 只有当依赖是具体的类时才尝试，接口无法 new
                    if (class_exists($dependencyClassName)) {
                        // 这里是关键：允许递归构建未注册的依赖树
                        $dependencies[] = $this->make($dependencyClassName);
                        continue;
                    }
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \RuntimeException("Unable to resolve dependency [{$parameter->name}] in class {$abstract}");
                }
            }

            return $reflector->newInstanceArgs($dependencies);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException('Container make failed: ' . $e->getMessage());
        }
    }
	
	
    /**
     * 2. 注册一个单例服务到容器.
     *
     * @param string   $id      服务的唯一ID
     * @param callable $factory 一个闭包或可调用对象，用于创建服务实例
     *
     * @throws \RuntimeException 如果容器已编译或不是 ContainerBuilder 实例
     */
    public function singleton(string $id, callable $factory): void
    {
        // 确保容器实例已经初始化
        if (self::$container === null) {
            throw new \RuntimeException('容器尚未初始化。');
        }

        // 动态注册服务只能在未编译的 ContainerBuilder 上进行
        if (!self::$container instanceof ContainerBuilder) {
             throw new \RuntimeException(
                '无法注册服务。当前容器不是一个可修改的 ContainerBuilder 实例。它可能已经被编译或从缓存加载。'
            );
        }

        $containerBuilder =  $this->getBuilder(); // 确保是 Builder or self::$container;

        if ($containerBuilder->isCompiled()) {
            throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
        }

        $definition = new Definition();
        $definition->setFactory($factory);
        $definition->setShared(true); // 明确指定为单例

        $containerBuilder->setDefinition($id, $definition);
    }
	
	/*
	* 3. 绑定接口到实现（Bind Interface to Implementation）
	* 将一个接口绑定到一个具体的实现类，容器会自动解析接口为对应的实现。
	*
	* 使用 setDefinition 注册接口，并指定其实现类。
	* 可以选择是否为单例。
	*/
	public function bind(string $abstract, string $concrete, bool $shared = false): void
	{
		if (self::$container === null) {
			throw new \RuntimeException('容器尚未初始化。');
		}

		if (!self::$container instanceof ContainerBuilder) {
			throw new \RuntimeException('当前容器不支持动态注册服务。');
		}

		$containerBuilder = $this->getBuilder(); //self::$container;

		if ($containerBuilder->isCompiled()) {
			throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
		}

		$definition = new Definition($concrete);
		$definition->setShared($shared);

		$containerBuilder->setDefinition($abstract, $definition);
	}
	
	/*
	4. 绑定工厂函数（Bind Factory Function）
	通过一个工厂函数来创建服务实例，适用于需要复杂初始化逻辑的场景。
	实现思路
	使用 setFactory 指定一个闭包或可调用对象作为工厂。
	可以选择是否为单例。
	*/
	public function factory(string $id, callable $factory, bool $shared = false): void
	{
		if (self::$container === null) {
			throw new \RuntimeException('容器尚未初始化。');
		}

		if (!self::$container instanceof ContainerBuilder) {
			throw new \RuntimeException('当前容器不支持动态注册服务。');
		}

		$containerBuilder = self::$container;

		if ($containerBuilder->isCompiled()) {
			throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
		}

		$definition = new Definition();
		$definition->setFactory($factory);
		$definition->setShared($shared);

		$containerBuilder->setDefinition($id, $definition);
	}

	/*
	5. 绑定实例（Bind Instance）
	直接将一个已存在的对象实例注册到容器中，适用于预初始化的对象。
	实现思路
	使用 set 方法直接注册实例（Symfony 容器原生支持）。
	注意：编译后的容器可能不支持 set 方法，因此需要在编译前调用。
	*/
	public function instance(string $id, object $instance): void
	{
		if (self::$container === null) {
			throw new \RuntimeException('容器尚未初始化。');
		}

		// 直接注册实例
		self::$container->set($id, $instance);
	}
	
	/*
	6. 绑定参数（Bind Parameter）
	注册一个参数（如配置值），供其他服务依赖注入时使用。
	实现思路
	使用 setParameter 方法注册参数。
	参数可以是字符串、数组、数字等。
	*/
	public function parameter(string $name, mixed $value): void
	{
		if (self::$container === null) {
			throw new \RuntimeException('容器尚未初始化。');
		}

		self::$container->setParameter($name, $value);
	}
		
	/*
	7. 绑定带标签的服务（Bind Tagged Services）
	为服务添加标签，方便批量获取同一类服务（如事件监听器、命令等）。
	实现思路
	在服务定义中添加标签。
	通过 findTaggedServiceIds 方法获取所有带特定标签的服务。
	*/	
	public function tag(string $id, string $tag, array $attributes = []): void
	{
		if (self::$container === null) {
			throw new \RuntimeException('容器尚未初始化。');
		}

		if (!self::$container instanceof ContainerBuilder) {
			throw new \RuntimeException('当前容器不支持动态注册服务。');
		}

		$containerBuilder = self::$container;

		if ($containerBuilder->isCompiled()) {
			throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
		}

		$definition = $containerBuilder->getDefinition($id);
		$definition->addTag($tag, $attributes);
	}
	
	/*
	8. 绑定延迟服务（Bind Lazy Services）
	延迟服务的初始化，直到第一次调用时才创建实例，适用于重量级服务。
	实现思路
	在服务定义中设置 setLazy(true)。
	Symfony 容器会自动生成一个代理类，延迟实例化。
	*/
	public function lazy(string $id, string $concrete, bool $shared = true): void
	{
		if (self::$container === null) {
			throw new \RuntimeException('容器尚未初始化。');
		}

		if (!self::$container instanceof ContainerBuilder) {
			throw new \RuntimeException('当前容器不支持动态注册服务。');
		}

		$containerBuilder = self::$container;

		if ($containerBuilder->isCompiled()) {
			throw new \RuntimeException('容器已经编译，无法再注册新的服务。');
		}

		$definition = new Definition($concrete);
		$definition->setShared($shared);
		$definition->setLazy(true);

		$containerBuilder->setDefinition($id, $definition);
	}	

    public static function setProviderManager(ContainerProviders $p): void
    {
        self::$providers = $p;
    }

    /**
     * 获取 Container 实例.
     */
    public static function getInstance(): self
    {
        if (self::$container === null) {
            self::init();
        }
        return new self();
    }

    // ========== 代理所有 Symfony ContainerInterface 方法 ==========
    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        return self::$container->get($id, intval($invalidBehavior));
    }
	
    public function has(string $id): bool
    {
        return self::$container->has($id);
    }

    public function set(string $id, ?object $service): void
    {
        // ⚠️ 注意：编译后的容器会抛出异常！
        self::$container->set($id, $service);
    }

    public function initialized(string $id): bool
    {
        return self::$container->initialized($id);
    }

    public function getServiceIds(): array
    {
        return self::$container->getServiceIds();
    }

	/*
	6. 绑定参数（Bind Parameter）
	注册一个参数（如配置值），供其他服务依赖注入时使用。
	实现思路
	使用 setParameter 方法注册参数。
	参数可以是字符串、数组、数字等。
	*/
    public function setParameter(string $name, array|bool|float|int|string|\UnitEnum|null $value): void
    {
		if (self::$container === null) {
			throw new \RuntimeException('容器尚未初始化。');
		}
		
        self::$container->setParameter($name, $value);
    }

    public function hasParameter(string $name): bool
    {
        return self::$container->hasParameter($name);
    }

    public function getParameter(string $name): array|bool|float|int|string|\UnitEnum|null
    {
        return self::$container->getParameter($name);
    }

    public function getParameterBag(): ParameterBagInterface
    {
        return self::$container->getParameterBag();
    }

    // 优化：compile 方法增加类型检查
    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        // 只有 Builder 才能编译
        if (self::$container instanceof ContainerBuilder) {
            self::$container->compile($resolveEnvPlaceholders);
        }
    }	

    public function isCompiled(): bool
    {
        return self::$container->isCompiled();
    }

    public function getCompilerPassConfig(): PassConfig
    {
        return self::$container->getCompilerPassConfig();
    }

    public function addCompilerPass(CompilerPassInterface $pass, string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0): static
    {
        self::$container->addCompilerPass($pass, $type, $priority);
        return $this;
    }
}
