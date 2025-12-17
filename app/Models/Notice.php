<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Utils\BaseModel;

class Notice extends BaseModel
{
    protected $table = 'ma_sys_notice';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}
