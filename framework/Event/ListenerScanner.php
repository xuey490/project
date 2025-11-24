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

namespace Framework\Event;

use Framework\Cache\CacheFactory;

class ListenerScanner
{
    private string $listenerDir;

    private CacheFactory $cache;

    private int $cacheTtl = 3600; // 1小时

    private string $cacheKey = 'event.subscribers';

    public function __construct(CacheFactory $cache, ?string $listenerDir = null)
    {
        $this->cache       = $cache;
        $this->listenerDir = $listenerDir ?? BASE_PATH . '/app/Listeners';
    }

    /**
     * 获取监听器（自动缓存 + 自动刷新）.
     */
    public function getSubscribers(): array
    {
        // 开发环境建议禁用缓存（可选）
        // if (APP_ENV === 'dev') {
        //     return $this->scanAndBuild();
        // }

        if (! function_exists('cache_get') || ! function_exists('cache_set')) {
            return $this->scanAndBuild();
        }

        // 1. 尝试从缓存读取
        $cached             = cache_get($this->cacheKey);
        $currentFingerprint = $this->generateFingerprint();

        // 2. 缓存命中且指纹一致 → 直接返回
        if ($cached && is_array($cached) && ($cached['fingerprint'] ?? null) === $currentFingerprint) {
            app('log')->info('[Event Scan] Subscribers loaded from cache (fingerprint match).');
            return $cached['subscribers'] ?? [];
        }

        // 3. 缓存未命中 或 指纹不一致 → 重新扫描
        app('log')->info('[Event Expired] Listener files changed or cache expired. Rescanning...');
        $result = $this->scanAndBuild();

        // 4. 更新缓存
        cache_set($this->cacheKey, [
            'fingerprint' => $currentFingerprint,
            'subscribers' => $result,
        ], $this->cacheTtl);

        return $result;
    }

    /**
     * 扫描并构建监听器列表.
     */
    private function scanAndBuild(): array
    {
        $listenerDir = $this->listenerDir;

        if (! is_dir($listenerDir)) {
            app('log')->info("[Event NF] Listeners directory not found: {$listenerDir}");
            return [];
        }

        $files = glob($listenerDir . '/*.php');
        if (! $files || ! is_array($files)) {
            app('log')->info("[Event] No PHP files found in: {$listenerDir}");
            return [];
        }

        $subscribers = [];

        foreach ($files as $file) {
            $className = 'App\Listeners\\' . pathinfo($file, PATHINFO_FILENAME);

            if (! class_exists($className, false)) {
                try {
                    require_once $file;
                } catch (\Throwable $e) {
                    app('log')->info("[Event] Failed to load listener file: {$file} - " . $e->getMessage());
                    continue;
                }
            }

            if (! class_exists($className)) {
                app('log')->info("[Event] Class not found after loading: {$className} (file: {$file})");
                continue;
            }

            try {
                $ref = new \ReflectionClass($className);
            } catch (\ReflectionException $e) {
                app('log')->info("[Event] Reflection failed for: {$className} - " . $e->getMessage());
                continue;
            }

            if (! $ref->isInstantiable()) {
                continue;
            }

            if (! $ref->implementsInterface(ListenerInterface::class)) {
                continue;
            }

            $subscribers[] = $className;
        }

        app('log')->info('[Event] Scanned and found ' . count($subscribers) . ' subscribers.');
        return $subscribers;
    }

    /**
     * 生成监听器目录的指纹（基于文件修改时间）.
     */
    private function generateFingerprint(): string
    {
        $listenerDir = $this->listenerDir;
        if (! is_dir($listenerDir)) {
            return md5('no_dir');
        }

        $files = glob($listenerDir . '/*.php');
        if (! $files) {
            return md5('no_files');
        }

        // 按文件名排序，确保顺序一致
        sort($files);

        $hashParts = [];
        foreach ($files as $file) {
            $hashParts[] = filemtime($file) . ':' . filesize($file); // 更健壮：mtime + size
        }

        return md5(implode('|', $hashParts));
    }
}
