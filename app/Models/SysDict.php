<?php

namespace App\Models;

class SysDict extends BaseModel
{
    protected $table = 'sys_dict';
    protected $primaryKey = 'id';
    
    public function items()
    {
        return $this->hasMany(SysDictItem::class, 'dict_id', 'id')->orderBy('sort');
    }
}
