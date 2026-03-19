<?php

namespace App\Services;

use App\Models\SysAccessLog;
use Framework\Basic\BaseService;

class SysAccessLogService extends BaseService
{
    public function getList(array $params)
    {
        $query = SysAccessLog::query();

        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', "%{$params['user_name']}%");
        }
        if (!empty($params['path'])) {
            $query->where('path', 'like', "%{$params['path']}%");
        }
        if (!empty($params['ip'])) {
            $query->where('ip', 'like', "%{$params['ip']}%");
        }
        if (isset($params['status_code']) && $params['status_code'] !== '') {
            $query->where('status_code', (int) $params['status_code']);
        }
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $query->whereBetween('created_at', [$params['start_time'], $params['end_time']]);
        }

        return $query->orderBy('created_at', 'desc')->paginate($params['limit'] ?? 10);
    }

    public function delete(array $ids): void
    {
        SysAccessLog::destroy($ids);
    }

    public function clean(): void
    {
        SysAccessLog::truncate();
    }
}
