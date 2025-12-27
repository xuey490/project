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
	
	
    /**
     * 允许批量赋值的字段白名单
     * 需包含所有要通过 create/fill/save(批量) 赋值的字段
     *
     * @var array
     */
    protected $fillable = [
        'name',          // 异常提示需要添加的字段
        'nickname',      // 你的批量赋值数据中的字段
        'englishname',   // 你的批量赋值数据中的字段
        'email',         // 你的批量赋值数据中的字段
        'group_id',      // 你的批量赋值数据中的字段
        'status'         // 你的批量赋值数据中的字段
        // 可根据业务需求补充其他字段（如 created_by、updated_by 等）
    ];

    // 封装状态为1的用户分页
    public static function activeUsersPaginate($perPage = 10)
    {
        return self::where('status', 1)
                   ->orderBy('created_at', 'desc')
                   ->orderBy('id', 'desc')
                   ->cursorPaginate($perPage);
    }
}