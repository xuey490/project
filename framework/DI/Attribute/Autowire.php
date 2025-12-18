<?php

declare(strict_types=1);

namespace Framework\DI\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Autowire
{
    /**
     * @param string $scope 作用域
     */
    public function __construct(
        public string $scope = Scope::SINGLETON
    ) {}
}
