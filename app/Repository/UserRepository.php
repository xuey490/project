<?php

declare(strict_types=1);

namespace App\Repository;

use Framework\Repository\BaseRepository;

/**
 * 用户仓库
 * 继承 BaseRepository 获得所有标准 CRUD 能力
 */
class UserRepository extends BaseRepository
{
    // 指定该仓库操作的模型 (完整类名)
    protected string $modelClass = \App\Model\User::class;

    // 如果需要扩展特定的复杂业务逻辑，可以在这里写
    // 比如：查找活跃的 VIP 用户
    public function findActiveVips(int $level = 1)
    {
        // 获取原生查询构造器，自己处理复杂逻辑
        $query = $this->newQuery();
        
        $query->where('status', 1)
              ->where('vip_level', '>=', $level);

        // 手动处理特定语法差异
        if ($this->isEloquent) {
            return $query->orderBy('vip_level', 'desc')->get();
        } else {
            return $query->order('vip_level', 'desc')->select();
        }
    }
	
    /**
     * 示例：复杂报表，使用原生 SQL
     */
    public function getUserStatByRawSql()
    {
        $sql = "SELECT status, COUNT(*) as total FROM user WHERE age > ? GROUP BY status";
        
        // 这里的 ? 会被自动处理绑定，防止注入
        return $this->query($sql, [18]); 
    }
}