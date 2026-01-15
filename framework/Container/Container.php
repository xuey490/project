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
 * A wrapper class for Symfony's Dependency Injection Container with extended functionality
 * 
 * This class encapsulates Symfony's ContainerBuilder and provides additional convenience methods
 * similar to Laravel/Webman container (make, singleton, bind, etc.), while supporting:
 * - Attribute-based dependency injection
 * - Environment variable loading (.env)
 * - Production-level container caching
 * - Service provider bootstrapping
 * - Interface-to-implementation binding
 * 
 * Implements Symfony's ContainerInterface to maintain compatibility with Symfony ecosystem
 */
class Container implements SymfonyContainerInterface
{
    /**
     * Path to the compiled container cache file for production environment
     * 
     * Using a pre-compiled container significantly improves performance in production by
     * avoiding runtime service definition resolution and compilation
     */
    private const CACHE_FILE = BASE_PATH . '/storage/cache/container.php';

    /**
     * Singleton instance of the Symfony container (either ContainerBuilder or compiled cache)
     * 
     * @var SymfonyContainerInterface|null
     */
    private static ?SymfonyContainerInterface $container = null;

    /**
     * Service provider manager instance for bootstrapping custom providers
     * 
     * @var ContainerProviders|null
     */
    private static ?ContainerProviders $providers = null;

    /**
     * Initialize and configure the dependency injection container
     * 
     * This is the entry point for container setup, handling:
     * 1. Environment variable loading from .env file
     * 2. Container parameter initialization (project dir, debug mode, environment)
     * 3. Service definition loading from config/services.php
     * 4. Compiler pass registration (including custom AttributeInjectionPass)
     * 5. Container compilation and caching (production only)
     * 6. Service provider bootstrapping
     * 
     * @param array $parameters Global parameters to be set in the container (e.g. app config)
     * 
     * @throws RuntimeException If config directory/files are missing, or cache directory creation fails
     */
    public static function init(array $parameters = []): void
    {
        // Prevent multiple initializations of the container
        if (self::$container !== null) {
            return;
        }

        // Load environment variables from .env file if exists
        // Variables loaded here are available via getenv()/$_ENV/$_SERVER
        $envFile = BASE_PATH . '/.env';
        if (file_exists($envFile)) {
            (new Dotenv())->load($envFile);
        }

        // Determine application environment (default: local) and production status
        $env    = env('APP_ENV') ?: 'local';
        $isProd = $env === 'prod';

        // Define core directory paths
        $projectRoot = BASE_PATH;
        $configDir   = $projectRoot . '/config';

        // Validate required config directory exists
        if (! is_dir($configDir)) {
            throw new RuntimeException("Configuration directory does not exist: {$configDir}");
        }

        // Validate required services configuration file exists
        $servicesFile = $configDir . '/services.php';
        if (! file_exists($servicesFile)) {
            throw new RuntimeException("Services configuration file does not exist: {$servicesFile}");
        }

        // Initialize service provider manager for later bootstrapping
        $providersManager = new ContainerProviders();

        // Create base container builder and set core framework parameters
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectRoot);
        $containerBuilder->setParameter('kernel.debug', (bool) getenv('APP_DEBUG'));
        $containerBuilder->setParameter('kernel.environment', $env);

        // Set custom global parameters (e.g. application configuration) if provided
        if (! empty($parameters)) {
            $containerBuilder->setParameter('config', $parameters);
        }

        // =========================================================
        // 🔥 [ADDED] Register AttributeInjectionPass
        // Must be added before compile() to process services registered in services.php
        // This pass handles automatic dependency injection via PHP attributes
        // =========================================================
        $containerBuilder->addCompilerPass(new AttributeInjectionPass());

        // Load service definitions from PHP configuration file
        // The PhpFileLoader parses services.php and registers all defined services
        $loader = new PhpFileLoader($containerBuilder, new FileLocator($configDir));
        $loader->load('services.php');

        // Compile the container - resolves all dependencies, validates service definitions
        // and prepares the container for use
        $containerBuilder->compile();

        // Boot all registered service providers after container compilation
        // Providers can access fully resolved services during boot()
        $providersManager->bootProviders($containerBuilder);

        // Handle production environment caching
        if ($isProd) {
            // Ensure cache directory exists with proper permissions
            $cacheDir = dirname(self::CACHE_FILE);
            if (! is_dir($cacheDir) && ! mkdir($cacheDir, 0777, true) && ! is_dir($cacheDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
            }

            // Dump compiled container to PHP file for performance optimization
            $dumper       = new PhpDumper($containerBuilder);
            $cacheContent = $dumper->dump(['class' => 'ProjectServiceContainer']);
            file_put_contents(self::CACHE_FILE, $cacheContent);

            // Load the pre-compiled container from cache
            $loadedContainer = require self::CACHE_FILE;
            self::$container = $loadedContainer instanceof SymfonyContainerInterface
                ? $loadedContainer
                : $containerBuilder;
        } else {
            // Use raw ContainerBuilder in non-production environments for easier debugging
            self::$container = $containerBuilder;
        }

        // ✅ Execute bootProviders on the final container instance after compilation
        // This ensures providers use the actual runtime container (cached or builder)
        if (isset(self::$providers)) {
            self::$providers->bootProviders(self::$container);
        }
    }
	
    /**
     * Internal helper: Get the ContainerBuilder instance or throw exception
     * 
     * Used to resolve IDE warnings and prevent runtime errors when trying to modify
     * a compiled/cached container instance (which is immutable)
     * 
     * @return ContainerBuilder The mutable container builder instance
     * 
     * @throws RuntimeException If current container is not a ContainerBuilder (e.g. cached/prod)
     */
    private function getBuilder(): ContainerBuilder
    {
        if (self::$container instanceof ContainerBuilder) {
            return self::$container;
        }
        throw new RuntimeException('Current container is not an instance of ContainerBuilder (it might be compiled or cached).');
    }

    /**
     * Internal helper: Get a valid container instance (initialize if null)
     * 
     * Ensures the container is always initialized before any service resolution
     * 
     * @return SymfonyContainerInterface The active container instance
     */
    private static function getContainer(): SymfonyContainerInterface
    {
        if (self::$container === null) {
            self::init();
        }
        return self::$container; // @phpstan-ignore-line
    }

    /**
     * Create a new instance of a class with automatic dependency resolution (Laravel/Webman style)
     * 
     * This method provides a convenient way to instantiate classes with:
     * 1. Direct service resolution for registered classes (no parameters)
     * 2. Reflection-based dependency injection for unregistered classes
     * 3. Manual parameter override support
     * 4. Post-instantiation attribute injection for all created instances
     * 
     * Ideal for controllers, middleware, and transient objects not registered as services
     * 
     * @param string $abstract Fully qualified class name to instantiate
     * @param array $parameters Constructor parameters to override (key: parameter name, value: value)
     * 
     * @return object The instantiated class with resolved dependencies
     * 
     * @throws RuntimeException If class is not instantiable or dependencies cannot be resolved
     */
    public function make(string $abstract, array $parameters = []): object
    {
        // 1. If no parameters and service exists in container, return directly (singleton/service)
        // Objects registered in services.php have already had attribute injection configured
        // during the init() phase via AttributeInjectionPass
        if (empty($parameters) && self::$container->has($abstract)) {
            return self::$container->get($abstract);
        }

        // 2. Dynamically create instance using reflection (for unregistered controllers/transient objects)
        try {
            $reflector = new ReflectionClass($abstract);

            // Validate the class can be instantiated (not abstract, interface, or trait)
            if (! $reflector->isInstantiable()) {
                throw new RuntimeException("Class [{$abstract}] is not instantiable.");
            }

            $constructor = $reflector->getConstructor();
            $instance = null;

            // Handle classes with no constructor (simple instantiation)
            if (is_null($constructor)) {
                $instance = new $abstract();
            } else {
                $dependencies = [];
                // Resolve each constructor parameter
                foreach ($constructor->getParameters() as $parameter) {
                    $name = $parameter->getName();

                    // Priority 1: Use manually provided parameters
                    if (array_key_exists($name, $parameters)) {
                        $dependencies[] = $parameters[$name];
                        continue;
                    }

                    // Priority 2: Resolve type-hinted dependencies from container
                    $type = $parameter->getType();
                    
                    if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                        $dependencyClassName = $type->getName();

                        // Resolve from container if available
                        if (self::$container->has($dependencyClassName)) {
                            $dependencies[] = self::$container->get($dependencyClassName);
                            continue;
                        }

                        // Recursively make dependency if class exists but not registered
                        if (class_exists($dependencyClassName)) {
                            $dependencies[] = $this->make($dependencyClassName);
                            continue;
                        }
                    }

                    // Priority 3: Use default parameter value if available
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        // Unresolvable dependency - throw meaningful exception
                        throw new RuntimeException("Unable to resolve dependency [{$parameter->name}] in class {$abstract}");
                    }
                }
                // Create instance with resolved constructor dependencies
                $instance = $reflector->newInstanceArgs($dependencies);
            }
            
            // =========================================================
            // 🔥 [ADDED] Manually trigger attribute injection
            // Required for objects created via make() (not registered in services.php)
            // Injects dependencies marked with #[Inject] attributes
            // =========================================================
            AttributeInjector::inject($instance);

            return $instance;

        } catch (ReflectionException $e) {
            // Wrap reflection exceptions for better error context
            throw new RuntimeException('Container make failed: ' . $e->getMessage());
        }
    }

    /**
     * Register a singleton service in the container
     * 
     * Singleton services are created once and reused for all subsequent requests.
     * The factory callable is executed only once (on first retrieval)
     * 
     * @param string $id Unique identifier for the service (usually class name or interface)
     * @param callable $factory A closure/callable that returns the service instance
     * 
     * @throws RuntimeException If container is not initialized, compiled, or not a ContainerBuilder
     */
    public function singleton(string $id, callable $factory): void
    {
        // Ensure container is initialized before modification
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        // Dynamic service registration only supported on mutable ContainerBuilder
        if (! self::$container instanceof ContainerBuilder) {
            throw new RuntimeException(
                'Cannot register service. Current container is not a modifiable ContainerBuilder instance. It may have been compiled or loaded from cache.'
            );
        }

        $containerBuilder = $this->getBuilder();

        // Prevent modifications to already compiled container
        if ($containerBuilder->isCompiled()) {
            throw new RuntimeException('Container has already been compiled, cannot register new services.');
        }

        // Create service definition with factory and singleton scope
        $definition = new Definition();
        $definition->setFactory($factory);
        $definition->setShared(true); // Explicitly mark as singleton/shared service

        // Register the singleton service definition
        $containerBuilder->setDefinition($id, $definition);
    }

    /**
     * Bind an interface to a concrete implementation class
     * 
     * Allows the container to automatically resolve interface type-hints to their
     * configured concrete implementations. Supports both singleton and transient instances.
     * 
     * Example: bind(PaymentGatewayInterface::class, StripePaymentGateway::class, true)
     * 
     * @param string $abstract Interface or abstract class name (service identifier)
     * @param string $concrete Concrete class name to instantiate for the abstract
     * @param bool $shared Whether to use singleton (true) or transient (false) scope
     * 
     * @throws RuntimeException If container is not initialized, compiled, or not a ContainerBuilder
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

        // Create definition for concrete implementation with specified scope
        $definition = new Definition($concrete);
        $definition->setShared($shared);

        // Register the abstract-concrete binding
        $containerBuilder->setDefinition($abstract, $definition);
    }

    /**
     * Bind a service to a factory function for complex initialization
     * 
     * Useful for services requiring complex setup logic (multiple dependencies,
     * configuration, or conditional initialization). The factory is called each time
     * the service is retrieved (unless shared=true).
     * 
     * @param string $id Unique service identifier
     * @param callable $factory Callable that returns the service instance
     * @param bool $shared Whether to use singleton (true) or transient (false) scope
     * 
     * @throws RuntimeException If container is not initialized, compiled, or not a ContainerBuilder
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

        // Create service definition with factory and specified scope
        $definition = new Definition();
        $definition->setFactory($factory);
        $definition->setShared($shared);

        // Register the factory-based service
        $containerBuilder->setDefinition($id, $definition);
    }

    /**
     * Bind an existing object instance directly into the container
     * 
     * Useful for pre-initialized objects (e.g. configuration objects, database connections)
     * that should be reused throughout the application. The instance is stored as-is
     * and returned for all subsequent get() calls.
     * 
     * NOTE: Compiled containers may not support the set() method - call before compilation
     * 
     * @param string $id Unique identifier for the instance
     * @param object $instance The pre-initialized object to register
     * 
     * @throws RuntimeException If container is not initialized
     */
    public function instance(string $id, object $instance): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        // Directly register the instance in the container
        self::$container->set($id, $instance);
    }

    /**
     * Register a parameter in the container for dependency injection
     * 
     * Parameters are scalar values/arrays available to services (e.g. API keys,
     * configuration values, paths). Can be injected into services via constructor
     * or method injection using %parameter_name% syntax.
     * 
     * @param string $name Parameter name (should be unique)
     * @param mixed $value Parameter value (string, array, int, bool, etc.)
     * 
     * @throws RuntimeException If container is not initialized
     */
    public function parameter(string $name, mixed $value): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        // Register the parameter in the container's parameter bag
        self::$container->setParameter($name, $value);
    }

    /**
     * Add a tag to an existing service for group retrieval
     * 
     * Tags allow grouping related services (e.g. event_listeners, console_commands, middleware)
     * that can be retrieved collectively using findTaggedServiceIds(). Attributes can store
     * additional metadata about the tagged service.
     * 
     * Example: tag('mail.notification.sms', 'notification_handler', ['priority' => 10])
     * 
     * @param string $id Service identifier to tag
     * @param string $tag Tag name (e.g. 'event_listener', 'command')
     * @param array $attributes Optional metadata for the tag (key-value pairs)
     * 
     * @throws RuntimeException If container is not initialized, compiled, or not a ContainerBuilder
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

        // Retrieve existing service definition and add the tag
        $definition = $containerBuilder->getDefinition($id);
        $definition->addTag($tag, $attributes);
    }

    /**
     * Bind a lazy-initialized service to improve performance
     * 
     * Lazy services create a proxy object instead of the actual service on container compilation.
     * The real service is only instantiated when a method is called on the proxy.
     * Ideal for heavyweight services that may not be used on every request.
     * 
     * Requires symfony/proxy-manager-bridge and ocramius/proxy-manager packages
     * 
     * @param string $id Unique service identifier
     * @param string $concrete Concrete class name to lazy-load
     * @param bool $shared Whether to use singleton scope (default: true)
     * 
     * @throws RuntimeException If container is not initialized, compiled, or not a ContainerBuilder
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

        // Create lazy service definition with specified scope
        $definition = new Definition($concrete);
        $definition->setShared($shared);
        $definition->setLazy(true); // Enable lazy initialization via proxy

        // Register the lazy service
        $containerBuilder->setDefinition($id, $definition);
    }

    /**
     * Set the service provider manager instance
     * 
     * Used to inject the provider manager before container initialization/bootstrapping
     * 
     * @param ContainerProviders $p The provider manager instance
     */
    public static function setProviderManager(ContainerProviders $p): void
    {
        self::$providers = $p;
    }

    /**
     * Get the singleton instance of this Container wrapper class
     * 
     * Initializes the container if it hasn't been already
     * 
     * @return self The Container wrapper instance
     */
    public static function getInstance(): self
    {
        if (self::$container === null) {
            self::init();
        }
        return new self();
    }

    // ========== Proxy all Symfony ContainerInterface methods ==========
    
    /**
     * Get a service from the container
     * 
     * @param string $id Service identifier
     * @param int $invalidBehavior Behavior when service is not found (default: throw exception)
     * 
     * @return object|null The service instance or null (depending on invalidBehavior)
     */
    public function get(string $id, int $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE): ?object
    {
        return self::$container->get($id, intval($invalidBehavior));
    }

    /**
     * Check if a service exists in the container
     * 
     * @param string $id Service identifier
     * 
     * @return bool True if service exists, false otherwise
     */
    public function has(string $id): bool
    {
        return self::$container->has($id);
    }

    /**
     * Set a service instance directly in the container
     * 
     * ⚠️ WARNING: Compiled containers will throw an exception when calling this method!
     * Only use on uncompiled ContainerBuilder instances (development environment)
     * 
     * @param string $id Service identifier
     * @param object|null $service The service instance to set
     */
    public function set(string $id, ?object $service): void
    {
        self::$container->set($id, $service);
    }

    /**
     * Check if a service has been initialized (created)
     * 
     * @param string $id Service identifier
     * 
     * @return bool True if service is initialized, false otherwise
     */
    public function initialized(string $id): bool
    {
        return self::$container->initialized($id);
    }

    /**
     * Get all registered service identifiers
     * 
     * @return array List of service IDs
     */
    public function getServiceIds(): array
    {
        return self::$container->getServiceIds();
    }

    /**
     * Register a parameter in the container (alias for parameter() method)
     * 
     * @param string $name Parameter name
     * @param mixed $value Parameter value
     * 
     * @throws RuntimeException If container is not initialized
     */
    public function setParameter(string $name, mixed $value): void
    {
        if (self::$container === null) {
            throw new RuntimeException('Container has not been initialized.');
        }

        self::$container->setParameter($name, $value);
    }

    /**
     * Check if a parameter exists in the container
     * 
     * @param string $name Parameter name
     * 
     * @return bool True if parameter exists, false otherwise
     */
    public function hasParameter(string $name): bool
    {
        return self::$container->hasParameter($name);
    }

    /**
     * Get a parameter value from the container
     * 
     * @param string $name Parameter name
     * 
     * @return array|bool|float|int|string|\UnitEnum|null The parameter value
     */
    public function getParameter(string $name): array|bool|float|int|string|\UnitEnum|null
    {
        return self::$container->getParameter($name);
    }

    /**
     * Get the parameter bag for direct parameter manipulation
     * 
     * @return ParameterBagInterface The container's parameter bag
     */
    public function getParameterBag(): ParameterBagInterface
    {
        return self::$container->getParameterBag();
    }

    /**
     * Compile the container (only applicable to ContainerBuilder instances)
     * 
     * Optimized to only compile if the container is a mutable ContainerBuilder
     * 
     * @param bool $resolveEnvPlaceholders Whether to resolve environment placeholders
     */
    public function compile(bool $resolveEnvPlaceholders = false): void
    {
        // Only compile if container is a mutable ContainerBuilder
        if (self::$container instanceof ContainerBuilder) {
            self::$container->compile($resolveEnvPlaceholders);
        }
    }

    /**
     * Check if the container has been compiled
     * 
     * @return bool True if compiled, false otherwise
     */
    public function isCompiled(): bool
    {
        return self::$container->isCompiled();
    }

    /**
     * Get the compiler pass configuration
     * 
     * @return PassConfig The compiler pass configuration
     */
    public function getCompilerPassConfig(): PassConfig
    {
        return self::$container->getCompilerPassConfig();
    }

    /**
     * Add a compiler pass to the container
     * 
     * @param CompilerPassInterface $pass The compiler pass to add
     * @param string $type The type of compiler pass (default: TYPE_BEFORE_OPTIMIZATION)
     * @param int $priority The priority of the compiler pass (higher = executed first)
     * 
     * @return static The current Container instance for method chaining
     */
    public function addCompilerPass(CompilerPassInterface $pass, string $type = PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0): static
    {
        self::$container->addCompilerPass($pass, $type, $priority);
        return $this;
    }
}