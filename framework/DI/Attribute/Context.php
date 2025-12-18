<?php

declare(strict_types=1);

namespace Framework\DI\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Context
{
    /**
     * @param string $key 上下文中存储的键名 (如 'request', 'user')
     */
    public function __construct(
        public string $key
    ) {}
}