<?php

namespace App\Models;

class SysRole extends BaseModel
{
    protected $table = 'sys_role';
    protected $primaryKey = 'id';
    protected $fillable = ['role_name', 'role_key', 'role_sort', 'data_scope', 'enabled', 'del_flag', 'created_by', 'updated_by', 'remark'];
    
    public function menus()
    {
        return $this->belongsToMany(SysMenu::class, 'sys_role_menu', 'role_id', 'menu_id');
    }
    
    public function depts()
    {
        return $this->belongsToMany(SysDept::class, 'sys_role_dept', 'role_id', 'dept_id');
    }
    
    public function users()
    {
        return $this->belongsToMany(SysUser::class, 'sys_user_role', 'role_id', 'user_id');
    }
}
