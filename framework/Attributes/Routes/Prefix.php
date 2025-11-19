<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-18
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */
 
namespace Framework\Attributes\Routes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Prefix
{
    public function __construct(
        public string $prefix,
        public array  $middleware = [],
        public ?bool  $auth = null,
        public array  $roles = []
    ) {}
}
