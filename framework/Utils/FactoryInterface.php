<?php
declare(strict_types=1);

namespace Framework\Utils;

/**
 * 工厂接口，所有工厂类都必须实现 __invoke()
 */
interface FactoryInterface
{
    /**
     * 工厂调用创建实例
     */
    public function __invoke(): mixed;
}
