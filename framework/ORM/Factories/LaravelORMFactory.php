<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: LaravelORMFactory.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\ORM\Factories;

use Framework\Core\App;
use Framework\ORM\Exception\Exception;
use Framework\Utils\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
#use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class LaravelORMFactory
{
    private mixed $modelClass;

    private ?Model $modelInstance = null;

    /**
     * 构造函数.
     * @param Model|string|null $model 模型类名或实例
     */
    public function __construct(Model|string|null $model = null)
    {
        if (is_object($model)) {
            $this->modelInstance = $model;
            $this->modelClass = get_class($model);
        } else {
            $this->modelClass = $model;
        }
    }

    /**
     * 获取模型实例 (懒加载).
     */
    public function getModel(): Model
    {
        if ($this->modelInstance) {
            return $this->modelInstance;
        }

        try {
            $class = $this->modelClass;
            if (!class_exists($class)) {
                throw new Exception($class . ' 不是一个有效的模型类');
            }
            $this->modelInstance = App::make($class);
            return $this->modelInstance;
        } catch (Throwable $e) {
            throw new Exception('模型加载失败: ' . $e->getMessage());
        }
    }

    /**
     * 核心优化：统一查询条件构建器
     * 将重复的 IN/NOT IN 解析逻辑收敛到此处
     */
    private function buildQuery(array $where, bool $search = false, ?array $withoutScopes = null): Builder
    {
        // 1. 获取基础查询构建器
        $query = $this->getModel()->query();

        // 2. 移除作用域
        if (!empty($withoutScopes)) {
            $this->applyScopeRemoval($query, $withoutScopes);
        }

        // 3. 如果启用搜索器模式
        if ($search) {
            return $this->applySearchScopes($query, $where);
        }

        // 4. 标准条件处理
        $normalWhere = [];
        foreach ($where as $key => $condition) {
            // 处理特殊数组格式: ['field', 'in', [1,2]]
            if (is_array($condition) && count($condition) === 3) {
                $operator = strtolower((string)$condition[1]);
                if (in_array($operator, ['in', 'not in'], true)) {
                    if (empty($condition[2])) {
                        continue;
                    }
                    $field = $condition[0];
                    $values = Arr::normalize($condition[2]);
                    
                    if ($operator === 'in') {
                        $query->whereIn($field, $values);
                    } else {
                        $query->whereNotIn($field, $values);
                    }
                    continue; // 已处理，跳过加入普通条件
                }
            }
            
            // 正常的键值对条件或 ['field', '=', 'val'] 格式保留
            $normalWhere[$key] = $condition;
        }

        if (!empty($normalWhere)) {
            $query->where($normalWhere);
        }

        return $query;
    }

    /**
     * 获取条数
     */
    public function count(array $where = [], bool $search = false): int
    {
        return $this->buildQuery($where, $search)->count();
    }

    /**
     * 查询列表
     */
    /*
    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): ?Collection
    {
        $query = $this->selectModel($where, $field, $page, $limit, $order, $with, $search, $withoutScopes);

        if ($page > 0 && $limit > 0 && $query instanceof LengthAwarePaginator) {
             // selectModel 如果处理了分页会返回 Paginator，这里可能需要防御性编程，
             // 但根据 selectModel 的逻辑，它返回的是 Builder 或 Paginator。
             // 如果 selectModel 返回了 Builder (没分页参数)，这里再调 get。
             // 如果 selectModel 返回了 Paginator (有分页参数)，直接取 collection。
             return $query->getCollection();
        }
        
        // 如果 selectModel 返回的是 Builder
        if ($query instanceof Builder) {
            if ($page > 0 && $limit > 0) {
                 return $query->paginate($limit, ['*'], 'page', $page)->getCollection();
            }
            return $query->get();
        }

        return $query instanceof LengthAwarePaginator ? $query->getCollection() : null;
    }
    */

    /**
     * 获取查询构建器或分页结果
     */
    /*
    public function selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): Builder|\Illuminate\Database\Query\Builder|LengthAwarePaginator|null
    {
        $query = $this->buildQuery($where, $search, $withoutScopes);

        if ($field !== '*') {
            $query->selectRaw($field);
        }

        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderByRaw($order);
        }

        // 注意：如果在 selectModel 里就执行了 paginate，返回类型会变
        if ($page > 0 && $limit > 0) {
            return $query->paginate($limit, ['*'], 'page', $page);
        }

        return $query;
    }
    */

    /**
     * 固化契约：
     * selectModel 只返回 Builder（含 Eloquent\Builder 或 BaseQueryBuilder）
     * 不再做分页，也不返回 LengthAwarePaginator
     */
    public function selectModel(
        array $where,
        string $field = '*',
        string $order = '',
        array $with = [],
        bool $search = false,
        ?array $withoutScopes = null
    ): Builder|\Illuminate\Database\Query\Builder {

        $query = $this->buildQuery($where, $search, $withoutScopes);

        if ($field !== '*') {
            $query->selectRaw($field);
        }

        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderByRaw($order);
        }

        // ❗绝不分页
        // ❗绝不返回 paginator
        return $query;
    }



    /**
     * selectList 永远返回 Collection
     * 分页与否由调用者指定 page/limit
     */
    public function selectList(
        array $where,
        string $field = '*',
        int $page = 0,
        int $limit = 0,
        string $order = '',
        array $with = [],
        bool $search = false,
        ?array $withoutScopes = null
    ): Collection {

        $query = $this->selectModel($where, $field, $order, $with, $search, $withoutScopes);

        // 分页模式
        if ($page > 0 && $limit > 0) {
            return $query->paginate($limit, ['*'], 'page', $page)->getCollection();
        }

        // 普通获取
        return $query->get();
    }


    /**
     * 获取条数 (别名)
     */
    public function getCount(array $where): int
    {
        return $this->buildQuery($where)->count();
    }

    /**
     * 计算符合条件的唯一记录数量
     */
    public function getDistinctCount(array $where, string $field, bool $search = true): int
    {
        return $this->buildQuery($where, $search)->distinct()->count($field);
    }

    public function getPk(): string
    {
        return $this->getModel()->getKeyName();
    }

    public function getTableName(): string
    {
        return $this->getModel()->getTable();
    }

    /**
     * 获取一条数据
     */
    public function get($id, ?array $field = null, ?array $with = [], string $order = '', ?array $withoutScopes = null): ?Model
    {
        $where = is_array($id) ? $id : [$this->getPk() => $id];
        
        $query = $this->buildQuery($where, false, $withoutScopes);

        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderByRaw($order);
        }

        return $query->select($field ?? ['*'])->first();
    }

    /**
     * 查询一条数据是否存在
     */
    public function be($map, string $field = ''): bool
    {
        if (!is_array($map) && empty($field)) {
            $field = $this->getPk();
        }
        $map = !is_array($map) ? [$field => $map] : $map;

        return $this->buildQuery($map)->exists();
    }

    /**
     * 根据条件获取一条数据
     */
    public function getOne(array $where, ?string $field = '*', array $with = []): ?Model
    {
        $fieldArray = $field === '*' ? ['*'] : explode(',', $field);
        $query = $this->buildQuery($where);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->select($fieldArray)->first();
    }

    /**
     * 获取某字段的值
     */
    public function value($where, ?string $field = null): mixed
    {
        $pk = $this->getPk();
        $where = $this->setWhere($where);
        
        return $this->buildQuery($where)->value($field ?? $pk);
    }

    /**
     * 获取某个字段数组
     */
    public function getColumn(array $where, string $field, string $key = ''): array
    {
        $query = $this->buildQuery($where);

        if ($key) {
            return $query->pluck($field, $key)->toArray();
        }

        return $query->pluck($field)->toArray();
    }

    /**
     * 删除
     */
    public function delete(array|int|string $id, ?string $key = null): int
    {
        try {
            // 如果是纯ID列表（非关联数组），直接 destroy 效率更高且同样触发事件
            if (is_array($id) && array_is_list($id)) {
                return $this->getModel()->destroy($id);
            }

            $query = $this->getModel()->query();
            if (is_array($id)) {
                $query->where($id);
            } else {
                $query->where($key ?: $this->getPk(), $id);
            }

            $models = $query->get();
            $count = 0;
            foreach ($models as $model) {
                if ($model->delete()) {
                    $count++;
                }
            }
            return $count;
        } catch (Throwable $e) {
            throw new Exception("删除失败:" . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * 销毁记录 (直接通过主键)
     */
    public function destroy(mixed $id, bool $force = false): bool
    {
        if ($force) {
            // 强制删除逻辑，如果模型使用了 SoftDeletes
            $model = $this->getModel();
            if (method_exists($model, 'forceDelete')) {
                // 需要先查出来再 forceDelete，或者用 query
                $count = $model->withTrashed()->whereIn($this->getPk(), (array)$id)->forceDelete();
                return $count > 0;
            }
        }
        return $this->getModel()->destroy($id) > 0;
    }

    /**
     * 更新
     */
    public function update(string|int|array $id, array $data, ?string $key = null): mixed
    {
        $where = is_array($id) ? $id : [is_null($key) ? $this->getPk() : $key => $id];
        // 复用 buildQuery 逻辑，这里 search 为 false
        return $this->buildQuery($where)->update($data);
    }

    protected function setWhere($where, ?string $key = null): array
    {
        if (!is_array($where)) {
            $where = [is_null($key) ? $this->getPk() : $key => $where];
        }
        return $where;
    }

    /**
     * 批量更新
     */
    public function batchUpdate(array $ids, array $data, ?string $key = null): bool
    {
        return (bool) $this->getModel()
            ->whereIn(is_null($key) ? $this->getPk() : $key, $ids)
            ->update($data);
    }

    /**
     * 保存返回模型
     */
    public function save(array $data): ?Model
    {
        return $this->getModel()->create($data);
    }

    /**
     * 批量插入
     * 优化：使用 insert() 批量插入数据库（不触发模型事件），或者保持循环 save()（触发事件）。
     * 原逻辑是循环 save，这里保持原逻辑但优化写法。
     */
    public function saveAll(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        // 使用事务保证原子性
        return DB::transaction(function () use ($data) {
            foreach ($data as $item) {
                if (!$this->getModel()->create($item)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * 获取某字段内的值
     */
    public function getFieldValue($value, string $field, ?string $valueKey = null, ?array $where = []): mixed
    {
        if ($valueKey) {
            $where[$valueKey] = $value;
        } else {
            $where[$this->getPk()] = $value;
        }

        return $this->buildQuery($where)->value($field);
    }

    /**
     * 辅助方法：应用搜索器逻辑
     */
    private function applySearchScopes(Builder $query, array $where): Builder
    {
        [$with, $withValues, $otherWhere] = $this->getSearchData($where);

        // 应用 Scope
        foreach ($with as $item) {
            $func = self::studly($item);
            // 直接调用，因为 getSearchData 已经检查过 method_exists
            // 实际上 Laravel 的 scope 调用是 scopeName($query, $value)
            // 这里原本的逻辑是 $query->$func($value)，这是利用了 __call 魔法方法
            $query->$func($withValues[$item] ?? null);
        }

        // 过滤并应用剩余条件
        $filteredWhere = $this->filterWhere($otherWhere);
        if (!empty($filteredWhere)) {
            $query->where($filteredWhere);
        }

        return $query;
    }

    /**
     * 获取搜索器和搜索条件
     */
    private function getSearchData(array $where): array
    {
        $with = [];
        $withValues = [];
        $otherWhere = [];
        $model = $this->getModel();
        
        // 优化：不每次都实例化 ReflectionClass，使用 method_exists 检查 scope
        foreach ($where as $key => $value) {
            // Laravel scope 方法名规则：scopeName
            $method = 'scope' . self::studly($key);
            
            if (method_exists($model, $method)) {
                $with[] = $key;
                $withValues[$key] = $value;
            } else {
                // 保留特殊字段逻辑
                if (!in_array($key, ['timeKey', 'store_stock', 'integral_time'])) {
                    if (!is_array($value)) {
                        $otherWhere[] = [$key, '=', $value];
                    } elseif (count($value) === 3) {
                        $otherWhere[] = $value;
                    }
                }
            }
        }
        return [$with, $withValues, $otherWhere];
    }

    /**
     * 根据搜索器获取内容 (内部使用)
     */
    protected function withSearchSelect(array $where, bool $search): Builder
    {
        return $this->buildQuery($where, $search);
    }

    protected function filterWhere(array $where = []): array
    {
        $fields = $this->getModel()->getFillable(); // 注意：getFields() 不是标准 Eloquent 方法，通常是 getFillable() 或需自定义
        // 如果你的 Base Model 实现了 getFields，请保留 getFields
        if (method_exists($this->getModel(), 'getFields')) {
            $fields = $this->getModel()->getFields();
        }

        // 如果 fields 为空（未定义 fillable 且不是 guarded），这步过滤可能会导致条件全丢，需谨慎
        if (empty($fields)) {
            return $where; 
        }

        foreach ($where as $key => $item) {
            // 如果 $where 是 [['id','=',1]] 这种格式，$key 是数字索引，不能过滤
            if (is_int($key)) {
                // 如果是索引数组，检查其中的列名
                if (is_array($item) && isset($item[0]) && !in_array($item[0], $fields)) {
                     // 复杂判断，暂且略过或根据需求保留
                }
                continue;
            }
            
            if (!in_array($key, $fields)) {
                unset($where[$key]);
            }
        }
        return $where;
    }

    /**
     * 搜索
     */
    public function search(array $where = [], bool $search = true): mixed
    {
        if ($where) {
            return $this->buildQuery($where, $search);
        }
        return $this->getModel();
    }

    /**
     * 求和
     */
    public function sum(array $where, string $field, bool $search = false): float
    {
        return (float) $this->buildQuery($where, $search)->sum($field);
    }

    /**
     * 高精度加法
     */
    public function bcInc(mixed $key, string $incField, string $inc, ?string $keyField = null, int $acc = 2): bool
    {
        $model = $this->getModel();
        $query = $keyField ? $model->where($keyField, $key) : $model->where($model->getKeyName(), $key);
        
        // 修正 DB 调用，确保使用 Illuminate\Support\Facades\DB
        return $query->update([
            $incField => DB::raw("COALESCE($incField, 0) + CAST($inc AS DECIMAL(20, $acc))")
        ]) > 0;
    }

    /**
     * 高精度 减法
     */
    public function bcDec($key, string $decField, string $dec, ?string $keyField = null, int $acc = 2): bool
    {
        return $this->bc($key, $decField, $dec, $keyField, 2, $acc);
    }

    /**
     * 高精度计算并保存 (应用层计算)
     */
    public function bc($key, string $field, string $value, ?string $keyField = null, int $type = 1, int $acc = 2): bool
    {
        $result = $keyField === null ? $this->get($key) : $this->getOne([$keyField => $key]);
        if (!$result) return false;
        
        $current = (string) ($result->{$field} ?? '0');
        $newValue = '0';

        if ($type === 1) {
            $newValue = bcadd($current, $value, $acc);
        } elseif ($type === 2) {
            if (bccomp($current, $value, $acc) < 0) return false;
            $newValue = bcsub($current, $value, $acc);
        }
        
        $result->{$field} = $newValue;
        return $result->save();
    }

    /**
     * 减库存加销量 (原子更新)
     * 
     * 优化：原代码先查后改不安全。这里改为直接 Update 语句。
     * 原逻辑：decrement()->increment() 是错误的，无法链式调用。
     * 正确逻辑：使用 updateRaw
     */
    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);

        // 检查库存是否充足
        $query->where($stock, '>=', $num);

        return $query->update([
            $stock => DB::raw("$stock - $num"),
            $sales => DB::raw("$sales + $num")
        ]) > 0;
    }

    /**
     * 加库存减销量 (原子更新)
     */
    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);

        // 检查销量是否足够减（可选业务逻辑，防止负销量）
        $query->where($sales, '>=', $num);

        return $query->update([
            $stock => DB::raw("$stock + $num"),
            $sales => DB::raw("$sales - $num")
        ]) > 0;
    }

    /**
     * 获取最大值
     */
    public function getMax(array $where = [], string $field = ''): mixed
    {
        return $this->buildQuery($where)->max($field);
    }

    /**
     * 获取最小值
     */
    public function getMin(array $where = [], string $field = ''): mixed
    {
        return $this->buildQuery($where)->min($field);
    }

    private static function studly(string $string): string
    {
        $string = str_replace(['-', '_'], ' ', $string);
        return str_replace(' ', '', ucwords($string));
    }

    protected function applyScopeRemoval(Builder $query, ?array $scopes): void
    {
        if (empty($scopes)) return;
        foreach ($scopes as $scope) {
            if (is_string($scope) && class_exists($scope)) {
                $query->withoutGlobalScope($scope);
            } elseif (is_string($scope)) {
                // 注意：Laravel Builder 没有 withoutNamedScope 方法，
                // 通常通过不调用该 scope 来实现。这里假设是一个自定义宏或扩展。
                // 如果是 Global Scope 的名称（字符串）：
                $query->withoutGlobalScope($scope);
            } elseif ($scope instanceof \Closure) {
                // 闭包无法直接移除，这通常用于 withoutGlobalScope 的闭包对象
                $query->withoutGlobalScope($scope);
            }
        }
    }

    protected function isInCondition(array $condition): bool
    {
        return count($condition) === 3
            && in_array(strtolower($condition[1] ?? ''), ['in', 'not in'], true);
    }

    public function tableExists($table): bool
    {
        return DB::schema()->hasTable($table);
    }
}