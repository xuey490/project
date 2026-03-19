<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DataScopeTrait;

class BaseModel extends Model
{
    use DataScopeTrait;
    
    public $timestamps = true;
    protected $guarded = [];
    
    // Default date format
    protected $dateFormat = 'Y-m-d H:i:s';
}
