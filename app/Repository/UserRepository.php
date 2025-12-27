<?php

declare(strict_types=1);

namespace App\Repository;

use Framework\Repository\BaseRepository;
use Framework\Database\DatabaseFactory;

use App\Services\UserService;
use Framework\DI\Attribute\Autowire;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Context;

/**
 * 用户仓库
 * 继承 BaseRepository 获得所有标准 CRUD 能力
 */
class UserRepository extends BaseRepository
{
    // 指定该仓库操作的模型 (完整类名)
    // 指定模型类或表名（支持 Model::class 或 'users'）
    protected string $modelClass = \App\Models\User::class; // 或 'users'
	
    #[Autowire]
    protected UserService $userService;

	/*
    public function __construct(DatabaseFactory $factory)
    {
		$n = new UserService();
		dump($this->userService->getUsers(1));
        parent::__construct($factory);
    }*/
		
    /**
     * 子类可根据需要覆盖 lifecycle
     */
    protected function initialize(): void
    {
		//dump($this->userService);
    }	

    // 1.插入：单条（返回主键）
    public function create(array $data):mixed
    {
        return $this->insertGetId($data);
    }

    /**
     * 示例自定义方法：查找活跃用户并返回带 posts 关系（避免 N+1）
     */
    public function findActiveWithPosts(int $limit = 50)
    {
		
        $criteria = ['status' => 1];
        $orderBy = ['last_login' => 'desc'];
        $with = ['posts']; // eager load posts to avoid N+1
        return $this->findAll($criteria, $orderBy, $limit, $with);
    }

    /**
     * 示例：按 DSL 更新
     */
    public function deactivateOldUsers(string $beforeDate): int
    {
        $criteria = [
            'AND' => [
                ['status' => 1],
                ['created_at' => ['<', $beforeDate]]
            ]
        ];
        return $this->updateBy($criteria, ['status' => 0]);
    }
	
    // 如果需要扩展特定的复杂业务逻辑，可以在这里写
    // 比如：查找活跃的 VIP 用户
    public function findActiveVips(int $id = 2)
    {
		
        // 获取原生查询构造器，自己处理复杂逻辑 
        $query = $this->newQuery();
		

        $query->where('status', 1)
              ->where('id', '>=', $id);
			  
			  

        // 手动处理特定语法差异
        if ($this->isEloquent) {
            return $query->orderBy('id', 'desc')->get();
        } else {
            return $query->order('id', 'desc')->paginate(2)->toArray();
        }
		
        // 现在的语法糖写法：
        //$query = ($this)(); 

        return $query->where('status', 1)->select();
		
    }
	
    public function checkLog()
    {
        // 内部临时调用其他表
        // 等价于 $this->factory->make('app_logs')
        return ($this)('admin_log')->count();
    }
	
    /**
     * 场景：复杂子查询
     * 目标：查找所有“有最近30天内有过消费记录”的用户，并分页
     * 
     * 这里演示如何在 Repository 内部处理 ORM 语法差异，对外只暴露结果
     */
    public function findActiveShoppers(int $days = 30, int $perPage = 15): mixed
    {
        $query = $this->newQuery();
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // 这里的逻辑通常差异较大，建议用 if 分支或原生 SQL
        if ($this->isEloquent) {
            // === Laravel Eloquent 风格 ===
            // 假设有一个 orders 关联关系
            $query->whereHas('orders', function ($q) use ($date) {
                $q->where('created_at', '>=', $date);
            })->orderBy('id', 'desc');
        } else {
            // === ThinkORM 风格 ===
            // 假设 orders 表存在
            $query->whereExists(function ($q) use ($date) {
                $table = $this->isEloquent ? 'orders' : 'orders'; // 实际表名
                $q->table('orders')
                  ->where('user_id', '=', \think\facade\Db::raw('user.id'))
                  ->where('created_at', '>=', $date);
            })->order('id', 'desc');
        }

        return $query->paginate($perPage);
    }

    /**
     * 场景：原生 SQL 复杂报表
     * 目标：统计每个地区的用户数量，过滤掉人数少于 10 的地区
     */
    public function getUserRegionReport(): array
    {
        $sql = "SELECT region, COUNT(*) as total 
                FROM user 
                WHERE status = ? 
                GROUP BY region 
                HAVING total > ? 
                ORDER BY total DESC";
        
        // 直接调用基类封装的 query，返回纯数组
        return $this->query($sql, [1, 10]); // status=1, count>10
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
