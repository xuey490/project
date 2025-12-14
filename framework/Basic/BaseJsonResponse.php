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

/*
{
  "code": 0,        // 0 = 成功，非 0 = 业务失败
  "msg": "ok",
  "data": {}
}

*/

class BaseJsonResponse extends JsonResponse
{
    /**
     * 业务成功
     */
    public static function success(
        mixed $data = [],
        string $msg = 'ok'
    ): static {
        return new static([
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
        ], self::HTTP_OK);
    }

    /**
     * 业务失败（仍然是 200）
     */
    public static function fail(
        string $msg = 'fail',
        mixed $data = [],
        int $code = 1
    ): static {
        return new static([
            'code' => $code, // 业务错误码（非 0）
            'msg'  => $msg,
            'data' => $data,
        ], self::HTTP_OK);
    }

    /**
     * HTTP 异常（权限 / 系统）
     */
    public static function error(
        string $msg,
        int $httpStatus = self::HTTP_INTERNAL_SERVER_ERROR,
        int $code = 1
    ): static {
        return new static([
            'code' => $code,
            'msg'  => $msg,
            'data' => [],
        ], $httpStatus);
    }

    /**
     * 未认证（JWT 失效 / 未登录）
     */
    public static function unauthorized(
        string $msg = 'Unauthenticated'
    ): static {
        return new static([
            'code' => 1,
            'msg'  => $msg,
            'data' => [],
        ], self::HTTP_UNAUTHORIZED);
    }

    /**
     * CSRF 校验失败
     */
    public static function csrfExpired(
        string $msg = 'CSRF token mismatch'
    ): static {
        return new static([
            'code' => 1,
            'msg'  => $msg,
            'data' => [],
        ], self::HTTP_FORBIDDEN);
    }
}
