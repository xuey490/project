<?php

declare(strict_types=1);

/**
 * 操作日志服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysOperationLog;
use App\Dao\SysOperationLogDao;
use Framework\Basic\BaseService;

/**
 * OperationLogService 操作日志服务
 */
class OperationLogService extends BaseService
{
    /**
     * 操作日志DAO
     * @var SysOperationLogDao
     */
    protected SysOperationLogDao $operationLogDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->operationLogDao = new SysOperationLogDao();
    }

    /**
     * 记录操作日志
     *
     * @param array $data 日志数据
     * @return SysOperationLog
     */
    public function record(array $data): SysOperationLog
    {
        return SysOperationLog::record(array_merge([
            'operation_time' => date('Y-m-d H:i:s'),
        ], $data));
    }

    /**
     * 获取操作日志列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $username = $params['username'] ?? '';
        $module = $params['module'] ?? '';
        $businessType = $params['business_type'] ?? '';
        $status = $params['status'] ?? '';
        $operationIp = $params['operation_ip'] ?? '';
        $startTime = $params['start_time'] ?? '';
        $endTime = $params['end_time'] ?? '';

        $query = SysOperationLog::query();

        if ($username !== '') {
            $query->where('username', 'like', "%{$username}%");
        }

        if ($module !== '') {
            $query->where('module', $module);
        }

        if ($businessType !== '') {
            $query->where('business_type', $businessType);
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        if ($operationIp !== '') {
            $query->where('operation_ip', 'like', "%{$operationIp}%");
        }

        if ($startTime !== '') {
            $query->where('operation_time', '>=', $startTime);
        }

        if ($endTime !== '') {
            $query->where('operation_time', '<=', $endTime . ' 23:59:59');
        }

        $total = $query->count();
        $list = $query->orderBy('operation_time', 'desc')
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
     * 获取操作日志详情
     *
     * @param int $id 日志ID
     * @return array|null
     */
    public function getDetail(int $id): ?array
    {
        $log = SysOperationLog::find($id);
        return $log ? $log->toArray() : null;
    }

    /**
     * 清理过期日志
     *
     * @param int $days 保留天数
     * @return int 删除数量
     */
    public function cleanOldLogs(int $days = 30): int
    {
        return SysOperationLog::cleanOldLogs($days);
    }

    /**
     * 获取操作统计
     *
     * @param string $startDate 开始日期
     * @param string $endDate   结束日期
     * @return array
     */
    public function getStatistics(string $startDate, string $endDate): array
    {
        $query = SysOperationLog::query()
            ->where('operation_time', '>=', $startDate)
            ->where('operation_time', '<=', $endDate . ' 23:59:59');

        return [
            'total' => $query->count(),
            'success' => (clone $query)->where('status', SysOperationLog::STATUS_SUCCESS)->count(),
            'failed' => (clone $query)->where('status', SysOperationLog::STATUS_FAIL)->count(),
        ];
    }

    /**
     * 获取模块操作统计
     *
     * @param string $startDate 开始日期
     * @param string $endDate   结束日期
     * @return array
     */
    public function getModuleStatistics(string $startDate, string $endDate): array
    {
        return SysOperationLog::query()
            ->selectRaw('module, COUNT(*) as count')
            ->where('operation_time', '>=', $startDate)
            ->where('operation_time', '<=', $endDate . ' 23:59:59')
            ->groupBy('module')
            ->orderByDesc('count')
            ->get()
            ->toArray();
    }
}
