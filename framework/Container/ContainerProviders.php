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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * 自动扫描并加载 Provider 类.
 */
class ContainerProviders
{
    protected array $loadedProviders = [];

    protected array $pendingBoot = [];

    /**
     * 扫描并注册所有 Provider（核心 + 应用）.
     */
    public function loadAll(ContainerConfigurator $configurator,  string $namespaceBase , ?string $appProviderDir = null): void
    {
        // 1️⃣ 核心 Provider（通过 Composer 自动映射）
        $coreNamespace = 'Framework\Providers\\';
        $corePath      = $this->getComposerNamespacePath($coreNamespace);

        if ($corePath && is_dir($corePath)) {
            $this->loadFromDirectory($configurator, $corePath, $coreNamespace);
        }

        // 2️⃣ 应用 Provider（可选）
        if ($appProviderDir && is_dir($appProviderDir)) {
            $this->loadFromDirectory($configurator, $appProviderDir, $namespaceBase);
        }
    }

    /**
     * 根据目录扫描并注册 Provider.
     */
    public function loadFromDirectory(ContainerConfigurator $configurator, string $directory, string $namespaceBase): void
    {
        if (! is_dir($directory)) {
            // 目录不存在直接跳过
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && preg_match('/Provider\.php$/i', $file->getFilename())) {
                // C:\Users\Administrator\Desktop\project-root\NovaPHP0.0.9\project/framework\Providers\CacheServiceProvider.php  getPathName()

                $className = $this->resolveClassName($file->getRealPath(), $directory, $namespaceBase);
                // $className.'<br />';
                if (! class_exists($className)) {
                    // 不再 require_once，Composer autoload 会自动加载
                    continue;
                }

                $this->registerProvider($configurator, $className);
            }
        }
    }

    /**
     * 注册单个 Provider.
     */
    public function registerProvider(ContainerConfigurator $configurator, string $className): void
    {
        // 防止重复注册
        foreach ($this->loadedProviders as $p) {
            if (get_class($p) === $className) {
                return;
            }
        }

        $ref = new \ReflectionClass($className);
        if (! $ref->implementsInterface(ServiceProviderInterface::class)) {
            return;
        }

        /** @var ServiceProviderInterface $provider */
        $provider = $ref->newInstance();

        // 调用 register
        if (method_exists($provider, 'register')) {
            // $className.'<br />';
            $provider->register($configurator);
        }

        $this->loadedProviders[] = $provider;
    }

    /**
     * 启动所有 Provider 的 boot 方法.
     */

    public function bootProviders($container): void
    {
        foreach ($this->loadedProviders as $provider) {
            // 如果是 ContainerConfigurator，则推迟 boot
            if ($container instanceof ContainerConfigurator) { 
                // 暂存，等待 ContainerBuilder 阶段再执行
                $this->pendingBoot[] = $provider;
                continue;
            }

            // 真正 boot（Builder 阶段）
            // if ($container instanceof \Framework\Container\Container) {
            if ($container instanceof ContainerBuilder) {
                $provider->boot($container);
            }
        }
    }


	public function bootProviders1($container): void
	{
		foreach ($this->loadedProviders as $provider) {

			// 如果还是配置阶段（Configurator），则延迟执行
			if ($container instanceof ContainerConfigurator) {
				$this->pendingBoot[] = $provider;
				continue;
			}

			// 进入这里说明已经是最终容器（Framework\Container\Container 或 ContainerBuilder）
			if (method_exists($provider, 'boot')) {
				$provider->boot($container);
			}
		}
	}

    /**
     * 自动获取 Composer 的 PSR-4 映射路径.
     */
    protected function getComposerNamespacePath(string $namespace): ?string
    {
        $autoloadFile = BASE_PATH . '/vendor/composer/autoload_psr4.php';
        if (! file_exists($autoloadFile)) {
            return null;
        }

        $map = require $autoloadFile;

        $namespace = trim($namespace, '\\') . '\\';

        foreach ($map as $nsPrefix => $paths) {
            if (str_starts_with($namespace, $nsPrefix)) {
                $path = is_array($paths) ? $paths[0] : $paths;
                return rtrim($path, '/');
            }
        }

        return null;
    }

    /**
     * 根据文件路径解析命名空间类名（更健壮）.
     *
     * @param  string $filePath      文件完整路径（realpath）
     * @param  string $baseDir       传入的扫描目录（例如 .../framework/Providers 或 .../app/Providers）
     * @param  string $namespaceBase 命名空间基准（例如 'Framework\\Providers\\' 或 'App\\Providers\\'）
     * @return string 完整类名（FQCN）
     */
    protected function resolveClassName(string $filePath, string $baseDir, string $namespaceBase): string
    {
        // 标准化目录分隔符
        $baseDir  = rtrim(str_replace('\\', '/', $baseDir), '/');
        $filePath = str_replace('\\', '/', $filePath);

        // 规范 namespaceBase（以单个反斜杠结尾，去多余）
        $namespaceBase = trim($namespaceBase, '\\') . '\\';

        // 取出 namespaceBase 最后的段（例如 'Providers'）
        $nsParts     = explode('\\', trim($namespaceBase, '\\'));
        $lastSegment = end($nsParts);

        // 试图在文件路径中找到最后那部分（/Providers/），以此作为相对路径起点（更可靠）
        $needle = '/' . $lastSegment . '/';
        $pos    = stripos($filePath, $needle);

        if ($pos !== false) {
            // 从该段开始取相对部分（去掉前导 slash）
            $relative = ltrim(substr($filePath, $pos + 1), '/');
        } else {
            // 回退：把 baseDir 从 filePath 中移除（你原来的方法）
            $relative = ltrim(str_replace($baseDir, '', $filePath), '/');
        }

        // 转换为命名空间的反斜杠并去掉 .php
        $relative = str_replace('/', '\\', $relative);
        $relative = preg_replace('/\.php$/i', '', $relative);

        // 如果 relative 以 lastSegment 开头（例如 Providers\...），则不要再次拼接 namespaceBase 中的 Providers
        $prefix = $lastSegment . '\\';
        if (str_starts_with($relative, $prefix)) {
            // 去掉开头的 lastSegment\
            $relative = preg_replace('/^' . preg_quote($prefix, '/') . '/i', '', $relative);
            $fqcn     = trim($namespaceBase . $relative, '\\');
        } else {
            $fqcn = trim($namespaceBase . $relative, '\\');
        }

        return $fqcn;
    }
}
