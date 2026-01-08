<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 用户模型
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property int $tenant_id 多租户ID
 * @property \Carbon\Carbon $created_at
 */
class Users extends \Framework\Utils\BaseModel
{
    use SoftDeletes;

    // 表名（Laravel 会自动复数化，可手动指定）
    protected $name = 'users';

    // 主键
    protected $primaryKey = 'id';

    // 批量赋值白名单
    protected $fillable = [
        'username', 'email', 'password', 'tenant_id'
    ];

    // 隐藏字段
    protected $hidden = [
        'password'
    ];

    // 时间戳自动维护
    public $timestamps = true;

    // 日期字段格式化
    protected $dates = [
        'created_at', 'updated_at', 'deleted_at'
    ];
	

}

/*
<?php
declare(strict_types=1);

namespace App\Models;

use think\Model;
use think\model\concern\SoftDelete;


class User extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'users';

    // 主键
    protected $pk = 'id';

    // 批量赋值白名单
    protected $field = [
        'id', 'username', 'email', 'password', 'tenant_id', 'created_at', 'updated_at', 'deleted_at'
    ];

    // 软删除字段
    protected $deleteTime = 'deleted_at';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
}
*/