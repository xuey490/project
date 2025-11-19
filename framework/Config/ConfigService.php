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

namespace Framework\Config;

class ConfigService
{
    private ?array $cachedConfig = null;   // 内存缓存

    private string $cacheFile;             // 文件缓存路径

    private int $cacheTtl = 60;            // 缓存有效期（秒）

    private array $excludedFiles = ['routes.php', 'services.php']; // ❗ 不参与缓存的配置文件名

    public function __construct(
        private string $configDir,
        ?string $cacheFile = null
    ) {
        $this->configDir = rtrim($this->configDir, '/\\') . DIRECTORY_SEPARATOR;
        $this->cacheFile = $cacheFile ?? sys_get_temp_dir() . '/project_config.cache.php';
    }

    /**
     * 加载所有配置文件（带缓存与自动刷新）.
     */
    public function loadAll(): array
    {
        // 优先返回内存缓存
        if ($this->cachedConfig !== null) {
            // ⚡ 确保排除文件始终实时加载（不缓存）
            $this->reloadExcludedFiles($this->cachedConfig);
            return $this->cachedConfig;
        }

        // 若缓存文件存在且有效，从文件缓存加载
        if ($this->isCacheValid() && file_exists($this->cacheFile)) {
            $data = include $this->cacheFile;
            if (is_array($data)) {
                // ⚡ 加载排除文件的最新内容
                $this->reloadExcludedFiles($data);
                return $this->cachedConfig = $data;
            }
        }

        // 否则重新加载所有文件
        $config = [];
        $files  = glob($this->configDir . '*.php');

        foreach ($files as $file) {
            $filename = basename($file);
            $key      = basename($file, '.php');
            $data     = require $file;

            // 如果在排除列表中，不写入缓存文件
            if (in_array($filename, $this->excludedFiles, true)) {
                $config[$key] = $data;
                continue;
            }

            $config[$key] = $data;
        }

        // 写入缓存文件（不含被排除项）
        $cacheableData = array_diff_key(
            $config,
            array_flip(array_map(fn ($f) => basename($f, '.php'), $this->excludedFiles))
        );

        $this->writeCacheFile($cacheableData);

        return $this->cachedConfig = $config;
    }

    /**
     * 获取配置项（支持点语法：database.host）.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->loadAll();
        $keys   = explode('.', $key);
        $value  = $config;

        foreach ($keys as $k) {
            if (is_array($value) && array_key_exists($k, $value)) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * 清除缓存（内存 + 文件）.
     */
    public function clearCache(): void
    {
        $this->cachedConfig = null;
        if (file_exists($this->cacheFile)) {
            @unlink($this->cacheFile);
        }
    }

    /**
     * 返回当前配置目录路径.
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * 动态添加/修改排除文件列表.
     */
    public function setExcludedFiles(array $files): void
    {
        $this->excludedFiles = $files;
    }

    /**
     * 获取当前排除文件列表.
     */
    public function getExcludedFiles(): array
    {
        return $this->excludedFiles;
    }

    /**
     * 判断缓存文件是否有效.
     */
    private function isCacheValid(): bool
    {
        if (! file_exists($this->cacheFile)) {
            return false;
        }

        $cacheMTime = filemtime($this->cacheFile);
        if (! $cacheMTime) {
            return false;
        }

        if ((time() - $cacheMTime) > $this->cacheTtl) {
            return false;
        }

        // 检查配置目录是否有更新
        $files = glob($this->configDir . '*.php');
        foreach ($files as $file) {
            // route.php 不参与缓存，无需触发更新
            if (in_array(basename($file), $this->excludedFiles, true)) {
                continue;
            }

            if (filemtime($file) > $cacheMTime) {
                return false;
            }
        }

        return true;
    }

    /**
     * 写入缓存文件.
     */
    private function writeCacheFile(array $data): void
    {
        $export  = var_export($data, true);
        $content = <<<PHP
<?php
// generated at: {date('Y-m-d H:i:s')}
return {$export};
PHP;
        @file_put_contents($this->cacheFile, $content);
    }

    /**
     * 重新加载被排除的文件内容.
     */
    private function reloadExcludedFiles(array &$config): void
    {
        foreach ($this->excludedFiles as $filename) {
            $file = $this->configDir . $filename;
            if (is_file($file)) {
                $key          = basename($filename, '.php');
                $config[$key] = require $file;
            }
        }
    }
}
