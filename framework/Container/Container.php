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
        $dotenv  = new Dotenv();
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
     * 简单的 make 实现，用于模拟 Laravel/Webman 的构建行为.
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
        return self::$container->get($id, $invalidBehavior);
    }

    public function has(string $id): bool
    {
        return self::$container->has($id);
    }

    public function set(string $id, mixed $service): void
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

    public function setParameter(string $name, array|bool|float|int|string|\UnitEnum|null $value): void
    {
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

    public function getParameterBag()
    {
        return self::$container->getParameterBag();
    }

    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        self::$container->compile($resolveEnvPlaceholders);
    }

    public function isCompiled(): bool
    {
        return self::$container->isCompiled();
    }

    public function getCompilerPassConfig()
    {
        return self::$container->getCompilerPassConfig();
    }

    public function addCompilerPass($pass, string $type = 'beforeOptimization', int $priority = 0): static
    {
        self::$container->addCompilerPass($pass, $type, $priority);
        return $this;
    }
}
