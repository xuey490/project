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

namespace Framework\Utils;

use DateTimeImmutable;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
// 校验token
use Lcobucci\JWT\Validation\Constraint\SignedWith;


class JwtFactory
{
    protected Configuration $config;

    protected array $jwtConfig;

    protected \DateTimeZone $timezone;

    public function __construct()
    {
        $this->jwtConfig = config('jwt');
        $this->timezone  = new \DateTimeZone(config('app.time_zone') ?? 'Asia/Shanghai');
        $this->config    = $this->buildConfiguration();
    }

    /*
     * 签发jwt token
     */
    /*
     * 签发jwt token
     * 返回一个包含 token 字符串和过期时间的数组，便于外部处理（如设置 Cookie）
     */
    public function issue(array $claims = [], ?int $ttl = null): array
    {
        $now       = new \DateTimeImmutable('now', $this->timezone);
        $ttl       = $ttl ?? ($this->jwtConfig['ttl'] ?? 3600);
        $expiresAt = $now->modify("+{$ttl} seconds");

        $userId = $claims['uid'] ?? null;

        // 单点登录：清理并踢下线
        if ($userId && ($this->jwtConfig['single_device_login'] ?? false)) {
            $this->cleanExpiredTokens((int) $userId);
            $this->revokeAllForUser((int) $userId);
        }

        $jti = bin2hex(random_bytes(16));

        $builder = $this->config->builder()
            ->permittedFor($this->jwtConfig['audience'] ?? null)
            ->identifiedBy($jti)
            ->issuedBy($this->jwtConfig['issuer'] ?? null)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->withHeader('typ', 'JWT');

        foreach ($claims as $key => $value) {
            $builder = $builder->withClaim($key, $value);
        }

        $token    = $builder->getToken($this->config->signer(), $this->config->signingKey());
        $tokenStr = $token->toString();

        if ($userId) {
            $redis = app('redis.client');
            // token -> user_id 映射（用于快速查询）
            $redis->setex("login:token:{$jti}", $ttl, (string) $userId);
            // 将 jti 加入用户活跃列表
            $redis->sadd("user:active_tokens:{$userId}", $jti);
            // 可选：给这个 set 也设个稍长的 TTL（如果需要）
        }

        // 返回 token 字符串和过期时间，让外部决定如何传输
        return [
            'token'     => $tokenStr,
            'expiresAt' => $expiresAt, // DateTimeImmutable 实例
            'ttl'       => $ttl,
        ];
    }

    /*
     * 解析jwt token
     */
    public function parse(string $token): Plain
    {
        $token = trim($token);

        if (substr_count($token, '.') !== 2 || ! preg_match('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/', $token)) {
            throw new \InvalidArgumentException('Invalid JWT format.');
        }

        $parsed = $this->config->parser()->parse($token);

        // 先：检查是否被加入黑名单（优先）
        if ($this->isBlacklisted($parsed)) {
            throw new \RuntimeException('Token has been revoked.');
        }

        // 额外：检查是否在 Redis 中存在（即未被提前注销）
        $jti = $parsed->claims()->get('jti');
        if ($jti && ! app('redis.client')->exists("login:token:{$jti}")) {
            throw new \RuntimeException('Token not active or already expired.');
        }

        // 通过 jti 查 user_id（可选用以返回或验证用户）
        $userId = $jti ? app('redis.client')->get("login:token:{$jti}") : null;
        if (! $userId) {
            // 如果没有映射且未被列入黑名单，上面已经处理；这里再保守一点抛错。
            throw new \RuntimeException('Token invalid or expired.');
        }

        $verify = [
            new SignedWith($this->config->signer(), $this->config->verificationKey()),
        ];

        // ✅ 验证签名是否合法
        $ok = $this->config->validator()->validate($parsed, ...$verify);
        if (! $ok) {
            throw new \Exception('Token verfiy failed');
        }

        // 验证过期时间
        $exp = $parsed->claims()->get('exp', null);
        if ($exp instanceof \DateTimeImmutable && $exp < new \DateTimeImmutable()) {
            throw new \Exception('Token was expired');
        }

        // SystemClock 需要时区
        $clock = new SystemClock(new \DateTimeZone(config('app.time_zone') ?? 'Asia/Shanghai'));

        $constraints = [
            new IssuedBy($this->jwtConfig['issuer'] ?? null),
            new LooseValidAt(
                $clock,
                new \DateInterval('PT' . intval($this->jwtConfig['blacklist_grace_period'] ?? 0) . 'S')
            ),
        ];

        $this->config->validator()->assert($parsed, ...$constraints);

        return $parsed;
    }

    /*
     * 刷新jwt token
     */
    public function refresh(string $token, ?int $ttl = null): string
    {
        $parsed = $this->parse($token);

        $iat = $parsed->claims()->get('iat'); // DateTimeImmutable expected
        if (! $iat instanceof \DateTimeImmutable) {
            throw new \RuntimeException('Invalid iat claim.');
        }

        $refreshTtl   = intval($this->jwtConfig['refresh_ttl'] ?? 0);
        $refreshExp   = $iat->getTimestamp() + $refreshTtl;
        $nowTimestamp = (new \DateTimeImmutable('now', $this->timezone))->getTimestamp();

        if ($nowTimestamp > $refreshExp) {
            throw new \RuntimeException('Token cannot be refreshed: refresh TTL expired.');
        }

        $claims = [];
        foreach ($parsed->claims()->all() as $name => $value) {
            if (! in_array($name, ['iss', 'iat', 'exp', 'nbf', 'jti'], true)) {
                $claims[$name] = $value;
            }
        }

        return $this->issue($claims, $ttl);
    }

    /*
     * 注销用户或所有用户的Token（踢下线）
     */
    public function revokeAllForUser(int $userId): void
    {
        $redis = app('redis.client');
        $jtis  = $redis->smembers("user:active_tokens:{$userId}");

        if (! empty($jtis)) {
            $keys = array_map(fn ($jti) => "login:token:{$jti}", $jtis);
            // 删除所有 token key（如果很多，注意 Redis 参数限制）
            $redis->del(...$keys);
        }

        // 清空用户 token 集合
        $redis->del("user:active_tokens:{$userId}");

        // 清理 cookie
        // app('cookie')->forget('token');
    }

    // 优化方案：在 issue() 中，单点登录前清理（或定期清理）
    public function cleanExpiredTokens(int $userId): void
    {
        $redis = app('redis.client');
        $jtis  = $redis->smembers("user:active_tokens:{$userId}");

        $validJtis = [];
        foreach ($jtis as $jti) {
            if ($redis->exists("login:token:{$jti}")) {
                $validJtis[] = $jti;
            }
        }

        if (count($validJtis) !== count($jtis)) {
            $redis->del("user:active_tokens:{$userId}");
            if (! empty($validJtis)) {
                $redis->sadd("user:active_tokens:{$userId}", ...$validJtis);
            }
        }
    }

    public function revoke(string $token): void
    {
        $parsed = $this->parse($token);
        if (! $parsed) {
            throw new \RuntimeException('Token parse failed, cannot revoke .');
        }

        $jti    = $parsed->claims()->get('jti');
        $userId = $parsed->claims()->get('uid');

        if (! $jti) {
            throw new \RuntimeException('Token missing jti claim, cannot revoke.');
        }

        $redis = app('redis.client');
        // 1. 从 Redis 删除登录映射（无论黑名单是否开启，都要踢下线）
        $redis->del("login:token:{$jti}");
        if ($userId) {
            $redis->srem("user:active_tokens:{$userId}", $jti);
        }

        // 2. 仅当黑名单开启时，加入 Redis 黑名单（防重放）
        if (! empty($this->jwtConfig['blacklist_enabled'])) {
            $exp = $parsed->claims()->get('exp'); // DateTimeImmutable expected
            if ($exp instanceof \DateTimeImmutable) {
                $expTimestamp = $exp->getTimestamp();
                $nowTimestamp = (new \DateTimeImmutable('now', $this->timezone))->getTimestamp();

                // 计算剩余有效秒数并加上 grace period（保证在 grace 期内也会被拒绝）
                $grace = intval($this->jwtConfig['blacklist_grace_period'] ?? 0);
                $ttl   = max(0, $expTimestamp - $nowTimestamp + $grace);

                if ($ttl > 0) {
                    $this->setBlacklist($jti, $ttl);
                } else {
                    // 已过期：仍可短时间内设置一个小 TTL，避免 race condition
                    $this->setBlacklist($jti, max(60, $grace));
                }
            } else {
                // 如果没有 exp，还是加入一个短期黑名单以防止重放
                $this->setBlacklist($jti, max(60, intval($this->jwtConfig['blacklist_grace_period'] ?? 60)));
            }
        }
    }

    public function getPayload(string $token): array
    {
        $parsed = $this->parse($token);
        return $parsed->claims()->all();
    }

    protected function buildConfiguration(): Configuration
    {
        $algo   = $this->jwtConfig['algo']   ?? 'HS256';
        $secret = $this->jwtConfig['secret'] ?? '';

        $signer = match ($algo) {
            'HS256' => new Sha256(),
            'HS384' => new Sha384(),
            'HS512' => new Sha512(),
            'RS256' => new RsaSha256(),
            default => throw new \InvalidArgumentException("Unsupported algorithm: {$algo}")
        };

        // 对称签名（HMAC）
        if (in_array($algo, ['HS256', 'HS384', 'HS512'], true)) {
            $signingKey = InMemory::plainText($secret);
            // 对称签名使用 forSymmetricSigner
            return Configuration::forSymmetricSigner($signer, $signingKey);
        }

        // 非对称签名（RSA）
        if ($algo === 'RS256') {
            $privateKeyPath = storage_path('keys/private.key');
            $publicKeyPath  = storage_path('keys/public.key');

            $private = InMemory::file($privateKeyPath);
            $public  = InMemory::file($publicKeyPath);

            return Configuration::forAsymmetricSigner($signer, $private, $public);
        }

        // 一般不会到这里
        throw new \InvalidArgumentException("Unsupported algorithm: {$algo}");
    }

    /**
     * 将 jti 写入 Redis 黑名单，使用 SETEX.
     */
    protected function setBlacklist(string $jti, int $ttl): void
    {
        if (empty($jti) || $ttl <= 0) {
            return;
        }
        app('redis.client')->setex("jwt_blacklist:{$jti}", $ttl, '1');
    }

    protected function isBlacklisted(Plain $token): bool
    {
        if (empty($this->jwtConfig['blacklist_enabled'])) {
            return false;
        }

        $jti = $token->claims()->get('jti');
        if (! $jti) {
            return false;
        }

        return (bool) app('redis.client')->exists("jwt_blacklist:{$jti}");
    }
}
