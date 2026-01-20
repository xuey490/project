<?php
declare(strict_types=1);

namespace App\Dao;

use Framework\Basic\BaseDao;
use App\Models\CustomGroup;

/**
 * CustomGroup数据访问层
 * @extends BaseDao<CustomGroup>
 */
class CustomGroupDao extends BaseDao
{
	protected string $modelClass = CustomGroup::class;
	
    /**
     * 绑定模型类
     */
    protected function setModel(): string
    {
        return CustomGroup::class;
    }
}