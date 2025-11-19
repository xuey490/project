<?php

declare(strict_types=1);

/**
 * This file is part of NavaFrame Framework.
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

#[Attribute(Attribute::TARGET_METHOD)]
abstract class BaseMapping
{
    public function __construct(
        public string $path,
        public array  $methods = [],
        public ?bool  $auth = null,
        public array  $roles = [],
        public array  $middleware = [],
        public array  $defaults = [],
        public array  $requirements = [],
        public array  $schemes = [],
        public ?string $host = null,
        public ?string $name = null
    ) {}
}