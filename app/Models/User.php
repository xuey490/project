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

	protected string $pkGenerateType = 'snowflake';//修改为自增
	
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