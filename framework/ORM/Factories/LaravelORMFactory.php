<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\ORM\Factories;

use Framework\Core\App;
use Framework\ORM\Exception\Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LaravelORMFactory // implements ORMAdapterInterface
{
    private mixed $modelClass;

    private ?Model $modelInstance = null;

    /**
     * 构造函数.
     * @param null|Model|string $model 模型类名或实例
     */
    public function __construct(Model|string|null $model = null)
    {
        // 如果传入的是对象，直接持有实例并获取类名
        if (is_object($model)) {
            $this->modelInstance = $model;
            $this->modelClass    = get_class($model);
        } else {
            $this->modelClass = $model;
        }
    }

    /**
     * 获取模型实例 (懒加载，使用 App::make 兼容依赖注入).
     */
    public function getModel(): Model
    {
        if ($this->modelInstance) {
            return $this->modelInstance;
        }

        try {
            $class = $this->modelClass;
            if (! class_exists($class)) {
                throw new Exception($class . ' 不是一个有效的模型类');
            }
            // 使用 App::make 解析模型
            $this->modelInstance = App::make($class);
            return $this->modelInstance;
        } catch (\Throwable $e) {
            throw new Exception('模型加载失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取条数.
     */
    public function count(array $where = [], bool $search = true): int
    {
        return $this->search($where, $search)->count();
    }

    /**
     * 查询列表.
     */
    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false): Collection
    {
        $query = $this->selectModel($where, $field, $page, $limit, $order, $with, $search);
        return $query->get();
    }

    /**
     * 构建查询模型 (返回 Builder).
     */
    public function selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false): Builder
    {
        if ($search) {
            $query = $this->search($where);
        } else {
            $query = $this->getModel()->query()->where($where);
        }

        // 字段选择
        if ($field !== '*' && $field !== '') {
            // Laravel select 接受数组或可变参数
            $fieldArr = is_array($field) ? $field : explode(',', $field);
            $query->select($fieldArr);
        }

        // 分页 (Laravel Builder 使用 forPage 或 offset/limit)
        if ($page > 0 && $limit > 0) {
            $query->forPage($page, $limit);
        }

        // 排序
        if ($order !== '') {
            $query->orderByRaw($order);
        }

        // 关联查询
        if (! empty($with)) {
            $query->with($with);
        }

        return $query;
    }

    /**
     * 获取某些条件总数.
     */
    public function getCount(array $where): int
    {
        return $this->getModel()->where($where)->count();
    }

    /**
     * 获取唯一记录数量.
     */
    public function getDistinctCount(array $where, string $field, bool $search = true): int
    {
        $query = $search ? $this->search($where) : $this->getModel()->where($where);
        return $query->distinct()->count($field);
    }

    /**
     * 获取主键.
     */
    public function getPk(): string
    {
        return $this->getModel()->getKeyName();
    }

    /**
     * 获取表名.
     */
    public function getTableName(): string
    {
        return $this->getModel()->getTable();
    }

    /**
     * 获取一条数据.
     * @param mixed $id
     */
    public function get($id, ?array $field = [], ?array $with = [], string $order = ''): mixed
    {
        $pk = $this->getPk();

        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$pk => $id];
        }

        $query = $this->getModel()->newQuery();
        $query->where($where);

        if (! empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderByRaw($order);
        }

        $select = $field ?? ['*'];
        return $query->select($select)->first();
    }

    /**
     * 查询是否存在.
     * @param mixed $map
     */
    public function be($map, string $field = ''): bool
    {
        if (! is_array($map) && empty($field)) {
            $field = $this->getPk();
        }
        $map = ! is_array($map) ? [$field => $map] : $map;

        return $this->getModel()->where($map)->exists();
    }

    /**
     * 根据条件获取一条数据.
     */
    public function getOne(array $where, ?string $field = '*', array $with = []): ?Model
    {
        $fieldArray = $field === '*' ? ['*'] : (is_string($field) ? explode(',', $field) : $field);
        return $this->getModel()->with($with)->where($where)->select($fieldArray)->first();
    }

    /**
     * 获取某字段的值
     * @param mixed $where
     */
    public function value($where, ?string $field = null): mixed
    {
        $pk = $this->getPk();
        // 如果使用了搜索器逻辑，需要调用 search
        // 这里假设 value 也要走 setWhere 的逻辑
        return $this->search($this->setWhere($where))->value($field ?: $pk);
    }

    /**
     * 获取某个字段数组.
     */
    public function getColumn(array $where, string $field, string $key = ''): array
    {
        $query = $this->getModel()->where($where);
        if ($key) {
            return $query->pluck($field, $key)->toArray();
        }
        return $query->pluck($field)->toArray();
    }

    /**
     * 删除.
     */
    public function delete(array|int|string $id, ?string $key = null): mixed
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }

        return $this->getModel()->where($where)->delete();
    }

    /**
     * 销毁记录 (Destroy 是根据主键删除).
     */
    public function destroy(mixed $id, bool $force = false): bool
    {
        $model = $this->getModel();
        // Eloquent destroy 方法接收 ID 或 数组
        $count = $model::destroy($id);
        return $count > 0;
    }

    /**
     * 更新.
     */
    public function update(array|int|string $id, array $data, ?string $key = null): mixed
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }

        // Eloquent 的 update 返回受影响行数 (int)
        return $this->getModel()->where($where)->update($data);
    }

    /**
     * 批量更新.
     */
    public function batchUpdate(array $ids, array $data, ?string $key = null): mixed
    {
        return $this->getModel()
            ->whereIn(is_null($key) ? $this->getPk() : $key, $ids)
            ->update($data);
    }

    /**
     * 保存/新增.
     */
    public function save(array $data): mixed
    {
        return $this->getModel()->create($data);
    }

    /**
     * 批量插入.
     */
    public function saveAll(array $data): bool
    {
        // Eloquent insert 返回 bool
        return $this->getModel()->insert($data);
    }

    /**
     * 获取字段值
     * @param mixed $value
     */
    public function getFieldValue($value, string $field, ?string $valueKey = null, ?array $where = []): mixed
    {
        if ($valueKey) {
            $where[$valueKey] = $value;
        } else {
            $where[$this->getPk()] = $value;
        }
        return $this->getModel()->where($where)->value($field);
    }

    public function search(array $where = [], bool $search = true): Builder|Model
    {
        if ($where) {
            return $this->withSearchSelect($where, $search);
        }
        return $this->getModel();
    }

    // =========================================================================
    // 数值计算与业务辅助
    // =========================================================================

    public function sum(array $where, string $field, bool $search = false): float
    {
        if ($search) {
            return (float) $this->search($where)->sum($field);
        }
        return (float) $this->getModel()->where($where)->sum($field);
    }

    public function bcInc($key, string $incField, string $inc, ?string $keyField = null, int $acc = 2): bool
    {
        return $this->bc($key, $incField, $inc, $keyField, 1, $acc);
    }

    public function bcDec($key, string $decField, string $dec, ?string $keyField = null, int $acc = 2): bool
    {
        return $this->bc($key, $decField, $dec, $keyField, 2, $acc);
    }

    /**
     * 高精度计算 (PHP侧计算，保持与 ThinkORMFactory 一致).
     * @param mixed $key
     */
    public function bc($key, string $field, string $value, ?string $keyField = null, int $type = 1, int $acc = 2): bool
    {
        $result = $keyField === null ? $this->get($key) : $this->getOne([$keyField => $key]);

        if (! $result) {
            return false;
        }

        $currentVal = $result->{$field} ?? 0;
        $newValue   = 0;

        if ($type === 1) {
            // 加法
            $newValue = bcadd((string) $currentVal, (string) $value, $acc);
        } elseif ($type === 2) {
            // 减法
            if ((float) $currentVal < (float) $value) {
                return false;
            }
            $newValue = bcsub((string) $currentVal, (string) $value, $acc);
        }

        $result->{$field} = $newValue;
        return $result->save();
    }

    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $product = $this->getModel()->where($where)->first();
        if ($product) {
            // 使用 DB::raw 实现原子更新，或者分别 update
            // Laravel 的 increment/decrement 是立即执行的
            // 同时更新两个字段建议用 update
            return (bool) $product->update([
                $stock => DB::raw("`{$stock}` - {$num}"),
                $sales => DB::raw("`{$sales}` + {$num}"),
            ]);
        }
        return false;
    }

    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $product = $this->getModel()->where($where)->first();
        if ($product) {
            $currentSales = $product->{$sales};
            $salesNum     = $num;
            if ($num > $currentSales) {
                $salesNum = $currentSales;
            }

            return (bool) $product->update([
                $stock => DB::raw("`{$stock}` + {$num}"),
                $sales => DB::raw("`{$sales}` - {$salesNum}"),
            ]);
        }
        return true;
    }

    public function getMax(array $where = [], string $field = ''): mixed
    {
        return $this->getModel()->where($where)->max($field);
    }

    public function getMin(array $where = [], string $field = ''): mixed
    {
        return $this->getModel()->where($where)->min($field);
    }

    protected function setWhere($where, ?string $key = null): array
    {
        if (! is_array($where)) {
            $where = [is_null($key) ? $this->getPk() : $key => $where];
        }
        return $where;
    }

    /**
     * 构建搜索查询.
     */
    protected function withSearchSelect(array $where, bool $search): Builder
    {
        [$withScopes, $scopeValues, $otherWhere] = $this->getSearchData($where);

        $query = $this->getModel()->newQuery();

        // 应用 Scopes
        foreach ($withScopes as $key) {
            // Laravel 调用 scope 的方式: $query->scopeName($value) -> 调用时写 $query->name($value)
            // 例如: 定义了 scopePhone, 调用时 $query->phone($val)
            $scopeMethod = Str::camel($key);
            $value       = $scopeValues[$key] ?? null;

            // 动态调用 Scope
            $query->{$scopeMethod}($value);
        }

        // 应用普通 Where
        if (! empty($otherWhere)) {
            // filterWhere 过滤字段
            $filteredWhere = $this->filterWhere($otherWhere);
            if (! empty($filteredWhere)) {
                $query->where($filteredWhere);
            }
        }

        return $query;
    }

    protected function filterWhere(array $where = []): array
    {
        // Laravel 获取字段列表比较麻烦，通常使用 $fillable
        // 或者直接查 Schema (性能低)。
        // 这里假设 $model->getFillable() 已经定义。
        // 如果没有定义 fillable，为了安全起见，建议不做过滤或者自行实现 getTableFields 缓存

        $fillable = $this->getModel()->getFillable();

        // 如果 fillable 为空 ( guarded = [] )，则不过滤，假设全部通过
        if (empty($fillable)) {
            return $where;
        }

        $fields = $fillable;
        // 加上主键和时间戳
        $fields[] = $this->getPk();
        if ($this->getModel()->usesTimestamps()) {
            $fields[] = $this->getModel()->getCreatedAtColumn();
            $fields[] = $this->getModel()->getUpdatedAtColumn();
        }

        foreach ($where as $key => $item) {
            // $item 结构可能是 [$field, '=', $val]
            $fieldName = $item[0] ?? null;
            if ($fieldName && ! in_array($fieldName, $fields)) {
                unset($where[$key]);
            }
        }
        return $where;
    }

    // =========================================================================
    // 搜索器逻辑
    // =========================================================================

    /**
     * 解析搜索条件
     * 适配 Laravel Scopes: scopeName($query, $value).
     */
    private function getSearchData(array $where): array
    {
        $withScopes  = []; // 存储需要调用的 Scope 名称
        $scopeValues = []; // 存储 Scope 对应的参数
        $otherWhere  = []; // 普通 Where 条件

        $model = $this->getModel();
        // 使用 modelInstance 获取类名确保准确
        $className = get_class($model);

        // 检查方法是否存在需要反射或 method_exists
        // Laravel Scope 方法名规则: scope + Name

        foreach ($where as $key => $value) {
            // Laravel 中 Scope 是驼峰命名，例如 scopeStatus
            $method = 'scope' . Str::studly($key);

            if (method_exists($model, $method)) {
                $withScopes[]      = $key; // 记录原始 key，调用时转 studly
                $scopeValues[$key] = $value;
            } else {
                // 过滤非数据库字段的逻辑需要谨慎，Laravel 默认没有 getTableFields
                // 这里保留基本的过滤逻辑
                if (! in_array($key, ['timeKey', 'store_stock', 'integral_time'])) {
                    if (! is_array($value)) {
                        $otherWhere[] = [$key, '=', $value];
                    } elseif (count($value) === 3) {
                        $otherWhere[] = $value;
                    }
                }
            }
        }
        return [$withScopes, $scopeValues, $otherWhere];
    }
}
