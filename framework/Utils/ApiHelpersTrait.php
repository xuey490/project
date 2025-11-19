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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiHelpersTrait
{
    /*
    protected function json(mixed $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }
    */

    /**
     * 返回成功响应.
     *
     * @param array|object $data
     */
    protected function success(mixed $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return $this->json(['success' => true, 'data' => $data], $status, $headers);
    }

    /**
     * 返回错误响应.
     */
    protected function error(string $message, int $status = 400, ?array $details = null, array $headers = []): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error'   => array_filter([
                'message' => $message,
                'code'    => $status,
                'details' => $details,
            ]),
        ], $status, $headers);
    }

    /**
     * 直接返回 JSON（不包装 success/data）.
     */
    protected function json(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        return new JsonResponse($data, $status, $headers);
    }
}
