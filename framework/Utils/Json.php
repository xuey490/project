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

use Symfony\Component\HttpFoundation\Response;

class Json
{
    private static int $code = 200;

    /**
     * 统一构造 JSON 响应（基于 Symfony Response）
     */
    public static function make(int $code, string $msg, ?array $data = null, ?array $replace = []): Response
    {
        $res = compact('code', 'msg');

        if (!is_null($data)) {
            $res['data'] = $data;
        }

        // 若 msg 是数字，等价于 code
        if (is_numeric($res['msg'])) {
            $res['code'] = (int)$res['msg'];
            $res['msg']  = (string)$res['msg'];
        }

        // 默认 HTTP 状态码
        $defaultHttpCode = self::$code;

        // 若 code 是合法 HTTP code，则使用它作为 HTTP 状态码
        if (!in_array($code, [-1, 0, 200, 400]) && $code >= 100 && $code < 600) {
            $defaultHttpCode = $code;
        }

        return new Response(
            json_encode($res, JSON_UNESCAPED_UNICODE),
            $defaultHttpCode,
            ['Content-Type' => 'application/json']
        );
    }

    /**
     * 业务成功
     */
    public static function success($msg = 'success', ?array $data = [], ?array $replace = []): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg  = 'success';
        }

        if (is_array($data)) {
            $data = self::convertLongNumbersToString($data);
        }

        return self::make(0, $msg, $data, $replace);
    }

    /**
     * 业务失败
     */
    public static function fail($msg = 'fail', ?array $data = null, int|string $code = -1, ?array $replace = []): Response
    {
        if (is_array($msg)) {
            $data = $msg;
            $msg  = 'fail';
        }

        return self::make((int)$code, $msg, $data, $replace);
    }

    /**
     * 通用状态包装
     */
    public static function status($status, $msg, $result = []): Response
    {
        $status = strtoupper($status);

        if (is_array($msg)) {
            $result = $msg;
            $msg    = 'success';
        }

        return self::success($msg, compact('status', 'result'));
    }

    /**
     * 避免 long 型雪花 ID 转换丢失精度（自动转字符串）
     */
    public static function convertLongNumbersToString($array): mixed
    {
        foreach ($array as &$value) {

            if (is_array($value)) {
                $value = self::convertLongNumbersToString($value);

            } elseif (is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $value = self::convertLongNumbersToString($value->toArray());
                }

            } elseif (is_numeric($value) && strlen((string)$value) > 15) {
                // 长整型 → 转字符串
                $value = (string)$value;
            }
        }

        return $array;
    }
}
