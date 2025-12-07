<?php

declare(strict_types=1);

namespace App\Repository;

use Framework\Repository\BaseRepository;

class LogRepository extends BaseRepository
{
    // ⚡ 直接指定表名，无需创建 Model 类
    protected string $modelClass = 'admin_log';


    public function SelectLogs(int $daysBefore): mixed
    {
        $date = strtotime(date('Y-m-d H:i:s', strtotime("-{$daysBefore} days")));

        // delete 操作
        // 这里的 query builder 语法在 Think 和 Laravel 基本兼容 (where + delete)
        return $this->newQuery()
            ->where('create_time', '<', $date)
            ->select();
    }
}