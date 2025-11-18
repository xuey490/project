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
class PostMapping extends BaseMapping
{
    public function __construct(
        string $path,
        ?bool  $auth = null,
        array  $roles = [],
        array  $middleware = []
    ) {
        parent::__construct(
            path: $path,
            methods: ['POST'],
            auth: $auth,
            roles: $roles,
            middleware: $middleware
        );
    }
}
