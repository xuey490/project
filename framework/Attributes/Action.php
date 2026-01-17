<?php

declare(strict_types=1);

namespace Framework\Attributes;;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Action
{
    public function __construct(
        public array $methods = [],
        public bool $expose = true,
    ) {}
}