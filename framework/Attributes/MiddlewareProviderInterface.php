<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-17
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;

/**
 * 接口：中间件提供者
 * 任何实现了此接口的注解，都会被 Loader 自动提取并注册其对应的中间件。
 */
interface MiddlewareProviderInterface
{
    /**
     * 返回该注解关联的中间件类名 (或类名数组)
     * @return string|array
     */
    public function getMiddleware(): string|array;
}