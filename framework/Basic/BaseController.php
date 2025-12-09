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

use Symfony\Component\HttpFoundation\Request;
use Framework\Basic\BaseJsonResponse;

abstract class BaseController
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;

        // 留给子类扩展生命周期行为
        $this->initialize();
    }

    /**
     * 子类可覆盖
     */
    protected function initialize(): void
    {
    }

    /**
     * 返回成功 JSON
     */
    protected function success(mixed $data = [], string $msg = 'success'): BaseJsonResponse
    {
        return BaseJsonResponse::success($data, $msg);
    }

    /**
     * 返回失败 JSON
     */
    protected function fail(string $msg = 'error', int $code = 1): BaseJsonResponse
    {
        return BaseJsonResponse::fail($msg, $code);
    }

    /**
     * 服务端错误
     */
    protected function error(string $msg = 'server error'): BaseJsonResponse
    {
        return BaseJsonResponse::error($msg, 500);
    }
}
