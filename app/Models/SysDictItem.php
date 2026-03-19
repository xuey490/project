<?php

namespace App\Models;

class SysDictItem extends BaseModel
{
    protected $table = 'sys_dict_item';
    protected $primaryKey = 'id';
    
    public function dict()
    {
        return $this->belongsTo(SysDict::class, 'dict_id', 'id');
    }
}
