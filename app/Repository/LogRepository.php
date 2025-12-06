<?php

declare(strict_types=1);

namespace App\Repository;

use Framework\Repository\BaseRepository;

class LogRepository extends BaseRepository
{
    // ⚡ 直接指定表名，无需创建 Model 类
    protected string $modelClass = 'app_logs';

    /**
     * 场景：批量清理旧日志
     */
    public function clearOldLogs(int $daysBefore): int
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$daysBefore} days"));
        
        // delete 操作
        // 这里的 query builder 语法在 Think 和 Laravel 基本兼容 (where + delete)
        return (int) $this->newQuery()
            ->where('created_at', '<', $date)
            ->delete();
    }
}