<?php
declare(strict_types=1);

namespace Framework\Utils;


/**
 * ORM 模型工厂接口
 */
interface ModelFactoryInterface
{
    public function make(string $modelClass): mixed;
}
