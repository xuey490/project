<?php

declare(strict_types=1);

namespace App\Models;

use Framework\Utils\BaseModel;

class Notice extends BaseModel
{
    protected $table = 'article';
    protected $pk = 'id';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}
