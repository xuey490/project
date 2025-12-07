<?php

declare(strict_types=1);

namespace Framework\Repository;

use Framework\Database\DatabaseFactory;
#use InvalidArgumentException;
use RuntimeException;
use think\facade\Db as ThinkDb;
use Illuminate\Database\Capsule\Manager as IlluminateDb;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use think\db\Query as ThinkQuery;

/**
 * Class BaseRepository
 * 核心数据库操作基类
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected string $modelClass;
    protected bool $isEloquent;

    public function __construct(protected DatabaseFactory $factory)
    {
        if (empty($this->modelClass)) {
            throw new RuntimeException('Repository must define property $modelClass');
        }
        $this->isEloquent = $this->factory->isEloquent();
    }

    /**
     * 判断是否配置了有效的模型类
     */
    protected function isModelClass(): bool
    {
        return class_exists($this->modelClass);
    }

    protected function newQuery(): mixed
    {
        return $this->factory->make($this->modelClass);
    }

    /**
     * 语法糖：$repo() 获取底层 Builder
     */
    public function __invoke(?string $modelClass = null): mixed
    {
        return $this->factory->make($modelClass ?? $this->modelClass);
    }

    /**
     * 统一处理 Eager Loading
     */
    protected function applyWith(mixed $query, array $with = []): mixed
    {
        if (empty($with)) {
            return $query;
        }

        // 只有定义了模型类，才支持关联查询
        // 纯表名模式下调用 with 会报错或无意义
        if (!$this->isModelClass()) {
            return $query;
        }

        // ThinkORM 和 Laravel 的 Builder/Model 都支持 with 方法
        if (method_exists($query, 'with')) {
            return $query->with($with);
        }

        return $query;
    }

    // --- 查询方法 ---

    public function findById(int|string $id, array $with = []): mixed
    {
        // 1. 如果是 Laravel，且是模型，直接用 Model::with()->find() 效率更高
        if ($this->isModelClass() && $this->isEloquent) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $model = new $this->modelClass;
            return $model->with($with)->find($id);
        }

        // 2. 通用流程
        $query = $this->newQuery();
        $query = $this->applyWith($query, $with);

        if ($this->isModelClass()) {
            // ThinkPHP Model 或 Laravel Builder
            return $query->find($id);
        }

        // 3. 表名模式
        return $query->where('id', $id)->first() ?? null;
    }

    public function findOneBy(array $criteria, array $with = []): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        $query = $this->applyWith($query, $with);

        if ($this->isEloquent) {
            return $query->first();
        }
        return $query->find() ?: null;
    }

    public function findAll(array $criteria = [], array $orderBy = [], ?int $limit = null, array $with = []): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
        $query = $this->applyWith($query, $with);

        if ($limit) {
            $query->limit($limit);
        }

        if ($this->isEloquent) {
            return $query->get();
        }
        return $query->select();
    }

    public function paginate(array $criteria = [], int $perPage = 15, array $orderBy = [], array $with = []): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
        $query = $this->applyWith($query, $with);
        return $query->paginate($perPage);
    }

    /**
     * 自增操作 (通用)
     * @param int|string $id 主键
     * @param string $field 字段
     * @param int $amount 增加数量
     * @param array $extra 同时更新的其他字段
     */
    public function increment(int|string $id, string $field, int $amount = 1, array $extra = []): bool
    {
        $query = $this->newQuery()->where('id', $id);

        if ($this->isEloquent) {
            // Laravel: increment 返回 int (受影响行数)
            return (bool) $query->increment($field, $amount, $extra);
        } else {
            // ThinkPHP: inc 只是标记，需要 update 执行 (或者直接 use Db::raw)
            // ThinkORM 的 inc 方法: inc('score', 1)->update($extra)
            return (bool) $query->inc($field, $amount)->update($extra);
        }
    }

    /**
     * 自减操作 (通用)
     */
    public function decrement(int|string $id, string $field, int $amount = 1, array $extra = []): bool
    {
        $query = $this->newQuery()->where('id', $id);

        if ($this->isEloquent) {
            return (bool) $query->decrement($field, $amount, $extra);
        } else {
            return (bool) $query->dec($field, $amount)->update($extra);
        }
    }


    // --- 写入方法 ---

    public function create(array $data): mixed
    {
        if ($this->isModelClass()) {
            return forward_static_call([$this->modelClass, 'create'], $data);
        }

        // 表名模式
        if ($this->isEloquent) {
            $id = $this->newQuery()->insertGetId($data);
            return $this->findById($id);
        } else {
            $id = $this->newQuery()->insert($data, true);
            return $this->findById($id);
        }
    }

    public function update(int|string $id, array $data): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        if (is_object($item) && method_exists($item, 'save')) {
            if ($this->isEloquent) {
                return $item->fill($data)->save();
            } else {
                return $item->save($data);
            }
        }

        return $this->newQuery()->where('id', $id)->update($data) > 0;
    }

    public function updateBy(array $criteria, array $data): int
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        return (int) $query->update($data);
    }

    public function delete(int|string $id): bool
    {
        if ($this->isModelClass()) {
            return (bool) forward_static_call([$this->modelClass, 'destroy'], $id);
        }
        return (bool) $this->newQuery()->where('id', $id)->delete();
    }

    public function deleteBy(array $criteria): int
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        return (int) $query->delete();
    }

    // --- 统计与原生 ---

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

        if ($type === 'sum' && is_numeric($result)) {
            return (string) $result; 
        }

        return $result;
    }

    public function transaction(\Closure $callback): mixed
    {
        if ($this->isEloquent) {
            return IlluminateDb::transaction($callback);
        }
        return ThinkDb::transaction($callback);
    }

    public function query(string $sql, array $bindings = []): array
    {
        if ($this->isEloquent) {
            $result = IlluminateDb::select($sql, $bindings);
            return array_map(fn($item) => (array) $item, $result);
        }
        return ThinkDb::query($sql, $bindings);
    }

    public function execute(string $sql, array $bindings = []): int
    {
        if ($this->isEloquent) {
            return IlluminateDb::affectingStatement($sql, $bindings);
        }
        return (int) ThinkDb::execute($sql, $bindings);
    }

    // --- 核心 DSL 解析 ---

    protected function buildQuery(mixed $query, array $criteria, array $orderBy = []): mixed
    {
        // ⚡⚡⚡ 关键修复步骤 1：确保 $query 是查询构造器，而不是模型实例 ⚡⚡⚡
        // 如果传入的是 Model 实例，调用 where/join 等方法会返回新对象，必须接住它。
        // 最稳妥的方法是先手动转换成 Builder。
        
        if ($this->isModelClass()) {
            if ($this->isEloquent) {
                // Laravel: 如果是模型，转为 Builder
                if ($query instanceof \Illuminate\Database\Eloquent\Model) {
                    $query = $query->newQuery();
                }
            } else {
                // ThinkPHP: 如果是模型，转为 Db\Query
                if ($query instanceof \think\Model) {
                    $query = $query->db(); 
                }
            }
        }
		
       // 1. SELECT 指定字段
        if (!empty($criteria['select'])) {
            $query->select($criteria['select']); // string or array
            unset($criteria['select']);
        }

        // 2. DISTINCT 去重
        if (!empty($criteria['distinct'])) {
            $query->distinct();
            unset($criteria['distinct']);
        }

        // 3. LOCK 悲观锁 (for update)
        if (!empty($criteria['lock'])) {
            if ($this->isEloquent) {
                $query->lockForUpdate();
            } else {
                $query->lock(true);
            }
            unset($criteria['lock']);
        }
		
        // 4. JOINs
        foreach (['join', 'leftJoin', 'rightJoin'] as $joinType) {
            if (!empty($criteria[$joinType]) && is_array($criteria[$joinType])) {
                foreach ($criteria[$joinType] as $join) {
                    $table = $join[0] ?? null;
                    $field1 = $join[1] ?? null;
                    $operator = $join[2] ?? '=';
                    $field2 = $join[3] ?? null;

                    if (!$table || !$field1) continue;

                    // 自动补 "="
                    if ($field2 === null && isset($join[2])) {
                        $field2 = $join[2];
                        $operator = '=';
                    }

                    if (!$this->isEloquent) {
                        // ThinkORM: join('table', 'a=b')
                        $query->$joinType($table, "{$field1} {$operator} {$field2}");
                    } else {
                        // Laravel: join('table', 'a', '=', 'b')
                        $query->$joinType($table, $field1, $operator, $field2);
                    }
                }
                unset($criteria[$joinType]);
            }
        }
		
        // 5. WHERE NULL / NOT NULL
        if (!empty($criteria['whereNull'])) {
            foreach ((array)$criteria['whereNull'] as $field) $query->whereNull($field);
            unset($criteria['whereNull']);
        }
        if (!empty($criteria['whereNotNull'])) {
            foreach ((array)$criteria['whereNotNull'] as $field) $query->whereNotNull($field);
            unset($criteria['whereNotNull']);
        }

        // 6. WHERE IN / NOT IN (显式 Key 方式)
        if (!empty($criteria['whereIn'])) {
            foreach ($criteria['whereIn'] as $field => $values) $query->whereIn($field, $values);
            unset($criteria['whereIn']);
        }
        if (!empty($criteria['whereNotIn'])) {
            foreach ($criteria['whereNotIn'] as $field => $values) $query->whereNotIn($field, $values);
            unset($criteria['whereNotIn']);
        }
		
        // 7. GroupBy & Having
        if (!empty($criteria['groupBy'])) {
            $groupBy = (array) $criteria['groupBy'];
            $query->groupBy(...$groupBy); // Laravel/Think 都支持变长参数或数组
            unset($criteria['groupBy']);
        }

        if (!empty($criteria['having']) && is_array($criteria['having'])) {
            foreach ($criteria['having'] as $cond) {
                if (count($cond) === 3) {
                    $query->having($cond[0], $cond[1], $cond[2]);
                } elseif (count($cond) === 2) {
                    $query->having($cond[0], '=', $cond[1]);
                }
            }
            unset($criteria['having']);
        }

        if (!empty($criteria['havingRaw'])) {
            $query->havingRaw($criteria['havingRaw']);
            unset($criteria['havingRaw']);
        }

		// 🚩 [新增] 处理 or_group (实现 WHERE (A OR B OR C) 逻辑)
        // 5. 🚩 处理 or_group (组内 OR)
        if (!empty($criteria['or_group']) && is_array($criteria['or_group'])) {
            $orGroup = $criteria['or_group'];
            $query->where(function ($subQuery) use ($orGroup) {
                // 这里用你验证过有效的逻辑即可
                // 如果是递归版本也没问题，只要外层 $query 是 Builder 就行
                $isFirst = true;
                foreach ($orGroup as $field => $value) {
                    $op = '='; $val = $value;
                    if (is_array($value)) { $op = $value[0] ?? '='; $val = $value[1] ?? $value[0]; }

                    if ($this->isEloquent) {
                        $isFirst ? $subQuery->where($field, $op, $val) : $subQuery->orWhere($field, $op, $val);
                    } else {
                        $isFirst ? $subQuery->where($field, $op, $val) : $subQuery->whereOr($field, $op, $val);
                    }
                    $isFirst = false;
                }
            });
            unset($criteria['or_group']);
        }


        // 3. Where
        foreach ($criteria as $field => $value) {
			
            // 忽略特殊 Key
            if (in_array($field, ['page', 'limit', 'per_page'])) continue; 
			
            // 修正：Laravel 没有 whereOr，只有 orWhere
            if ($field === 'or' && is_array($value)) {
                $callback = function ($q) use ($value) {
                    $this->buildQuery($q, $value);
                };

                if ($this->isEloquent) {
                    $query->orWhere($callback);
                } else {
                    $query->whereOr($callback);
                }
                continue;
            }
			
			
			
            if ($field === 'group' && is_callable($value)) {
                $query->where(function ($q) use ($value) {
                    $value($q);
                });
                continue;
            }

            if ($field === 'raw') {
                $query->whereRaw($value);
                continue;
            }

            if (is_array($value)) {
                [$op, $val] = $value;
                switch (strtolower($op)) {
                    case 'between':
                        $query->whereBetween($field, $val);
                        break;
                    case 'in':
                        $query->whereIn($field, $val);
                        break;
                    case 'like':
                        // Think 和 Laravel 都支持 where('field', 'like', 'val')
                        $query->where($field, 'LIKE', $val);
                        break;
                    default:
                        $query->where($field, $op, $val);
                }
            } else {
                $query->where($field, $value);
            }
        }

        // 4. OrderBy
        foreach ($orderBy as $field => $direction) {
            if ($this->isEloquent) {
                $query->orderBy($field, $direction);
            } else {
                $query->order($field, $direction);
            }
        }

        return $query;
    }
}