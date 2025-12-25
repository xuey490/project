<?php

declare(strict_types=1);

namespace App\Models;

use think\Model;
use think\model\relation\HasMany;

/**
 * ThinkPHP User Model
 * 
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $balance
 * @property int $status
 * @property int $vip_level
 * @property string $create_time
 * @property string $update_time
 */
class User extends \Framework\Utils\BaseModel
{
    // 1. 指定表名
    // ThinkPHP 默认将类名转蛇形，即 User => user。显式定义更安全。
    protected $name = 'admin_module'; 

    // 2. 主键
    protected $pk = 'id';

    // 3. 自动时间戳
    // 'datetime' 表示数据库存的是 Y-m-d H:i:s 格式
    // 如果是 'int' 表示存时间戳整数
    protected $autoWriteTimestamp = 'datetime';

    // ThinkPHP 默认时间字段是 create_time 和 update_time
    // 如果你的数据库是 created_at / updated_at (Laravel风格)，需要映射
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 4. 字段类型定义 (类似 Laravel 的 casts)
    protected $type = [
        'status'    => 'integer',
        'vip_level' => 'integer',
        'balance'   => 'float', // ThinkORM 没有 decimal:2 这种语法，通常用 float 或 string
    ];

    // 5. 批量赋值
    // ThinkORM 默认允许所有字段写入，可以通过 $field 定义允许的字段
    // 也可以不做限制，由 Repository 层控制
    // protected $field = ['username', 'email', ...];

    /**
     * 定义关联关系
     */
    public function orders(): HasMany
    {
        // hasMany('关联模型名', '外键', '主键')
        return $this->hasMany(Order::class, 'user_id', 'id');
    }
}