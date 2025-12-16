<?php

declare(strict_types=1);  // 启用严格类型模式

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%  // 文件名占位符
 * @Date: 2025-11-15      // 创建日期
 * @Developer: xuey863toy // 开发者
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Attributes;  // 定义命名空间

use Attribute;  // 引入PHP内置的Attribute类

/**
 * 完全兼容 Symfony 路由写法的 Attribute 路由定义类
 * ✅ 支持：path、methods、name、defaults、requirements、schemes、host
 * ✅ 扩展：prefix、group、middleware（控制器级继承）
 * ✅ 扩展：auth（是否需要认证）和 roles（允许的角色列表）.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]  // 定义注解属性，可应用于类、方法，且可重复
class Route
{
    public function __construct(  // 构造函数，定义各种路由参数
        public string $path = '',        // 路由路径，默认空字符串
        public array $methods = [],      // HTTP方法数组，默认空数组
        public ?string $name = null,     // 路由名称，默认null
        public array $defaults = [],     // 默认参数数组，默认空数组
        public array $requirements = [], // 参数要求数组，默认空数组
        public array $schemes = [],      // 协议方案数组，默认空数组
        public ?string $host = null,     // 主机名，默认null

        // ==== 扩展属性 ====
        public ?string $prefix = null,   // 路由前缀，默认null
        public ?string $group = null,    // 路由分组，默认null
        public array $middleware = [],   // 中间件数组，默认空数组

        // ==== 权限控制 ====
        public ?bool $auth = null,       // 认证要求，true需要认证，false不需要，null不设置
        public array $roles = [],        // 允许的角色列表，默认空数组
    ) {}
}