<?php

declare(strict_types=1);

namespace Framework\Attributes;

use Attribute;
use App\Middlewares\ValidateMiddleware;

/**
 * @Validate
 * 参数校验注解
 *
 * 示例：
 * #[Validate(UserValidator::class)] // 使用默认场景
 * #[Validate(UserValidator::class, scene: 'login')] // 指定场景
 * #[Validate(UserValidator::class, batch: true)] // 批量验证(返回所有错误)
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Validate implements MiddlewareProviderInterface
{
    /**
     * @param string      $validator 验证器类名 (需继承 Framework\Validation\Validate)
     * @param string|null $scene     验证场景 (可选)
     * @param bool        $batch     是否批量验证 (默认为false，一旦出错立即返回)
     */
    public function __construct(
        public string $validator,
        public ?string $scene = null,
        public bool $batch = false
    ) {}

    public function getMiddleware(): string|array
    {
        return ValidateMiddleware::class;
    }
}