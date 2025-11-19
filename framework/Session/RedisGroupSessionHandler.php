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

namespace Framework\Session;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

/**
 * RedisGroupSessionHandler.
 *
 * - 支持 group_prefix（不会传递给父类）
 * - 实现 Redis 分布式锁（SET NX PX + Lua 释放）以保证高并发安全写入
 *
 * Options 支持（兼容 Symfony 的常规选项）：
 *  - prefix (string)         : 键前缀（父类支持）
 *  - ttl (int)               : session TTL（秒）
 *  - locking (bool)          : 是否启用显式锁（默认 true）
 *  - spin_lock_wait (int)    : 自旋每次等待，单位微秒（默认 150000）
 *  - lock_ttl (int)          : 锁过期时间，单位毫秒（默认 30000）
 *  - group_prefix (string)   : 自定义分组前缀（会拼接到 prefix 前面）
 */
class RedisGroupSessionHandler extends RedisSessionHandler
{
    protected \Redis $redis;

    protected int $ttlLocal;

    protected bool $locking = true;

    protected int $spinLockWait = 150000; // 微秒

    protected int $lockTtl = 30000;       // 毫秒

    protected string $prefix = 'session:';

    protected string $groupPrefix = '';

    public function __construct(\Redis $redis, array $options = [])
    {
        // 处理自定义 option（不要把 group_prefix 传给父类）
        if (isset($options['group_prefix'])) {
            $this->groupPrefix = rtrim((string) $options['group_prefix'], ':') . ':';
            unset($options['group_prefix']);
        }

        // 读取并移除我们自定义的 lock_ttl/spin_wait/locking（父类不认识）
        if (isset($options['lock_ttl'])) {
            $this->lockTtl = (int) $options['lock_ttl'];
            unset($options['lock_ttl']);
        }
        if (isset($options['spin_lock_wait'])) {
            // NOTE: Symfony uses same key name; we keep it if present (microseconds)
            $this->spinLockWait = (int) $options['spin_lock_wait'];
            unset($options['spin_lock_wait']);
            // don't unset: parent allows spin_lock_wait
        }
        if (isset($options['locking'])) {
            $this->locking = (bool) $options['locking'];
            unset($options['locking']);
            // parent allows locking option too; keep it
        }

        // 确定 TTL（秒），优先从 options['ttl']，否则用 php.ini
        $this->ttlLocal = isset($options['ttl']) ? (int) $options['ttl'] : (int) (ini_get('session.gc_maxlifetime') ?: 0);

        // 拼接 groupPrefix 到真正的 prefix 参数，交给父类
        $options['prefix'] = ($this->groupPrefix . ($options['prefix'] ?? $this->prefix));

        // 调用父类构造（传入 options），以保持兼容性与内部锁/机制
        parent::__construct($redis, $options);

        // 保存 redis 实例与最终前缀（父类也持有，但我们用自己的更明确）
        $this->redis  = $redis;
        $this->prefix = $options['prefix'];
    }

    /**
     * 重写 write() —— 使用分布式锁保护写入（高并发安全）.
     */
    public function write(string $sessionId, string $data): bool
    {
        // 如果不启用显式锁，直接写（setex）
        if (! $this->locking) {
            try {
                if ($this->ttlLocal > 0) {
                    return (bool) $this->redis->setex($this->sessionKey($sessionId), $this->ttlLocal, $data);
                }
                return (bool) $this->redis->set($this->sessionKey($sessionId), $data);
            } catch (\Throwable $e) {
                error_log('[RedisGroupSessionHandler] write error: ' . $e->getMessage());
                return false;
            }
        }

        // 启用锁：获取 token
        $token = $this->acquireLock($sessionId);
        if ($token === false) {
            // 失败则返回 false（session_write_close 会报错），但我们尽量减少这种情况
            error_log('[RedisGroupSessionHandler] acquireLock failed for session ' . $sessionId);
            return false;
        }

        // 拿到锁后写入
        try {
            if ($this->ttlLocal > 0) {
                $ok = $this->redis->setex($this->sessionKey($sessionId), $this->ttlLocal, $data);
            } else {
                $ok = $this->redis->set($this->sessionKey($sessionId), $data);
            }
        } catch (\Throwable $e) {
            error_log('[RedisGroupSessionHandler] write error after lock: ' . $e->getMessage());
            $ok = false;
        }

        // 释放锁（尽量释放，即使写入失败也要释放）
        try {
            $this->releaseLock($sessionId, $token);
        } catch (\Throwable $e) {
            // 忽略释放异常
        }

        return (bool) $ok;
    }

    /**
     * read 保持简单与健壮.
     */
    public function read(string $sessionId): string
    {
        try {
            $v = $this->redis->get($this->sessionKey($sessionId));
            return $v ?: '';
        } catch (\Throwable $e) {
            error_log('[RedisGroupSessionHandler] read error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * destroy.
     */
    public function destroy(string $sessionId): bool
    {
        try {
            $this->redis->del($this->sessionKey($sessionId));
        } catch (\Throwable $e) {
            error_log('[RedisGroupSessionHandler] destroy error: ' . $e->getMessage());
        }
        return true;
    }

    /**
     * gc: Redis TTL 自管理.
     */
    public function gc(int $maxlifetime): false|int
    {
        return 0;
    }

    /**
     * make session key.
     */
    protected function sessionKey(string $sessionId): string
    {
        // Symfony 的 parent 默认把 prefix 放在 session id 前
        return $this->prefix . $sessionId;
    }

    /**
     * lock key for this session id.
     */
    protected function lockKey(string $sessionId): string
    {
        // 保持与其他实现区分，使用 prefix + 'lock:' + id
        return $this->prefix . 'lock:' . $sessionId;
    }

    /**
     * Acquire lock using SET NX PX, return token string on success, false on fail.
     */
    protected function acquireLock(string $sessionId): false|string
    {
        $lockKey = $this->lockKey($sessionId);
        $token   = uniqid('', true);

        // 计算最大自旋次数：我们等待一次 spinLockWait，每次尝试一次
        $maxAttempts = 1; // at least 1
        // We'll try until we acquire; but to avoid infinite loop, limit attempts:
        $maxAttempts = (int) max(1, ceil(3000000 / max(1, $this->spinLockWait))); // cap ~3s total wait

        $attempt = 0;
        do {
            ++$attempt;
            // 使用 Redis::set with options ['nx','px' => ms]
            // ext-redis supports $redis->set($k,$v, ['nx','px' => $ms])
            try {
                $ok = $this->redis->set($lockKey, $token, ['nx', 'px' => $this->lockTtl]);
            } catch (\Throwable $e) {
                // 若底层出错，短暂休眠再试一把
                usleep((int) $this->spinLockWait);
                $ok = false;
            }

            if ($ok) {
                return $token;
            }

            // 没拿到锁 -> 自旋等待
            usleep((int) $this->spinLockWait);
        } while ($attempt < $maxAttempts);

        // 最后再尝试一次，若失败则返回 false
        try {
            $ok = $this->redis->set($lockKey, $token, ['nx', 'px' => $this->lockTtl]);
            if ($ok) {
                return $token;
            }
        } catch (\Throwable) {
            // ignore
        }

        return false;
    }

    /**
     * Release lock only if token matches (atomic via Lua).
     */
    protected function releaseLock(string $sessionId, string $token): bool
    {
        $lockKey = $this->lockKey($sessionId);
        $lua     = <<<'LUA'
if redis.call("get", KEYS[1]) == ARGV[1] then
    return redis.call("del", KEYS[1])
end
return 0
LUA;
        try {
            $res = $this->redis->eval($lua, [$lockKey, $token], 1);
            return (bool) $res;
        } catch (\Throwable $e) {
            // 如果 eval 不可用，做最简单的 fallback（非原子）
            try {
                $current = $this->redis->get($lockKey);
                if ($current === $token) {
                    $this->redis->del($lockKey);
                    return true;
                }
            } catch (\Throwable) {
                // ignore
            }
            return false;
        }
    }
}
