<?php

namespace App\Services;

use App\Models\SysLoginLog;
use Framework\Basic\BaseService;

class SysLoginLogService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysLoginLog::query();

        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', "%{$params['user_name']}%");
        }
        if (!empty($params['ip'])) {
            $query->where('ip', 'like', "%{$params['ip']}%");
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (int) $params['status']);
        }
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $query->whereBetween('login_time', [$params['start_time'], $params['end_time']]);
        }

        return $query->orderBy('login_time', 'desc')->paginate($params['limit'] ?? 10);
    }

    public function delete(array $ids): void
    {
        SysLoginLog::destroy($ids);
    }

    public function clean(): void
    {
        SysLoginLog::truncate();
    }
}
