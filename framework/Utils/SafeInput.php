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

use Symfony\Component\HttpFoundation\Request;

class SafeInput
{
    /**
     * 获取过滤后的 GET 参数.
     */
    public static function query(Request $request, ?string $key = null, mixed $default = null): mixed
    {
        $safe = $request->attributes->get('_safe_query', []);
        if ($key === null) {
            return $safe;
        }
        return $safe[$key] ?? $default;
    }

    /**
     * 获取过滤后的 POST 参数.
     */
    public static function request(Request $request, ?string $key = null, mixed $default = null): mixed
    {
        $safe = $request->attributes->get('_safe_request', []);
        if ($key === null) {
            return $safe;
        }
        return $safe[$key] ?? $default;
    }

    /**
     * 获取过滤后的 JSON 请求体.
     */
    public static function json(Request $request, ?string $key = null, mixed $default = null): mixed
    {
        $safe = $request->attributes->get('_safe_json', []);
        if ($key === null) {
            return $safe;
        }
        return $safe[$key] ?? $default;
    }

    /**
     * 获取所有安全输入（合并 query + request + json）
     * 注意：仅用于简单场景，可能有键名冲突
     */
    public static function all(Request $request): array
    {
        return array_replace(
            $request->attributes->get('_safe_json', []),
            $request->attributes->get('_safe_request', []),
            $request->attributes->get('_safe_query', [])
        );
    }
}
