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

namespace Framework\Basic;

use Symfony\Component\HttpFoundation\JsonResponse;

class BaseJsonResponse extends JsonResponse
{
    public static function success(mixed $data = [], string $msg = 'success', int $code = 0): static
    {
        return new static([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ], 200);
    }

    public static function fail(string $msg = 'error', int $code = 1, mixed $data = []): static
    {
        return new static([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data
        ], 200);
    }

    public static function error(string $msg, int $httpCode = 500): static
    {
        return new static([
            'code' => 1,
            'msg'  => $msg,
            'data' => []
        ], $httpCode);
    }
}
