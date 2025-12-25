<?php

declare(strict_types=1);

namespace App\Repository;

use Framework\Repository\BaseRepository;
use App\Models\Module;

class ModuleRepository extends BaseRepository
{
    #protected string $modelClass = 'admin_module';//Module::class;
	
	protected string $modelClass = \App\Models\Module::class; //或上面那一句

    /**
     * 获取列表 (支持搜索、状态、关联)
     */
    public function getList(array $params, int $page, int $limit): mixed
    {
        // 构建 DSL 查询条件
        $criteria = [];
		
        // 1. 搜索: 标题 OR 名称
        if (!empty($params['keyword'])) {
            // ✅ 正确写法：使用 buildQuery 中新增的 or_group 逻辑
            $criteria['or_group'] = [
                'title' => ['like', "%{$params['keyword']}%"],
                'name'  => ['like', "%{$params['keyword']}%"],
            ];
        }
		
        // 2. 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $criteria['status'] = (int) $params['status'];
        }
		
		#dump($criteria);
        
        // 3. 筛选未分组用户 (WHERE NULL)
        if (!empty($params['no_group'])) {
        //    $criteria['whereNull'] = 'group_id';
        }
		

		$criteria['and'] = [
            //['id' => 'admin'],
            'id' => ['>=', 7],
        ];
		
		$criteria['or'] =[
		 'id' =>['in', [5,6,7]]
		];
		

        // 4. 排序
        $order = ['id' => 'desc'];
        if (!empty($params['sort_balance'])) {
        //    $order = ['balance' => $params['sort_balance']]; // 'asc' or 'desc'
        }

        // 5. 执行查询 (带关联)
        return $this->paginate(
            criteria: $criteria,
            perPage: $limit,
            orderBy: $order,
			with: [],
            //with: ['group'] // 预加载 group 防止 N+1
        );
    }

    /**
     * 充值余额 (演示 Increment)
     */
    public function recharge(int $userId, float $money): bool
    {
        // 增加余额，同时更新 updated_at
        return $this->increment($userId, 'balance', (int)$money); 
    }

    /**
     * 检查邮箱唯一性 (排除自己)
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $criteria = ['name' => $name];
        if ($excludeId) {
            $criteria['id'] = ['<>', $excludeId];
        }
        // 使用 aggregate 统计
        return $this->aggregate('count', $criteria) > 0;
    }
	
    /**
     * 查询 id > 1 且 status = 1 的用户
     */
    public function activeUsers()
    {
        return $this->newQuery()
            ->where('id' , '>' , 1)
            ->where(['status' =>[1]]);//where('status' , '=' , 1)
    }
	
}