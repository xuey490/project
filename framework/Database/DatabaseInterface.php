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

namespace Framework\Database;

/**
 * ORM 模型工厂接口.
 */
interface DatabaseInterface
{
    /**
     * 像函数一样调用工厂 (语法糖)
     */
    public function __invoke(string $modelClass): mixed;

    /**
     * 创建模型实例或查询构造器
     */
    public function make(string $modelClass): mixed;
}