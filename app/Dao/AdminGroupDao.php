<?php
declare(strict_types=1);

namespace App\Dao;

use Framework\Basic\BaseDao;
use App\Models\AdminGroup;

/**
 * AdminGroup数据访问层
 * @extends BaseDao<AdminGroup>
 */
class AdminGroupDao extends BaseDao
{
	protected string $modelClass = AdminGroup::class;
	
    /**
     * 绑定模型类
     */
    protected function setModel(): string
    {
        return AdminGroup::class;
    }
}