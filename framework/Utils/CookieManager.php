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

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/*
$response = app('response')->setContent('Hello FSSPHP!');

//app('cookie')->queueCookie('token', $this->tokenString, 3600);
//app('cookie')->queueCookie('token111', 'oooooo', 3600);


// 在发送 Response 前统一绑定队列中的 Cookie
//app('cookie')->sendQueuedCookies($response);

// 快捷设置 Cookie
#app('cookie')->setResponseCookie($response, 'token', $this->tokenString , 3600);

// 快捷删除 Cookie
//app('cookie')->forgetResponseCookie($response, 'old_cookie');

return $response;
*/

class CookieManager
{
    protected array $config;

    protected string $secret;

    protected string $cipher;

    protected string $domain;

    protected string $path;

    protected int $expire;

    protected bool $secure;

    protected bool $httponly;

    protected string $samesite;

    protected bool $encrypt;

    protected array $queuedCookies = [];

    public function __construct(?string $configPath = null)
    {
        $configPath = $configPath ?? __DIR__ . '/../../config/cookie.php';

        if (! file_exists($configPath)) {
            throw new \RuntimeException("Cookie config not found: {$configPath}");
        }

        $this->config = require $configPath;

        $this->secret   = (string) ($this->config['secret'] ?? '');
        if (strlen($this->secret) < 16) {
            throw new \RuntimeException('Cookie secret must be at least 16 characters.');
        }

        $this->cipher   = $this->config['cipher'] ?? 'AES-256-CBC';
        $this->domain   = $this->config['domain'] ?? '';
        $this->path     = $this->config['path']   ?? '/';
        $this->expire   = (int) ($this->config['expire'] ?? 86400);
        $this->secure   = (bool) ($this->config['secure'] ?? false);
        $this->httponly = (bool) ($this->config['httponly'] ?? true);
        $this->samesite = $this->config['samesite'] ?? 'Lax';
        $this->encrypt  = (bool) ($this->config['encrypt'] ?? false);
    }

    // --------------------------------------
    // Cookie 队列
    // --------------------------------------

    public function queueCookie(string $name, string $value, ?int $expire = null): void
    {
        $payload = $this->encodePayload($value);
        $expire  = time() + ($expire ?? $this->expire);

        $this->queuedCookies[] = compact('name', 'payload', 'expire');
    }

    public function queueForgetCookie(string $name): void
    {
        $this->queuedCookies[] = [
            'name'   => $name,
            'payload'=> '',
            'expire' => time() - 3600,
        ];
    }

    public function sendQueuedCookies(?Response $response = null): void
    {
        foreach ($this->queuedCookies as $c) {
            if ($response instanceof Response) {
                $cookie = Cookie::create(
                    $c['name'],
                    $c['payload'],
                    $c['expire'],
                    $this->path,
                    $this->domain ?: null,
                    $this->secure,
                    $this->httponly,
                    false,
                    ucfirst($this->samesite)
                );
                $response->headers->setCookie($cookie);
            } else {
                // Workerman / FPM 原生
                setcookie(
                    $c['name'],
                    $c['payload'],
                    [
                        'expires'  => $c['expire'],
                        'path'     => $this->path,
                        'domain'   => $this->domain,
                        'secure'   => $this->secure,
                        'httponly' => $this->httponly,
                        'samesite' => ucfirst($this->samesite),
                    ]
                );
                $_COOKIE[$c['name']] = $c['payload'];
            }
        }

        $this->queuedCookies = [];
    }

    // --------------------------------------
    // Response API
    // --------------------------------------

    public function setResponseCookie(Response $response, string $name, string $value, ?int $expire = null): void
    {
        $payload = $this->encodePayload($value);
        $cookie  = Cookie::create(
            $name,
            $payload,
            time() + ($expire ?? $this->expire),
            $this->path,
            $this->domain ?: null,
            $this->secure,
            $this->httponly,
            false,
            ucfirst($this->samesite)
        );
        $response->headers->setCookie($cookie);
    }

    public function forgetResponseCookie(Response $response, string $name): void
    {
        $cookie = Cookie::create(
            $name,
            '',
            time() - 3600,
            $this->path,
            $this->domain ?: null,
            $this->secure,
            $this->httponly,
            false,
            ucfirst($this->samesite)
        );
        $response->headers->setCookie($cookie);
    }

    // --------------------------------------
    // 读取 Cookie
    // --------------------------------------

    public function get(Request $request, string $name): ?string
    {
        $raw = $request->cookies->get($name);
        if (! $raw) {
            return null;
        }

        return $this->decodePayload($raw);
    }

    // --------------------------------------
    // 加密 + 签名 + 封装
    // --------------------------------------

    protected function encodePayload(string $value): string
    {
        $data = $this->encrypt ? $this->encryptValue($value) : $value;
        $sig  = $this->sign($data);

        $json = json_encode(['data' => $data, 'sig' => $sig], JSON_UNESCAPED_SLASHES);

        // 全流程统一 URL-safe Base64
        return $this->base64url_encode($json);
    }

    protected function decodePayload(string $payload): ?string
    {
        $json = $this->base64url_decode($payload);
        if (! $json) {
            return null;
        }

        $arr = json_decode($json, true);
        if (! is_array($arr) || ! isset($arr['data'], $arr['sig'])) {
            return null;
        }

        if (! $this->verify($arr['data'], $arr['sig'])) {
            return null;
        }

        return $this->encrypt ? $this->decryptValue($arr['data']) : $arr['data'];
    }

    // ------------------------ 内部加密/签名方法 ------------------------
    /*
    // 加密（AES-128-GCM）
    protected function encryptValue(string $value): string
    {
        $ivLen = 12; // GCM 推荐 IV 长度为 12 字节
        $iv = random_bytes($ivLen);
        $tagLen = 16; // GCM 认证标签长度
        $ciphertext = openssl_encrypt(
            $value,
            $this->cipher,
            $this->secret,
            OPENSSL_RAW_DATA,
            $iv,
            $tag // 生成认证标签
        );
        // 拼接 IV + 密文 + 标签（标签用于解密时验证）
        return $this->base64url_encode($iv . $ciphertext . $tag);
    }

    // 解密（AES-128-GCM）
    protected function decryptValue(string $encoded): string
    {
        $data = $this->base64url_decode($encoded);
        $ivLen = 12;
        $tagLen = 16;
        $iv = substr($data, 0, $ivLen);
        $tag = substr($data, -$tagLen);
        $ciphertext = substr($data, $ivLen, -$tagLen);

        $decrypted = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $this->secret,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        return $decrypted ?: '';
    }
    */

    // --------------------------------------
    // 加密 / 解密
    // --------------------------------------

    protected function encryptValue(string $value): string
    {
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv    = random_bytes($ivLen);

        $ciphertext = openssl_encrypt(
            $value,
            $this->cipher,
            $this->secret,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $this->base64url_encode($iv . $ciphertext);
    }

    protected function decryptValue(string $encoded): string
    {
        $data  = $this->base64url_decode($encoded);
        $ivLen = openssl_cipher_iv_length($this->cipher);

        $iv         = substr($data, 0, $ivLen);
        $ciphertext = substr($data, $ivLen);

        $plain = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $this->secret,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $plain ?: '';
    }

    // --------------------------------------
    // 签名
    // --------------------------------------

    protected function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->secret);
    }

    protected function verify(string $data, string $sig): bool
    {
        return hash_equals($this->sign($data), $sig);
    }

    // --------------------------------------
    // Base64 Url-safe
    // --------------------------------------

    protected function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64url_decode(string $data): string
    {
        $pad = 4 - (strlen($data) % 4);
        if ($pad < 4) {
            $data .= str_repeat('=', $pad);
        }
        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
