<?php

declare(strict_types=1);

namespace App\Services;

use App\Repository\UserRepository;
use App\Repository\LogRepository;
use Exception;

class UserBusinessService
{
    public function __construct(
        protected UserRepository $userRepo,
        protected LogRepository $logRepo
    ) {}

    /**
     * 演示 1: 事务回滚 & 多仓库协作
     * 场景：注册用户，并写入日志。如果日志写入失败，用户也回滚。
     */
    public function registerUser(array $userData): array
    {
        // 使用 UserRepository 开启事务
        // 由于 BaseRepository 封装了事务闭包，这里非常干净
        try {
            return $this->userRepo->transaction(function () use ($userData) {
                
                // 1. 创建用户 (Model模式)
                $user = $this->userRepo->create([
                    'username' => $userData['username'],
                    'email'    => $userData['email'],
                    'balance'  => '0.00', // 初始余额
                    'status'   => 1
                ]);

                // 获取用户ID (兼容数组或对象返回)
                $userId = $user['id'] ?? $user->id;

                // 2. 写入日志 (Table模式)
                // 模拟一个错误：如果用户名包含 "error"，则抛出异常触发回滚
                if (str_contains($userData['username'], 'error')) {
                    throw new Exception("模拟故障：触发事务回滚");
                }

                $this->logRepo->create([
                    'user_id' => $userId,
                    'action'  => 'register',
                    'ip'      => '127.0.0.1',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // 返回结果给外层
                return ['user_id' => $userId, 'status' => 'success'];
            });

        } catch (\Throwable $e) {
            // 事务已自动回滚
            return ['status' => 'fail', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 演示 2: 高精度聚合查询 & 原生 SQL
     * 场景：计算所有 VIP 用户的总余额，用于财务核对
     */
    public function auditFinances(): array
    {
        // 1. 使用基类 aggregate 方法 (高精度 sum 返回 string)
        // 查找 status=1 且 vip_level > 0 的用户余额总和
        $totalBalance = $this->userRepo->aggregate(
            'sum', 
            ['status' => 1, 'vip_level' => ['>', 0]], 
            'balance'
        );

        // 假设当前 totalBalance = "10000.55"
        // 使用 bcmath 计算 1% 的手续费
        $fee = bcmul((string)$totalBalance, '0.01', 2);

        // 2. 调用 Repository 里的原生 SQL 方法获取报表
        $regionStats = $this->userRepo->getUserRegionReport();

        return [
            'total_balance' => $totalBalance, // string
            'platform_fee'  => $fee,          // string
            'region_report' => $regionStats   // array
        ];
    }

    /**
     * 演示 3: 分页查询 & 批量修改 & 删除
     * 场景：后台管理列表
     */
    public function manageUsers(int $page, int $limit): array
    {
        // 1. 分页查询
        // 筛选 status=1, 按 id 倒序
        $paginator = $this->userRepo->paginate(
            ['status' => 1], 
            $limit, 
            ['id' => 'desc']
        );

        // 2. 数据清洗 (兼容 Eloquent Collection 和 Think Collection)
        $list = [];
        // items() 在 Laravel 是方法，ThinkPHP 分页对象可以直接遍历
        // 为了安全，通常我们在 BaseRepository 或这里做归一化
        $items = method_exists($paginator, 'items') ? $paginator->items() : $paginator;
        
        foreach ($items as $item) {
            $list[] = [
                'id' => $item['id'] ?? $item->id,
                'username' => $item['username'] ?? $item->username,
            ];
        }

        return [
            'data' => $list,
            'total' => method_exists($paginator, 'total') ? $paginator->total() : $paginator->total(),
            'current_page' => $page
        ];
    }

    /**
     * 演示 4: 维护模式 (Table模式下的 Update/Delete)
     */
    public function maintenance(): void
    {
        // 1. 修改：将 id=5 的日志标记为已读
        // Table模式下 update 返回 bool
        $this->logRepo->update(5, ['is_read' => 1]);

        // 2. 删除：清理30天前的日志
        $affectedRows = $this->logRepo->clearOldLogs(30);
        
        // 3. 原生执行：优化表 (示例)
        $this->logRepo->execute("OPTIMIZE TABLE app_logs");
    }
}