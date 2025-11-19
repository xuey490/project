<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
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
