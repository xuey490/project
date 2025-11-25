<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Models;

use Framework\Basic\BaseTpORMModel;
use Framework\Basic\BaseLaORMModel;

class Custom extends BaseTpORMModel
{
    /**
     * 数据表主键
     *
     * @var string
     */
    protected $pk = 'id';
	
    // 表名、字段等设置继承自基类或自动识别
    protected $name = 'custom'; 
}