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

/*
symfony/cache组件 工厂类
*/

namespace Framework\Cache;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

class CacheFactory
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create(): TagAwareAdapter
    {
        $store = $this->config['default'];
        return $this->createStore($store);
    }

    private function createStore(string $name): TagAwareAdapter
    {
        $config = $this->config['stores'][$name] ?? throw new \InvalidArgumentException("Cache store [{$name}] not found.");

        $driver = $config['driver'];
        $prefix = $config['prefix'] ?? '';
        // 注意：即使 enable_tags=false，我们也返回 TagAwareAdapter（它仍可工作）
        // 但你可以选择只在需要时包装，这里为简化统一包装

        $innerAdapter = match ($driver) {
            'file'      => new FilesystemAdapter($prefix, 0, $config['path']),
            'redis'     => $this->createRedisAdapter($prefix, $config),
            'apcu'      => new ApcuAdapter($prefix, 0),
            'array'     => new ArrayAdapter(),
            'memcached' => $this->createMemcachedAdapter($prefix, $config),
            default     => throw new \InvalidArgumentException("Unsupported driver: {$driver}"),
        };

        return new TagAwareAdapter($innerAdapter);
    }

    private function createRedisAdapter(string $prefix, array $config): AdapterInterface
    {
        # $conn     = $config['connection'];
        $host     = $config['host'];
        $port     = $config['port'];
        $password = $config['password']   ?? null;
        $database = $config['database']   ?? 0;

        // 构造标准 Redis DSN: redis://[password@]host:port
        if ($password) {
            $dsn = sprintf('redis://:%s@%s:%d/%d', urlencode($password), $host, $port, $database);
        } else {
            $dsn = sprintf('redis://%s:%d/%d', $host, $port, $database);
        }

        // 直接交给 Symfony 创建连接
        $redisConnection = RedisAdapter::createConnection($dsn);

        return new RedisAdapter($redisConnection, $prefix);
    }

    // === Memcached ===
    private function createMemcachedAdapter(string $prefix, array $config): AdapterInterface
    {
        if (! extension_loaded('memcached')) {
            throw new \RuntimeException('Memcached extension is required for "memcached" driver.');
        }

        $client  = new \Memcached();
        $servers = array_map(fn ($s) => [$s['host'], $s['port']], $config['servers']);
        $client->addServers($servers);

        return new MemcachedAdapter($client, $prefix);
    }
}
