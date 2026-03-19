<?php

namespace App\Models;

class SysAccessLog extends BaseModel
{
    protected $table = 'sys_access_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
}

