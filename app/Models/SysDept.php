<?php

namespace App\Models;

class SysDept extends BaseModel
{
    protected $table = 'sys_dept';
    protected $primaryKey = 'id';
    protected $fillable = ['pid', 'ancestors', 'dept_name', 'order_num', 'leader', 'phone', 'email', 'enabled', 'del_flag', 'created_by', 'updated_by', 'remark'];
    
    public function children()
    {
        return $this->hasMany(SysDept::class, 'pid', 'id')->orderBy('order_num');
    }
    
    public function parent()
    {
        return $this->belongsTo(SysDept::class, 'pid', 'id');
    }
    
    public function roles()
    {
        return $this->belongsToMany(SysRole::class, 'sys_role_dept', 'dept_id', 'role_id');
    }
}
