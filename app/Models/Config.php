<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Model;
use \Framework\Utils\BaseModel;

class Config extends BaseModel
{
    // 移除这行，让 ORM 自动根据模型类名推断表名并应用前缀
	//如果thinkphp，这是用$name 不用表前缀，$table必须有表前缀，$table='oa_config';
	//如果Illuminate，必须$table='config' 用$name 直接报错;
    protected $name = 'config';

    protected $pk = 'id'; // 主键名称

    // 可选：如果你需要自定义一些配置
    protected $autoWriteTimestamp = true;

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';
}
