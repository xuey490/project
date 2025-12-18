<?php

declare(strict_types=1);

namespace Framework\DI;

final class ContextBag
{
    protected static array $data = [];

    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$data[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$data);
    }

    public static function clear(): void
    {
        self::$data = [];
    }
}