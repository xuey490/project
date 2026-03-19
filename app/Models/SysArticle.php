<?php

namespace App\Models;

class SysArticle extends BaseModel
{
    protected $table = 'sys_article';
    // primaryKey is 'id' by default, which matches schema
    
    public function author()
    {
        return $this->belongsTo(SysUser::class, 'author_id', 'user_id');
    }
}
