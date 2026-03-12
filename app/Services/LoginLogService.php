<?php

declare(strict_types=1);

/**
 * 登录日志服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysLoginLog;
use App\Dao\SysLoginLogDao;
use Framework\Basic\BaseService;

/**
 * LoginLogService 登录日志服务
 */
class LoginLogService extends BaseService
{
    /**
     * 登录日志DAO
     * @var SysLoginLogDao
     */
    protected SysLoginLogDao $loginLogDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->loginLogDao = new SysLoginLogDao();
    }

    /**
     * 记录登录日志
     *
     * @param array $data 日志数据
     * @return SysLoginLog
     */
    public function record(array $data): SysLoginLog
    {
        return SysLoginLog::record(array_merge([
            'login_time' => date('Y-m-d H:i:s'),
        ], $data));
    }

    /**
     * 获取登录日志列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $username = $params['username'] ?? '';
        $loginStatus = $params['login_status'] ?? '';
        $loginIp = $params['login_ip'] ?? '';
        $startTime = $params['start_time'] ?? '';
        $endTime = $params['end_time'] ?? '';

        $query = SysLoginLog::query();

        if ($username !== '') {
            $query->where('username', 'like', "%{$username}%");
        }

        if ($loginStatus !== '') {
            $query->where('login_status', (int)$loginStatus);
        }

        if ($loginIp !== '') {
            $query->where('login_ip', 'like', "%{$loginIp}%");
        }

        if ($startTime !== '') {
            $query->where('login_time', '>=', $startTime);
        }

        if ($endTime !== '') {
            $query->where('login_time', '<=', $endTime . ' 23:59:59');
        }

        $total = $query->count();
        $list = $query->orderBy('login_time', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 清理过期日志
     *
     * @param int $days 保留天数
     * @return int 删除数量
     */
    public function cleanOldLogs(int $days = 30): int
    {
        return SysLoginLog::cleanOldLogs($days);
    }

    /**
     * 获取登录统计
     *
     * @param string $startDate 开始日期
     * @param string $endDate   结束日期
     * @return array
     */
    public function getStatistics(string $startDate, string $endDate): array
    {
        $query = SysLoginLog::query()
            ->where('login_time', '>=', $startDate)
            ->where('login_time', '<=', $endDate . ' 23:59:59');

        return [
            'total' => $query->count(),
            'success' => (clone $query)->where('login_status', SysLoginLog::STATUS_SUCCESS)->count(),
            'failed' => (clone $query)->where('login_status', SysLoginLog::STATUS_FAIL)->count(),
        ];
    }
}
