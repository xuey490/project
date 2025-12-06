<?php

declare(strict_types=1);

namespace App\Models;

// 根据你选的 ORM 打开对应的基类注释
// use Illuminate\Database\Eloquent\Model; // Laravel
use think\Model; // ThinkPHP

class Order extends Model
{
    // Laravel 用 $table = 'orders';
    // ThinkPHP 用 protected $name = 'orders';
    protected $table = 'orders'; 
    protected $name  = 'orders';
}