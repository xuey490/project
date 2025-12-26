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
    protected $name = 'custom'; 

    // 2. 主键
    protected $pk = 'id';

    // 3. 覆盖基类的时间字段名（核心） 这里定义后会直接覆盖掉BaseTpORMModel基类
    protected $createTime = 'created_at';  // 映射到数据库 created_at
    protected $updateTime = 'updated_at';  // 映射到数据库 updated_at

    // 4. 字段类型定义（强制转换，适配int）
    protected $type = [
        'status'     => 'integer',
        'vip_level'  => 'integer',
        'group_id'   => 'integer',
        'balance'    => 'float',
        'created_at' => 'integer', // 匹配数据库 int(11)
        'updated_at' => 'integer', // 匹配数据库 int(11)
        'tenant_id'  => 'integer',
        'created_by' => 'integer',
    ];

    // 5. 覆盖只读字段（添加自定义时间字段）
    protected $readonly = ['created_by', 'created_at', 'tenant_id'];

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