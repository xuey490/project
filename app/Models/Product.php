<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends \Framework\Utils\BaseModel
{
    use SoftDeletes;

    protected $table = 'products';
	
    // 定义允许批量赋值和作为搜索条件的字段
    protected $fillable = ['name', 'category_id', 'price', 'stock', 'sales', 'status', 'is_hot'];

    // 必须实现的方法（如果你的 factory 依赖 getFields）
    public function getFields(?string $field  =null ): array 
    {
        return $this->fillable;
    }

    /**
     * 定义搜索器：热门商品
     * 对应搜索参数: ['hot' => 1]
     */
    public function scopeHot($query, $value)
    {
        if ($value) {
            return $query->where('is_hot', 1);
        }
        return $query;
    }

    /**
     * 定义搜索器：价格区间
     * 对应搜索参数: ['price_range' => [100, 500]]
     */
    public function scopePriceRange($query, $value)
    {
        if (is_array($value) && count($value) === 2) {
            return $query->whereBetween('price', $value);
        }
        return $query;
    }
}