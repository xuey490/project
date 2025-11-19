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

namespace Framework\Cache;

use Psr\SimpleCache\CacheInterface;
use think\cache\Driver; // ThinkCache 的统一接口
use think\contract\CacheHandlerInterface; // 或者直接用基类

class ThinkAdapter extends AbstractCache implements CacheInterface
{
    protected CacheHandlerInterface|Driver $driver;

    public function __construct(CacheHandlerInterface|Driver $cache)
    {
        $this->driver = $cache;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->driver->get($this->formatKey($key), $default);
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $ttlSeconds = is_int($ttl) ? $ttl : ($ttl ? (int) $ttl->format('%s') : $this->defaultTtl);
        return $this->driver->set($this->formatKey($key), $value, $ttl ?: $this->defaultTtl);
    }

    public function has(string $key): bool
    {
        return $this->driver->has($this->formatKey($key));
    }

    public function delete(string $key): bool
    {
        return $this->driver->delete($this->formatKey($key));
    }

    public function clear(): bool
    {
        return $this->driver->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        return $results;
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }
}
