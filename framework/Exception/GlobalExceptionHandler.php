<?php

declare(strict_types=1);

namespace Framework\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Exception\HttpExceptionInterface;
use Throwable;

class GlobalExceptionHandler
{
    public function handle(Throwable $e): JsonResponse
    {
        // 1. Symfony HttpException（如 404 / 403）
        if ($e instanceof HttpExceptionInterface) {
            return new JsonResponse([
                'code' => $e->getStatusCode(),
                'msg'  => $e->getMessage(),
                'data' => []
            ], $e->getStatusCode());
        }

        // 2. 自定义业务异常
        if ($e instanceof ServiceException) {
            return new JsonResponse([
                'code' => $e->getCode() ?: 1,
                'msg'  => $e->getMessage(),
                'data' => []
            ], 200);
        }

        // 3. 未知异常 => 系统错误
        // 也可以在这里记录日志
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());

        return new JsonResponse([
            'code' => 500,
            'msg'  => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}
