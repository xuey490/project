<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: Container.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Container;

use Framework\Container\Compiler\AttributeInjectionPass; // Import custom compiler pass for attribute-based injection
use Framework\DI\AttributeInjector; // Import attribute injector for manual dependency injection
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * 依赖注入容器包装类
 *
 * 该类封装了 Symfony 的 ContainerBuilder，并提供类似 Laravel/Webman 风格的便捷方法。
 * 支持以下功能：
 * - 基于属性的依赖注入（#[Inject] 注解）
 * - 环境变量加载（.env 文件）
 * - 生产环境容器缓存
 * - 服务提供者引导
 * - 接口到实现类的绑定
 *
 * 实现了 Symfony 的 ContainerInterface 以保持与 Symfony 生态系统的兼容性。
 *
 * @package Framework\Container
 */
class Container implements SymfonyContainerInterface
{
    /**
     * 生产环境编译缓存文件路径
     *
     * 使用预编译的容器可以显著提高生产环境性能，
     * 避免运行时服务定义解析和编译。
     *
     * @var string
     */
    private const CACHE_FILE = BASE_PATH . '/storage/cache/container.php';

    /**
     * Symfony 容器的单例实例（ContainerBuilder 或编译缓存）
     *
     * @var SymfonyContainerInterface|null
     */
    private static ?SymfonyContainerInterface $container = null;

    /**
     * 服务提供者管理器实例
     *
     * @var ContainerProviders|null
     */
    private static ?ContainerProviders $providers = null;

    /**
     * 初始化并配置依赖注入容器
     *
     * 容器设置的入口点，处理以下步骤：
     * 1. 从 .env 文件加载环境变量
     * 2. 容器参数初始化（项目目录、调试模式、环境）
     * 3. 从 config/services.php 加载服务定义
     * 4. 注册编译器通道（包括自定义的 AttributeInjectionPass）
     * 5. 容器编译和缓存（仅生产环境）
     * 6. 服务提供者引导
     *
     * @param array $parameters 要设置到容器中的全局参数（例如应用配置）
     *
     * @throws RuntimeException 如果配置目录/文件缺失，或缓存目录创建失败
     *
     * @return void
     */
    public static function init(array $parameters = []): void
    {
        // 防止容器多次初始化
        if (self::$container !== null) {
            return;
        }

        // 加载环境变量从 .env 文件（如果存在）
        // 此处加载的变量可通过 getenv()/$_ENV/$_SERVER 访问
        $envFile = BASE_PATH . '/.env';
        if (file_exists($envFile)) {
            (new Dotenv())->load($envFile);
        }

        // 确定应用环境（默认：local）和生产状态
        $env    = env('APP_ENV') ?: 'local';
        $isProd = $env === 'prod';

        // 定义核心目录路径
        $projectRoot = BASE_PATH;
        $configDir   = $projectRoot . '/config';

        // 验证必需的配置目录存在
        if (! is_dir($configDir)) {
            throw new RuntimeException("Configuration directory does not exist: {$configDir}");
        }

        // 验证必需的服务配置文件存在
        $servicesFile = $configDir . '/services.php';
        if (! file_exists($servicesFile)) {
            throw new RuntimeException("Services configuration file does not exist: {$servicesFile}");
        }

        // 初始化服务提供者管理器（用于后续引导）
        $providersManager = new ContainerProviders();

        // 创建基础容器构建器并设置核心框架参数
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectRoot);
        $containerBuilder->setParameter('kernel.debug', (bool) getenv('APP_DEBUG'));
        $containerBuilder->setParameter('kernel.environment', $env);

        // 设置自定义全局参数（例如应用配置）如果提供
        if (! empty($parameters)) {
            $containerBuilder->setParameter('config', $parameters);
        }

        // =========================================================
        // 注册 AttributeInjectionPass
        // 必须在 compile() 之前添加，以处理 services.php 中注册的服务
        // 此通道通过 PHP 属性处理自动依赖注入
        // =========================================================
        $containerBuilder->addCompilerPass(new AttributeInjectionPass());

        // 从 PHP 配置文件加载服务定义
        // PhpFileLoader 解析 services.php 并注册所有定义的服务
        $loader = new PhpFileLoader($containerBuilder, new FileLocator($configDir));
        $loader->load('services.php');

        // 编译容器 - 解析所有依赖，验证服务定义并准备容器使用
        $containerBuilder->compile();

        // 在容器编译后引导所有已注册的服务提供者
        // 提供者可以在 boot() 期间访问完全解析的服务
        $providersManager->bootProviders($containerBuilder);

        // 处理生产环境缓存
        if ($isProd) {
            // 确保缓存目录存在并具有正确的权限
            $cacheDir = dirname(self::CACHE_FILE);
            if (! is_dir($cacheDir) && ! mkdir($cacheDir, 0777, true) && ! is_dir($cacheDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
            }

            // 将编译后的容器转储到 PHP 文件以进行性能优化
            $dumper       = new PhpDumper($containerBuilder);
            $cacheContent = $dumper->dump(['class' => 'ProjectServiceContainer']);
            file_put_contents(self::CACHE_FILE, $cacheContent);

            // 从缓存加载预编译的容器
            $loadedContainer = require self::CACHE_FILE;
            self::$container = $loadedContainer instanceof SymfonyContainerInterface
                ? $loadedContainer
                : $containerBuilder;
        } else {
            // 在非生产环境使用原始 ContainerBuilder 以便于调试
            self::$container = $containerBuilder;
        }

        // 在编译后对最终容器实例执行 bootProviders
        // 确保提供者使用实际的运行时容器（缓存或构建器）
        if (isset(self::$providers)) {
            self::$providers->bootProviders(self::$container);
        }
    }

    /**
     * 内部辅助方法：获取 ContainerBuilder 实例或抛出异常
     *
     * 用于解决 IDE 警告，并在尝试修改已编译/缓存的容器实例时防止运行时错误。
     *
     * @return ContainerBuilder 可变的容器构建器实例
     *
     * @throws RuntimeException 如果当前容器不是 ContainerBuilder（例如已缓存/生产环境）
     */
    private function getBuilder(): ContainerBuilder
    {
        if (self::$container instanceof ContainerBuilder) {
            return self::$container;
        }
        throw new RuntimeException('Current container is not an instance of ContainerBuilder (it might be compiled or cached).');
    }

    /**
     * 内部辅助方法：获取有效的容器实例（如果为 null 则初始化）
     *
     * 确保在任何服务解析之前容器已初始化。
     *
     * @return SymfonyContainerInterface 活动的容器实例
     */
    private static function getContainer(): SymfonyContainerInterface
    {
        if (self::$container === null) {
            self::init();
        }
        return self::$container; // @phpstan-ignore-line
    }

    /**
     * 创建类的实例并自动解析依赖（Laravel/Webman 风格）
     *
     * 该方法提供了一种便捷的方式来实例化类：
     * 1. 直接服务解析（无参数时）
     * 2. 基于反射的依赖注入（未注册的类）
     * 3. 手动参数覆盖支持
     * 4. 创建后属性注入
     *
     * 适用于控制器、中间件和未注册为服务的临时对象。
     *
     * @param string $abstract    要实例化的完整类名
     * @param array  $parameters  要覆盖的构造函数参数（key: 参数名, value: 值）
     *
     * @return object 实例化的类及其解析的依赖
     *
     * @throws RuntimeException 如果类不可实例化或依赖无法解析
     */
    public function make(string $abstract, array $parameters = []): object
    {
        // 1. 如果无参数且服务存在于容器中，直接返回（单例/服务）
        // 在 services.php 中注册的对象已通过 AttributeInjectionPass 配置了属性注入
        if (empty($parameters) && self::$container->has($abstract)) {
            return self::$container->get($abstract);
        }

        // 2. 使用反射动态创建实例（用于未注册的控制器/临时对象）
        try {
            $reflector = new ReflectionClass($abstract);

            // 验证类可以被实例化（不是抽象类、接口或 trait）
            if (! $reflector->isInstantiable()) {
                throw new RuntimeException("Class [{$abstract}] is not instantiable.");
            }

            $constructor = $reflector->getConstructor();
            $instance = null;

            // 处理没有构造函数的类（简单实例化）
            if (is_null($constructor)) {
                $instance = new $abstract();
            } else {
                $dependencies = [];
                // 解析每个构造函数参数
                foreach ($constructor->getParameters() as $parameter) {
                    $name = $parameter->getName();

                    // 优先级 1：使用手动提供的参数
                    if (array_key_exists($name, $parameters)) {
                        $dependencies[] = $parameters[$name];
                        continue;
                    }

                    // 优先级 2：从容器解析类型提示的依赖
                    $type = $parameter->getType();

                    if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                        $dependencyClassName = $type->getName();

                        // 如果可用则从容器解析
                        if (self::$container->has($dependencyClassName)) {
                            $dependencies[] = self::$container->get($dependencyClassName);
                            continue;
                        }

                        // 如果类存在但未注册，递归 make
                        if (class_exists($dependencyClassName)) {
                            $dependencies[] = $this->make($dependencyClassName);
                            continue;
                        }
                    }

                    // 优先级 3：使用默认参数值（如果可用）
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        // 无法解析的依赖 - 抛出有意义的异常
                        throw new RuntimeException("Unable to resolve dependency [{$parameter->name}] in class {$abstract}");
                    }
                }
                // 使用解析的构造函数依赖创建实例
                $instance = $reflector->newInstanceArgs($dependencies);
            }

            // 手动触发属性注入
            // 对于通过 make() 创建的对象（未在 services.php 中注册）是必需的
            // 注入标记为 #[Inject] 属性的依赖
            AttributeInjector::inject($instance);

            return $instance;

        } catch (ReflectionException $e) {
            // 包装反射异常以提供更好的错误上下文
            throw new RuntimeException('Container make failed: ' . $e->getMessage());
        }
    }

    /**
     * 在容器中注册单例服务
     *
     * 单例服务只创建一次，并在后续所有请求中重用。
     * 工厂回调只执行一次（在首次获取时）。
     *
     * @param string   $id      服务的唯一标识符（通常是类名或接口）
     * @param callable $factory 返回服务实例的闭包/可调用对象
     *
     * @throws RuntimeException 如果容器未初始化、已编译或不是 ContainerBuilder
     *
     * @return void
     */
    public function singleton(string $id, callable $factory): void
    {
        // 确保容器在修改前已初始化
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        // 动态服务注册仅在可变的 ContainerBuilder 上支持
        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException(
                'Cannot register service. Current container is not a modifiable ContainerBuilder instance. It may have been compiled or loaded from cache.'
            );
        }

        $containerBuilder = $this->getBuilder();

        // 防止修改已编译的容器
        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }

        // 创建带有工厂和单例作用域的服务定义
        $definition = new Definition();
        $definition->setFactory($factory);
        $definition->setShared(true); // 显式标记为单例/共享服务

        // 注册单例服务定义
        $containerBuilder->setDefinition($id, $definition);
    }

    /**
     * 将接口绑定到具体实现类
     *
     * 允许容器自动将接口类型提示解析为其配置的具体实现。
     * 支持单例和瞬态实例。
     *
     * 示例：bind(PaymentGatewayInterface::class, StripePaymentGateway::class, true)
     *
     * @param string $abstract 接口或抽象类名（服务标识符）
     * @param string $concrete 抽象的具体实现类名
     * @param bool   $shared   是否使用单例（true）或瞬态（false）作用域
     *
     * @throws RuntimeException 如果容器未初始化、已编译或不是 ContainerBuilder
     *
     * @return void
     */
    public function bind(string $abstract, string $concrete, bool $shared = false): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }

        $containerBuilder = $this->getBuilder();

        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }

        // 为具体实现创建定义并设置指定的作用域
        $definition = new Definition($concrete);
        $definition->setShared($shared);

        // 注册抽象-具体绑定
        $containerBuilder->setDefinition($abstract, $definition);
    }

    /**
     * 将服务绑定到工厂函数以进行复杂初始化
     *
     * 适用于需要复杂设置逻辑的服务（多个依赖、配置或条件初始化）。
     * 每次获取服务时都会调用工厂（除非 shared=true）。
     *
     * @param string   $id      唯一的服务标识符
     * @param callable $factory 返回服务实例的可调用对象
     * @param bool     $shared  是否使用单例（true）或瞬态（false）作用域
     *
     * @throws RuntimeException 如果容器未初始化、已编译或不是 ContainerBuilder
     *
     * @return void
     */
    public function factory(string $id, callable $factory, bool $shared = false): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }

        $containerBuilder = self::$container;

        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }

        // 创建带有工厂和指定作用域的服务定义
        $definition = new Definition();
        $definition->setFactory($factory);
        $definition->setShared($shared);

        // 注册基于工厂的服务
        $containerBuilder->setDefinition($id, $definition);
    }

    /**
     * 将现有对象实例直接绑定到容器中
     *
     * 适用于预初始化的对象（例如配置对象、数据库连接）
     * 这些对象应该在整个应用程序中重用。实例按原样存储，
     * 并在后续所有 get() 调用中返回。
     *
     * 注意：已编译的容器可能不支持 set() 方法 - 请在编译前调用
     *
     * @param string $id       实例的唯一标识符
     * @param object $instance 要注册的预初始化对象
     *
     * @throws RuntimeException 如果容器未初始化
     *
     * @return void
     */
    public function instance(string $id, object $instance): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        // 直接在容器中注册实例
        self::$container->set($id, $instance);
    }

    /**
     * 在容器中注册参数以用于依赖注入
     *
     * 参数是服务可用的标量值/数组（例如 API 密钥、配置值、路径）。
     * 可以通过构造函数或方法注入使用 %parameter_name% 语法注入到服务中。
     *
     * @param string $name  参数名（应该唯一）
     * @param mixed  $value 参数值（字符串、数组、整数、布尔值等）
     *
     * @throws RuntimeException 如果容器未初始化
     *
     * @return void
     */
    public function parameter(string $name, mixed $value): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        // 在容器的参数包中注册参数
        self::$container->setParameter($name, $value);
    }

    /**
     * 为现有服务添加标签以便分组检索
     *
     * 标签允许将相关服务分组（例如 event_listeners, console_commands, middleware）
     * 这些服务可以使用 findTaggedServiceIds() 集体检索。
     * 属性可以存储关于标记服务的额外元数据。
     *
     * 示例：tag('mail.notification.sms', 'notification_handler', ['priority' => 10])
     *
     * @param string $id         要标记的服务标识符
     * @param string $tag        标签名（例如 'event_listener', 'command'）
     * @param array  $attributes 标签的可选元数据（键值对）
     *
     * @throws RuntimeException 如果容器未初始化、已编译或不是 ContainerBuilder
     *
     * @return void
     */
    public function tag(string $id, string $tag, array $attributes = []): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }

        $containerBuilder = self::$container;

        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }

        // 获取现有服务定义并添加标签
        $definition = $containerBuilder->getDefinition($id);
        $definition->addTag($tag, $attributes);
    }

    /**
     * 绑定延迟初始化的服务以提高性能
     *
     * 延迟服务在容器编译时创建代理对象而不是实际服务。
     * 只有在代理上调用方法时才会实例化真正的服务。
     * 适用于可能不会在每次请求中都使用的重量级服务。
     *
     * 需要 symfony/proxy-manager-bridge 和 ocramius/proxy-manager 包
     *
     * @param string $id       唯一的服务标识符
     * @param string $concrete 要延迟加载的具体类名
     * @param bool   $shared   是否使用单例作用域（默认：true）
     *
     * @throws RuntimeException 如果容器未初始化、已编译或不是 ContainerBuilder
     *
     * @return void
     */
    public function lazy(string $id, string $concrete, bool $shared = true): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException('Current container does not support dynamic service registration.');
        }

        $containerBuilder = self::$container;

        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }

        // 创建带有指定作用域的延迟服务定义
        $definition = new Definition($concrete);
        $definition->setShared($shared);
        $definition->setLazy(true); // 通过代理启用延迟初始化

        // 注册延迟服务
        $containerBuilder->setDefinition($id, $definition);
    }

    /**
     * 设置服务提供者管理器实例
     *
     * 用于在容器初始化/引导之前注入提供者管理器。
     *
     * @param ContainerProviders $p 提供者管理器实例
     *
     * @return void
     */
    public static function setProviderManager(ContainerProviders $p): void
    {
        self::$providers = $p;
    }

    /**
     * 获取此 Container 包装类的单例实例
     *
     * 如果容器尚未初始化，则进行初始化。
     *
     * @return self Container 包装实例
     */
    public static function getInstance(): self
    {
        if (self::$container === null) {
            self::init();
        }
        return new self();
    }

    // ========== 代理所有 Symfony ContainerInterface 方法 ==========

    /**
     * 从容器获取服务
     *
     * @param string $id              服务标识符
     * @param int    $invalidBehavior 服务未找到时的行为（默认：抛出异常）
     *
     * @return object|null 服务实例或 null（取决于 invalidBehavior）
     */
    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        return self::$container->get($id, intval($invalidBehavior));
    }

    /**
     * 检查服务是否存在于容器中
     *
     * @param string $id 服务标识符
     *
     * @return bool 如果服务存在返回 true，否则返回 false
     */
    public function has(string $id): bool
    {
        return self::$container->has($id);
    }

    /**
     * 直接在容器中设置服务实例
     *
     * 警告：已编译的容器在调用此方法时会抛出异常！
     * 仅在未编译的 ContainerBuilder 实例上使用（开发环境）
     *
     * @param string       $id      服务标识符
     * @param object|null  $service 要设置的服务实例
     *
     * @return void
     */
    public function set(string $id, ?object $service): void
    {
        self::$container->set($id, $service);
    }

    /**
     * 检查服务是否已初始化（已创建）
     *
     * @param string $id 服务标识符
     *
     * @return bool 如果服务已初始化返回 true，否则返回 false
     */
    public function initialized(string $id): bool
    {
        return self::$container->initialized($id);
    }

    /**
     * 获取所有已注册的服务标识符
     *
     * @return array 服务 ID 列表
     */
    public function getServiceIds(): array
    {
        return self::$container->getServiceIds();
    }

    /**
     * 在容器中注册参数（parameter() 方法的别名）
     *
     * @param string $name  参数名
     * @param mixed  $value 参数值
     *
     * @throws RuntimeException 如果容器未初始化
     *
     * @return void
     */
    public function setParameter(string $name, mixed $value): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        self::$container->setParameter($name, $value);
    }

    /**
     * 检查参数是否存在于容器中
     *
     * @param string $name 参数名
     *
     * @return bool 如果参数存在返回 true，否则返回 false
     */
    public function hasParameter(string $name): bool
    {
        return self::$container->hasParameter($name);
    }

    /**
     * 从容器获取参数值
     *
     * @param string $name 参数名
     *
     * @return array|bool|float|int|string|\UnitEnum|null 参数值
     */
    public function getParameter(string $name): array|bool|float|int|string|\UnitEnum|null
    {
        return self::$container->getParameter($name);
    }

    /**
     * 获取参数包以直接操作参数
     *
     * @return ParameterBagInterface 容器的参数包
     */
    public function getParameterBag(): ParameterBagInterface
    {
        return self::$container->getParameterBag();
    }

    /**
     * 编译容器（仅适用于 ContainerBuilder 实例）
     *
     * 优化为仅在容器是可变的 ContainerBuilder 时才编译
     *
     * @param bool $resolveEnvPlaceholders 是否解析环境占位符
     *
     * @return void
     */
    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        // 仅在容器是可变的 ContainerBuilder 时才编译
        if (self::$container instanceof ContainerBuilder) {
            self::$container->compile($resolveEnvPlaceholders);
        }
    }

    /**
     * 检查容器是否已编译
     *
     * @return bool 如果已编译返回 true，否则返回 false
     */
    public function isCompiled(): bool
    {
        return self::$container->isCompiled();
    }

    /**
     * 获取编译器通道配置
     *
     * @return PassConfig 编译器通道配置
     */
    public function getCompilerPassConfig(): PassConfig
    {
        return self::$container->getCompilerPassConfig();
    }

    /**
     * 向容器添加编译器通道
     *
     * @param CompilerPassInterface $pass     要添加的编译器通道
     * @param string                $type     编译器通道类型（默认：TYPE_BEFORE_OPTIMIZATION）
     * @param int                   $priority 编译器通道的优先级（越高越先执行）
     *
     * @return static 当前 Container 实例，用于方法链式调用
     */
    public function addCompilerPass(CompilerPassInterface $pass, string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0): static
    {
        self::$container->addCompilerPass($pass, $type, $priority);
        return $this;
    }
}
