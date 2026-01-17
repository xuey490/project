<?php

declare(strict_types=1);

namespace Framework\Repository;

use Framework\Database\DatabaseFactory;
use RuntimeException;
use think\facade\Db as ThinkDb;
use Illuminate\Database\Capsule\Manager as IlluminateDb;
use Framework\DI\Injectable;
use Framework\Core\App;
use Framework\Tenant\TenantContext; // [新增] 引入租户上下文

/**
 * Class BaseRepository
 * 核心数据库操作基类（兼容 Illuminate ORM & ThinkPHP 8 ORM）
 * 集成 TenantContext 实现轻量级多租户隔离，屏蔽底层ORM差异
 * @package Framework\Repository
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * 模型类全名（子类必须覆盖定义，如：\App\Models\User::class）
     * @var string
     */
    protected string $modelClass = '';

    /**
     * 是否启用 Illuminate Eloquent ORM（由 DatabaseFactory 初始化）
     * @var bool
     */
    protected bool $isEloquent = false;

    /**
     * 租户字段名（默认为 tenant_id，子类可覆盖）
     * @var string
     */
    protected string $tenantField = 'tenant_id';

    // 引入依赖注入能力
    use Injectable;

    /**
     * BaseRepository 构造函数
     * 初始化ORM类型、模型校验、依赖注入
     * @param DatabaseFactory $factory 数据库工厂
     * @throws RuntimeException 当未定义 $modelClass 时抛出异常
     */
    public function __construct(protected DatabaseFactory $factory)
    {
        // 1. 执行依赖注入
        $this->inject();

        // 2. 校验模型类是否定义
        if (empty($this->modelClass)) {
            throw new RuntimeException('Repository 必须定义 $modelClass 属性（指定对应模型类全名）');
        }

        // 3. 初始化ORM类型标识
        $this->isEloquent = $this->factory->isEloquent();

        // 4. 子类初始化钩子
        $this->initialize();
    }

    /**
     * 子类初始化钩子方法
     * 子类可覆盖此方法，实现自定义初始化逻辑
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * 应用多租户筛选条件
     * 自动判断是否需要拼接 tenant_id 条件
     * 注意：模型类通常由 Global Scope 处理，此处主要针对纯表查询或特定场景补漏
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyTenantFilter(mixed $query, array $criteria): void
    {
        // 1. 如果查询条件中已手动包含 tenant_id，则跳过自动筛选，避免冲突
        if (isset($criteria[$this->tenantField])) {
            return;
        }

        // 2. 检查上下文是否应该应用租户隔离 (TenantContext 已封装了 ignore 和 id 是否存在的判断)
        if (!TenantContext::shouldApplyTenant()) {
            return;
        }

        // 3. 如果是模型类 (isModelClass = true)，则租户筛选应由 Model 的 Global Scope 负责
        // Repository 不应再手动添加 where，否则会出现 "WHERE tenant_id = 1 AND tenant_id = 1"
        if ($this->isModelClass()) {
            return;
        }

        // 4. 纯表模式：手动拼接租户筛选条件
        $query->where($this->tenantField, TenantContext::getTenantId());
    }

    /**
     * 判断是否配置了有效的模型类
     * @return bool
     */
    public function isModelClass(): bool
    {
        return class_exists($this->modelClass);
    }

    /**
     * 获取模型实例（容器实例化，保证单例和依赖注入一致性）
     * @return mixed 模型实例（Illuminate\Model 或 think\Model）
     */
    protected function getModel(): mixed
    {
        // 再次校验模型类是否存在，不存在则尝试通过工厂创建（兼容纯表模式）
        if (!class_exists($this->modelClass)) {
            return ($this->factory)->make($this->modelClass);
        }
        // 通过应用容器实例化模型
        return App()->make($this->modelClass);
    }

    /**
     * 获取查询构造器（统一模型实例转构造器，屏蔽ORM差异）
     * 等价于 newQuery()，是核心查询入口
     * @return mixed 查询构造器（EloquentBuilder 或 ThinkQuery）
     */
    protected function newQuery(): mixed
    {
        // 1. 获取基础构造器
        $builder = $this->getBuilder($this->getModel());

        // 2. 检查上下文是否处于“忽略租户”模式 (超管/系统模式)
        if (TenantContext::isIgnoring()) {
            
            // --- Illuminate ORM 处理 ---
            if ($this->isEloquent) {
                // [核心修复]：只有当 builder 是 Eloquent 构建器时，才存在 GlobalScope 的概念
                // 如果是基础 Query\Builder (纯表查询)，它没有 Scope，无需也无法调用 withoutGlobalScope
                if ($builder instanceof \Illuminate\Database\Eloquent\Builder) {
                    // 移除由 Trait 注入的全局作用域
                    $builder->withoutGlobalScope(\Framework\Basic\Scopes\LaTenantScope::class);
                }
            } 
            // --- ThinkPHP ORM 处理 ---
            else {
                $model = $this->getModel();
                // 确保是 ThinkPHP 模型且具备 ignoreTenant 能力
                if ($model instanceof \think\Model && class_exists($this->modelClass)) {
                    // 如果模型有 ignoreTenant 方法（TP通常通过静态变量控制 Scope）
                    if (method_exists($model, 'ignoreTenant')) {
                        // A. 【开启忽略】
                        $model->ignoreTenant();
                        
                        // B. 【重新生成构造器】
                        // 因为 TP 的 Scope 通常在调用 db() 时触发，所以需要在设置忽略后重新获取 Builder
                        $builder = $this->getBuilder($model);

                        // C. 【立即还原】
                        // 还原开关，避免污染后续该模型实例的其他查询
                        if (method_exists($model, 'restoreTenant')) {
                            $model->restoreTenant();
                        }
                    }
                }
            }
        }

        return $builder;
    }

    /**
     * 语法糖：$repo() 直接获取底层查询构造器
     * @param string|null $modelClass 自定义模型类
     * @return mixed
     */
    public function __invoke(?string $modelClass = null): mixed
    {
        return $this->factory->make($modelClass ?? $this->modelClass);
    }

    /**
     * 获取原生查询构建器（已自动处理租户条件）
     * @return mixed
     */
    public function rawQuery(): mixed
    {
        $query = $this->newQuery();
        
        // 纯表模式或特殊情况下，如果 newQuery 没能加上租户条件（例如绕过了 Scope），这里进行补救
        // 如果是 Model 模式，Tenant Scope 会自动处理，这里不再重复添加以免冲突
        if (TenantContext::shouldApplyTenant()) {
            $isEloquentBuilder = $query instanceof \Illuminate\Database\Eloquent\Builder;
            
            // 只有不是 Eloquent Model Builder (即纯表查询) 才手动加，防止重复
            if (!$isEloquentBuilder && !$this->isModelClass()) {
                 $query->where($this->tenantField, TenantContext::getTenantId());
            }
        }
        
        return $query;
    }

    /**
     * 统一处理关联预加载
     * @param mixed $query
     * @param array $with
     * @return mixed
     */
    protected function applyWith(mixed $query, array $with = []): mixed
    {
        if (empty($with)) {
            return $query;
        }
        if (!$this->isModelClass()) {
            return $query;
        }
        if (method_exists($query, 'with')) {
            return $query->with($with);
        }
        return $query;
    }

    /**
     * 统一获取查询构造器
     * @param mixed $query
     * @return mixed
     */
    protected function getBuilder(mixed $query): mixed
    {
        // 非模型类场景，直接返回原查询
        if (!$this->isModelClass()) {
            return $query;
        }

        // Illuminate ORM
        if ($this->isEloquent) {
            if ($query instanceof \Illuminate\Database\Eloquent\Model) {
                return $query->newQuery();
            }
            return $query;
        }

        // ThinkPHP ORM
        if ($query instanceof \think\Model) {
            return $query->db();
        }
        return $query;
    }

    /**
     * 统一获取主键名
     * @return string
     */
    protected function getPrimaryKey(): string
    {
        if (!$this->isModelClass()) {
            return 'id';
        }
        $model = $this->getModel();
        if ($this->isEloquent) {
            return $model->getKeyName();
        }
        return $model->getPk();
    }

    // --- 通用查询方法 ---

    /**
     * 根据主键ID查询单条记录
     */
    public function findById(int|string $id, array $with = []): mixed
    {
        $query = $this->newQuery();
        $query = $this->applyWith($query, $with);

        if ($this->isModelClass()) {
            return $query->find($id); 
        }

        $primaryKey = $this->getPrimaryKey();
        if ($this->isEloquent) {
             return $query->where($primaryKey, $id)->first();
        } else {
            return $query->where($primaryKey, $id)->find();
        }
    }

    /**
     * 根据主键ID查询批量记录
     */
    public function findByArrayId(array $id, array $with = []): mixed
    {
        $query = $this->newQuery();
        $query = $this->applyWith($query, $with);
        $primaryKey = $this->getPrimaryKey();

        if ($this->isModelClass()) {
            if ($this->isEloquent) {
                 return $query->find($id);
            } else {
                 return $query->whereIn($primaryKey, $id)->select(); 
            }
        } else {
            if ($this->isEloquent) {
                 return $query->whereIn($primaryKey, $id)->get();
            } else {
                 return $query->whereIn($primaryKey, $id)->select(); 
            }
        }
    }
    
    /**
     * 根据条件查询单条记录
     */
    public function findOneBy(array $criteria, array $with = []): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        $query = $this->applyWith($query, $with);

        if ($this->isEloquent) {
            return $query->first();
        }
        return $query->find() ?: null;
    }

    /**
     * 根据条件查询多条记录
     */
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

    /**
     * 分页查询
     */
    public function paginate(array $criteria = [], int $perPage = 15, array $orderBy = [], array $with = []): mixed
    {
        $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
        $query = $this->applyWith($query, $with);

        return $query->paginate($perPage);
    }

    // --- 字段增减方法 ---

    /**
     * 字段自增操作
     */
    public function increment(int|string $id, string $field, int $amount = 1, array $extra = []): bool
    {
        $primaryKey = $this->getPrimaryKey();
        $query = $this->newQuery()->where($primaryKey, $id);

        if ($this->isEloquent) {
            return (bool) $query->increment($field, $amount, $extra);
        } else {
            return (bool) $query->inc($field, $amount)->update($extra);
        }
    }

    /**
     * 字段自减操作
     */
    public function decrement(int|string $id, string $field, int $amount = 1, array $extra = []): bool
    {
        return $this->increment($id, $field, -abs($amount), $extra);
    }

    // --- 写入操作 ---

    /**
     * 新增记录
     */
    public function create(array $data): mixed
    {
        // 1. 模型模式
        if ($this->isModelClass()) {
            // 使用 forward_static_call 兼容不同调用方式
            return forward_static_call([$this->modelClass, 'create'], $data);
        }

        // 2. 表名模式
        $primaryKey = $this->getPrimaryKey();
        
        // 雪花ID/已存在主键场景
        if (isset($data[$primaryKey]) && !empty($data[$primaryKey])) {
            $insertResult = $this->newQuery()->insert($data);
            return $insertResult ? $this->findById($data[$primaryKey]) : null;
        }

        // 自增ID场景
        if ($this->isEloquent) {
            $id = $this->newQuery()->insertGetId($data);
        } else {
            $id = $this->newQuery()->insert($data, true);
        }

        return $this->findById($id);
    }

    /**
     * 通用保存方法（有主键更新，无主键新增）
     */
    public function save(array $data)
    {
        // 1. 表名模式
        if (!$this->isModelClass()) {
            $primaryKey = $this->getPrimaryKey();
            if (isset($data[$primaryKey]) && !empty($data[$primaryKey])) {
                $updateCount = $this->updateBy([$primaryKey => $data[$primaryKey]], $data);
                return $updateCount > 0;
            }
            return $this->create($data);
        }

        // 2. 模型模式
        $model = $this->getModel();
        $primaryKey = $this->isEloquent ? $model->getKeyName() : $model->getPk();

        // 无主键：新增
        if (!isset($data[$primaryKey]) || empty($data[$primaryKey])) {
            if ($this->isEloquent) {
                return $this->modelClass::create($data);
            }
            return $this->modelClass::create($data); 
        }

        // 更新
        $id = $data[$primaryKey];
        $instance = $this->findById($id);
        
        if (!$instance) {
            throw new RuntimeException("Record with ID {$id} not found.");
        }

        if ($this->isEloquent) {
            $instance->fill($data);
        } else {
            $instance->save($data);
            return $instance;
        }

        $instance->save();
        return $instance;
    }

    /**
     * 根据主键ID更新记录
     */
    public function update(int|string $id, array $data): bool
    {
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        if (!is_object($item)) {
            $primaryKey = $this->getPrimaryKey();
            $updateCount = $this->newQuery()->where($primaryKey, $id)->update($data);
            return $updateCount > 0;
        }

        if ($this->isEloquent) {
            $item->fill($data);
        } else {
            $item->data($data);
        }

        $saveResult = $item->save();
        return $saveResult !== false;
    }

    /**
     * 根据条件批量更新记录
     */
    public function updateBy(array $criteria, array $data): int
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        return (int) $query->update($data);
    }

    // --- 删除操作 ---

    /**
     * 根据主键ID删除记录
     */
    public function delete(int|string $id): bool
    {
        if ($this->isModelClass()) {
            return (bool) forward_static_call([$this->modelClass, 'destroy'], $id);
        }

        $primaryKey = $this->getPrimaryKey();
        return (bool) $this->newQuery()->where($primaryKey, $id)->delete();
    }

    /**
     * 根据条件批量删除记录
     */
    public function deleteBy(array $criteria): int
    {
        $query = $this->buildQuery($this->newQuery(), $criteria);
        return (int) $query->delete();
    }

    // --- 统计与原生SQL操作 ---

    /**
     * 聚合查询
     */
    public function aggregate(string $type, array $criteria = [], string $field = '*'): int|float
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

        return is_numeric($result) ? (float)$result : 0;
    }

    /**
     * 事务操作
     */
    public function transaction(\Closure $callback): mixed
    {
        if ($this->isEloquent) {
            return IlluminateDb::transaction($callback);
        }
        return ThinkDb::transaction($callback);
    }

    /**
     * 执行原生查询SQL
     */
    public function query(string $sql, array $bindings = []): array
    {
        if ($this->isEloquent) {
            $result = IlluminateDb::select($sql, $bindings);
            return array_map(fn($item) => (array) $item, $result);
        }
        return ThinkDb::query($sql, $bindings);
    }

    /**
     * 执行原生执行SQL
     */
    public function execute(string $sql, array $bindings = []): int
    {
        if ($this->isEloquent) {
            return IlluminateDb::affectingStatement($sql, $bindings);
        }
        return (int) ThinkDb::execute($sql, $bindings);
    }

    // --- 核心查询条件构建器 ---

    /**
     * 构建查询条件
     */
    protected function buildQuery(mixed $query, array $criteria, array $orderBy = []): mixed
    {
        // 1. 统一转为查询构造器
        $query = $this->getBuilder($query);

        // 2. 多租户自动筛选（通过上下文判断，仅针对纯表或补漏）
        $this->applyTenantFilter($query, $criteria);

        // 3. SELECT 指定字段
        if (!empty($criteria['select'])) {
            $query->select($criteria['select']);
            unset($criteria['select']);
        }

        // 4. DISTINCT 去重
        if (!empty($criteria['distinct'])) {
            $query->distinct();
            unset($criteria['distinct']);
        }

        // 5. LOCK 悲观锁
        if (!empty($criteria['lock'])) {
            if ($this->isEloquent) {
                $query->lockForUpdate();
            } else {
                $query->lock(true);
            }
            unset($criteria['lock']);
        }

        // 6. JOIN 关联查询
        $this->applyJoins($query, $criteria);

        // 7. WHERE NULL / NOT NULL
        $this->applyWhereNull($query, $criteria);

        // 8. WHERE IN / NOT IN
        $this->applyWhereIn($query, $criteria);

        // 9. GROUP BY & HAVING
        $this->applyGroupByAndHaving($query, $criteria);

        // 10. OR 分组查询
        $this->applyOrGroup($query, $criteria);

        // 11. 基础 WHERE 条件
        $this->applyBasicWhere($query, $criteria);

        // 12. ORDER BY 排序
        $this->applyOrderBy($query, $orderBy);

        return $query;
    }

    /**
     * 应用JOIN关联查询
     */
    protected function applyJoins(mixed $query, array &$criteria): void
    {
        foreach (['join', 'leftJoin', 'rightJoin'] as $joinType) {
            if (empty($criteria[$joinType]) || !is_array($criteria[$joinType])) {
                continue;
            }

            foreach ($criteria[$joinType] as $join) {
                $table = $join[0] ?? null;
                $field1 = $join[1] ?? null;
                $operator = $join[2] ?? '=';
                $field2 = $join[3] ?? null;

                if (!$table || !$field1) continue;

                if ($field2 === null && isset($join[2])) {
                    $field2 = $join[2];
                    $operator = '=';
                }

                if (!$this->isEloquent) {
                    $query->$joinType($table, "{$field1} {$operator} {$field2}");
                } else {
                    $query->$joinType($table, $field1, $operator, $field2);
                }
            }
            unset($criteria[$joinType]);
        }
    }

    /**
     * 应用 WHERE NULL / NOT NULL
     */
    protected function applyWhereNull(mixed $query, array &$criteria): void
    {
        if (!empty($criteria['whereNull'])) {
            foreach ((array)$criteria['whereNull'] as $field) {
                $query->whereNull($field);
            }
            unset($criteria['whereNull']);
        }

        if (!empty($criteria['whereNotNull'])) {
            foreach ((array)$criteria['whereNotNull'] as $field) {
                $query->whereNotNull($field);
            }
            unset($criteria['whereNotNull']);
        }
    }

    /**
     * 应用 WHERE IN / NOT IN
     */
    protected function applyWhereIn(mixed $query, array &$criteria): void
    {
        if (!empty($criteria['whereIn'])) {
            foreach ($criteria['whereIn'] as $field => $values) {
                $query->whereIn($field, $values);
            }
            unset($criteria['whereIn']);
        }

        if (!empty($criteria['whereNotIn'])) {
            foreach ($criteria['whereNotIn'] as $field => $values) {
                $query->whereNotIn($field, $values);
            }
            unset($criteria['whereNotIn']);
        }
    }

    /**
     * 应用 GROUP BY & HAVING
     */
    protected function applyGroupByAndHaving(mixed $query, array &$criteria): void
    {
        if (!empty($criteria['groupBy'])) {
            $groupBy = (array) $criteria['groupBy'];
            $query->groupBy(...$groupBy);
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
    }

    /**
     * 应用 OR 分组查询
     */
    protected function applyOrGroup(mixed $query, array &$criteria): void
    {
        if (empty($criteria['or_group']) || !is_array($criteria['or_group'])) {
            return;
        }

        $orGroup = $criteria['or_group'];
        $query->where(function ($subQuery) use ($orGroup) {
            $this->buildQuery($subQuery, $orGroup);
        }, null, null, 'or');

        unset($criteria['or_group']);
    }

    /**
     * 应用基础 WHERE 条件
     */
    protected function applyBasicWhere(mixed $query, array &$criteria): void
    {
        foreach ($criteria as $field => $value) {
            if (in_array($field, ['page', 'limit', 'per_page'])) {
                continue;
            }

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

            if ($field === 'and' && is_array($value)) {
                $callback = function ($q) use ($value) {
                    $this->buildQuery($q, $value);
                };
                $query->where($callback);
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
                $op = $value[0] ?? '=';
                $val = $value[1] ?? $value[0];

                switch (strtolower($op)) {
                    case 'in':
                        $query->whereIn($field, $val);
                        break;
                    case 'not in':
                    case 'not_in':
                        $query->whereNotIn($field, $val);
                        break;
                    case 'between':
                        $query->whereBetween($field, $val);
                        break;
                    case 'not between':
                        $query->whereNotBetween($field, $val);
                        break;
                    case 'like':
                        $query->where($field, 'like', $val);
                        break;
                    case 'not like':
                        $query->where($field, 'not like', $val);
                        break;
                    case 'null':
                        $query->whereNull($field);
                        break;
                    case 'not null':
                        $query->whereNotNull($field);
                        break;
                    case 'exists':
                        if ($val instanceof \Closure) {
                            $query->whereExists($val);
                        }
                        break;
                    default:
                        $query->where($field, $op, $val);
                }
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * 应用 ORDER BY 排序条件
     */
    protected function applyOrderBy(mixed $query, array $orderBy): void
    {
        foreach ($orderBy as $field => $direction) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            if ($this->isEloquent) {
                $query->orderBy($field, $direction);
            } else {
                $query->order($field, $direction);
            }
        }
    }
}