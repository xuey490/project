<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

// src/Attributes/Auth.php

namespace Framework\Attributes;

/**
 * @Auth
 * 用于声明需要登录验证和角色控制的控制器/方法。
 *
 * 示例：
 * #[Auth]
 * #[Auth(roles: ['admin', 'editor'])]
 * #[Auth(required: false)] // 可选认证
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Auth
{
    /**
     * @param array<string> $roles    允许访问的角色列表
     * @param bool          $required 是否强制要求认证（false 表示匿名也能访问）
     * @param bool          $refresh  是否自动续期 JWT（默认 true）
     */
    public function __construct(
        public array $roles = [],
        public bool $required = true,
        public bool $refresh = true
    ) {}
}
