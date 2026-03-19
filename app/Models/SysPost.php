<?php

namespace App\Models;

class SysPost extends BaseModel
{
    protected $table = 'sys_post';
    protected $primaryKey = 'id';
    protected $fillable = ['post_code', 'post_name', 'post_sort', 'enabled', 'del_flag', 'created_by', 'updated_by', 'remark'];
    
    public function users()
    {
        return $this->belongsToMany(SysUser::class, 'sys_user_post', 'post_id', 'user_id');
    }
}
