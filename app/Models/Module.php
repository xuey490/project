<?php
namespace App\Models;
use think\Model;

class Module extends \Framework\Utils\BaseModel
{
	
    protected $name = 'admin_module'; // 对应 module 表
	
    protected $autoWriteTimestamp = 'datetime';
    
    // 关联：用户组
    public function group() {
        //return $this->belongsTo(UserGroup::class, 'group_id');
    }
}