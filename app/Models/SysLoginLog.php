<?php

namespace App\Models;

class SysLoginLog extends BaseModel
{
    protected $table = 'sys_login_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
}

