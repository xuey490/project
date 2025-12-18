<?php

declare(strict_types=1);

namespace Framework\DI\Attribute;

use Attribute;

class Scope
{
    public const SINGLETON = 'singleton'; // 单例
    public const PROTOTYPE = 'prototype'; // 多例
}
