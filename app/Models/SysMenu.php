<?php

namespace App\Models;

class SysMenu extends BaseModel
{
    protected $table = 'sys_menu';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'pid', 'sort', 'path', 'component', 'type', 'is_show', 'enabled', 'code', 'icon', 'is_cache', 'is_affix', 'is_link', 'link_url', 'created_by', 'updated_by', 'remark'];
    
    public function children()
    {
        return $this->hasMany(SysMenu::class, 'pid', 'id')->orderBy('sort');
    }
    
    public function roles()
    {
        return $this->belongsToMany(SysRole::class, 'sys_role_menu', 'menu_id', 'role_id');
    }
}
