<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-6
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Repository;

use Framework\Database\DatabaseFactory;
use InvalidArgumentException;
use RuntimeException;
use think\facade\Db as ThinkDb;
use Illuminate\Database\Capsule\Manager as IlluminateDb;

/**
 * Class BaseRepository
 * 核心数据库操作基类，用于屏蔽 ThinkPHP 和 Laravel ORM 的语法差异
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var string 当前仓库操作的模型类名 (由子类定义)
     */
    protected string $modelClass;

    /**
     * @var bool 标记是否为 Laravel Eloquent 环境
     */
    protected bool $isEloquent;

    /**
     * @param DatabaseFactory $factory 注入数据库工厂
     */
    public function __construct(protected DatabaseFactory $factory)
    {
        if (empty($this->modelClass)) {
            throw new RuntimeException('Repository must define property $modelClass');
        }

        // 检测底层驱动类型
        // 通过简单实例化一个对象来判断它是 Think 模型还是 Laravel 模型
        $instance = $this->factory->make($this->modelClass);
		
        $this->isEloquent = ($instance instanceof \Illuminate\Database\Eloquent\Model) 
                         || ($instance instanceof \Illuminate\Database\Query\Builder);
    }
	
	/**
     * 语法糖：允许像函数一样调用 Repository
     * 
     * 用法 1 (推荐): $repo() -> 获取当前模型的 QueryBuilder (等同于 newQuery)
     * 用法 2 (工厂): $repo('App\Model\Order') -> 临时获取其他模型的 Builder (等同于 factory->make)
     */
    public function __invoke(?string $modelClass = null): mixed
    {
        // 如果没有传参，就用当前仓库定义的 modelClass
        // 如果传了参，就通过 factory 制造那个参数指定的模型
        return $this->factory->make($modelClass ?? $this->modelClass);
    }

    /**
     * 获取一个新的查询构造器/模型实例
     */
    protected function newQuery(): mixed
    {
        return $this->factory->make($this->modelClass);
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int|string $id): mixed
    {
        // 两者都支持 find($id)
        return $this->newQuery()->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        
        // 差异屏蔽：Think 用 find(), Laravel 用 first()
        if ($this->isEloquent) {
            return $query->first();
        }
        return $query->find();
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(array $criteria = [], array $orderBy = [], ?int $limit = null): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);

        if ($limit) {
            $query->limit($limit);
        }

        // 差异屏蔽：Think 用 select(), Laravel 用 get()
        if ($this->isEloquent) {
            return $query->get();
        }
        return $query->select();
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(array $criteria = [], int $perPage = 15, array $orderBy = []): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
        
        // 两者都支持 paginate() 方法，虽然返回的对象结构不同，但方法名一致
        return $query->paginate($perPage);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): mixed
    {
        // 差异屏蔽：Laravel create返回模型，Think create返回模型
        // 但如果 modelClass 是表名字符串，处理方式不同
        if (class_exists($this->modelClass)) {
            // 模型模式
            return forward_static_call([$this->modelClass, 'create'], $data);
        }
        
        // 表名模式
        if ($this->isEloquent) {
            $id = $this->newQuery()->insertGetId($data);
            return $this->findById($id);
        } else {
            // ThinkPHP insert 默认返回受影响行数，需要 getId=true
            $id = $this->newQuery()->insert($data, true);
            return $this->findById($id); // 返回 array
        }
    }

    /**
     * {@inheritDoc}
     */
    public function update(int|string $id, array $data): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }
		
		// 1. 模型模式 (对象且有save方法)
        if (is_object($item) && method_exists($item, 'save')) {
            // 模型模式：统一使用 fill + save
            // Laravel 用 fill($data), Think 用 save($data) 更新
            if ($this->isEloquent) {
                return $item->fill($data)->save();
            } else {
                return $item->save($data);
            }
        }
        
        // 2. 表名模式 (Query Builder)
        // 注意：ThinkPHP 的 update 返回受影响行数(int)，Laravel 也是 int
        return $this->newQuery()->where('id', $id)->update($data) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int|string $id): bool
    {
        // 差异屏蔽：模型删除通常用 destroy (静态) 或 delete (实例)
        if (class_exists($this->modelClass)) {
             if ($this->isEloquent) {
                 return (bool) forward_static_call([$this->modelClass, 'destroy'], $id);
             } else {
                 return (bool) forward_static_call([$this->modelClass, 'destroy'], $id);
             }
        }

        // 表名模式
        return (bool) $this->newQuery()->where('id', $id)->delete();
    }
	
	/**
     * 聚合统计
     * 修改返回类型：增加 string (为了高精度)
     */
	// 业务代码
	//$totalMoney = $userRepo->aggregate('sum', ['status' => 1], 'balance');

	// $totalMoney 可能是 "1024.56" (string)
	// 使用 bcmath 进行后续计算
	//$fee = bcmul($totalMoney, '0.01', 2);	 
    public function aggregate(string $type, array $criteria = [], string $field = '*'): string|int|float
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        
        $result = match (strtolower($type)) {
            'count' => $query->count($field),
            'sum'   => $query->sum($field),
            'max'   => $query->max($field),
            'min'   => $query->min($field),
            'avg'   => $query->avg($field),
            default => 0,
        };

        // ⚡ 关键修正：确保金额计算返回字符串或数字，不要隐式转 float
        // 如果是 sum 操作，且结果是 numeric 字符串，直接返回字符串给业务层用 bcmath 处理
        if ($type === 'sum' && is_numeric($result)) {
            return (string) $result; 
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function transaction(\Closure $callback): mixed
    {
        if ($this->isEloquent) {
            return IlluminateDb::transaction($callback);
        }
        return ThinkDb::transaction($callback);
    }
	
    /**
     * 执行原生查询
     * 统一返回 array 格式 (Laravel 默认返回 stdClass 数组，这里建议转为 array 以保持统一)
     */
    public function query(string $sql, array $bindings = []): array
    {
        if ($this->isEloquent) {
            // Laravel 返回的是 stdClass 对象数组
            $result = IlluminateDb::select($sql, $bindings);
            // 转换为纯数组，保证与 ThinkPHP 行为一致
            return array_map(fn($item) => (array) $item, $result);
        }

        // ThinkPHP 返回的是 array
        return ThinkDb::query($sql, $bindings);
    }

    /**
     * 执行原生指令
     */
    public function execute(string $sql, array $bindings = []): int
    {
        if ($this->isEloquent) {
            // Laravel statement 返回 bool，affectingStatement 返回 int
            return IlluminateDb::affectingStatement($sql, $bindings);
        }

        // ThinkPHP execute 返回受影响行数 (int)
        return (int)ThinkDb::execute($sql, $bindings);
    }

    /**
     * 内部通用方法：构建查询条件和排序
     * 自动处理 orderBy(Laravel) 和 order(Think) 的差异
     * 
     * @param mixed $query 查询对象
     * @param array $criteria 如 ['age' => ['>', 18], 'status' => 1]
     * @param array $orderBy 如 ['id' => 'desc']
     */
    protected function buildQuery(mixed $query, array $criteria, array $orderBy = []): mixed
    {
        // 1. 处理 Where 条件
        foreach ($criteria as $field => $value) {
            if (is_array($value) && count($value) === 2) {
                // 格式: ['age' => ['>', 18]]
                $query->where($field, $value[0], $value[1]);
            } else {
                // 格式: ['status' => 1]
                $query->where($field, $value);
            }
        }

        // 2. 处理排序
        // 差异屏蔽：Think 使用 order(), Laravel 使用 orderBy()
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                if ($this->isEloquent) {
                    $query->orderBy($field, $direction);
                } else {
                    $query->order($field, $direction);
                }
            }
        }

        return $query;
    }
}