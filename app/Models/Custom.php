<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Models;

#use Framework\Basic\BaseTpORMModel;
#use Framework\Basic\BaseLaORMModel;

class Custom extends \Framework\Utils\BaseModel
{
    /**
     * 数据表主键
     *
     * @var string
     */
    protected $pk = 'id';
	
    // 表名、字段等设置继承自基类或自动识别
    //protected $name = 'sys_admin'; //for tp
	
	protected $name = 'custom'; 

    // 封装状态为1的用户分页
    public static function activeUsersPaginate($perPage = 10)
    {
        return self::where('status', 1)
                   ->orderBy('created_at', 'desc')
                   ->orderBy('id', 'desc')
                   ->cursorPaginate($perPage);
    }
}