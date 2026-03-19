<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SysUser extends BaseModel
{
    protected $table = 'sys_user';
    protected $primaryKey = 'id';
    
    protected $hidden = ['password'];
    
    public function dept(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'dept_id', 'id');
    }
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(SysRole::class, 'sys_user_role', 'user_id', 'role_id');
    }
    
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(SysPost::class, 'sys_user_post', 'user_id', 'post_id');
    }
}
