<?php

declare(strict_types=1);

namespace Framework\Attributes;

use Attribute;

/**
 * #[Permission]
 * 用于定义控制器或方法的权限标识。
 *
 * 示例：
 * #[Permission(['core:user:index'])]
 * #[Permission(['core:user:index', 'core:dept:index'], mode: 'OR')]
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Permission
{
    /**
     * @param array<string> $slugs 允许访问的权限标识列表
     * @param string $mode 匹配模式：'OR' (满足其一) 或 'AND' (必须全部满足)
     */
    public function __construct(
        public array $slugs = [],
        public string $mode = 'OR'
    ) {
        $this->mode = strtoupper($this->mode);
    }
}
