<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: ThinkORMFactory.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\ORM\Factories;

use Framework\Core\App;
use Framework\ORM\Exception\Exception;
use Framework\Utils\Arr;
use think\Collection;
use think\db\Query;
use think\facade\Db;
use think\Model;
use think\Paginator;
use Throwable;

class ThinkphpORMFactory
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
            // 使用 App::make 解析模型
            $this->modelInstance = App::make($class);
            return $this->modelInstance;
        } catch (Throwable $e) {
            throw new Exception('模型加载失败: ' . $e->getMessage());
        }
    }

    /**
     * 核心优化：统一查询条件构建器
     * 适配 ThinkPHP 的 Query 对象
     */
    private function buildQuery(array $where, bool $search = false, ?array $withoutScopes = null): Query
    {
        $query = $this->getModel()->db();
        if (!empty($withoutScopes)) {
            $this->applyScopeRemoval($query, $withoutScopes);
        }
        if ($search) {
            return $this->applySearchScopes($query, $where);
        }
        if (!empty($where)) {
            $this->applyConditions($query, $where);
        }
        return $query;
    }

    private function splitWhere(array $where): array
    {
        $special = [];
        foreach ($where as $key => $condition) {
            if (is_array($condition) && count($condition) === 3) {
                $op = strtolower((string)($condition[1] ?? ''));
                if ($op === 'in' || $op === 'not in') {
                    $special[] = $condition;
                    unset($where[$key]);
                }
            }
        }
        return [$where, $special];
    }

    private function applyConditions(Query $query, array $where): void
    {
        [$normal, $special] = $this->splitWhere($where);
        if (!empty($normal)) {
            $query->where($normal);
        }
        foreach ($special as $condition) {
            if (empty($condition[2])) {
                continue;
            }
            $operator = strtolower((string)$condition[1]);
            $value = Arr::normalize($condition[2]);
            if ($operator === 'in') {
                $query->whereIn($condition[0], $value);
            } elseif ($operator === 'not in') {
                $query->whereNotIn($condition[0], $value);
            }
        }
    }

    private function applyFields(Query $query, array|string $field): void
    {
        $isWildcard = ($field === '*' || ($field === ['*']));
        if ($isWildcard) {
            return;
        }
        if (is_array($field)) {
            $query->field($field);
            return;
        }
        if (!empty($field)) {
            $query->field($field);
        }
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
    public function selectList(array $where, array|string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): ?Collection
    {
        $query = $this->selectModel($where, $field, $page, $limit, $order, $with, $search, $withoutScopes);

        if ($query instanceof Paginator) {
            return $query->getCollection();
        }

        return $query->select();
    }

    /**
     * 获取查询构建器或分页结果
     * @return Query|Paginator
     */
    public function selectModel(array $where, array|string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): Query|Paginator
    {
        $query = $this->buildQuery($where, $search, $withoutScopes);

        $this->applyFields($query, $field);

        // 关联预加载
        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderRaw($order);
        }

        if ($page > 0 && $limit > 0) {
            return $query->paginate([
                'list_rows' => $limit,
                'page'      => $page,
            ]);
        }

        return $query;
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
        // ThinkPHP: count('DISTINCT field')
        return (int) $this->buildQuery($where, $search)->count('DISTINCT ' . $field);
    }

    public function getPk(): string
    {
        return $this->getModel()->getPk();
    }

    public function getTableName(): string
    {
        return $this->getModel()->getName();
    }

    /**
     * 获取一条数据
     */
    public function get($id, array|string|null $field = null, ?array $with = [], string $order = '', ?array $withoutScopes = null): ?Model
    {
        $where = is_array($id) ? $id : [$this->getPk() => $id];
        $query = $this->buildQuery($where, false, $withoutScopes);

        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderRaw($order);
        }

        $this->applyFields($query, $field ?? '*');
        return $query->find();
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

        // ThinkPHP 使用 count() > 0 或 value() 判断
        return $this->buildQuery($map)->count() > 0;
    }

    /**
     * 根据条件获取一条数据
     */
    public function getOne(array $where, array|string|null $field = '*', array $with = []): ?Model
    {
        $query = $this->buildQuery($where);

        if (!empty($with)) {
            $query->with($with);
        }

        $this->applyFields($query, $field ?? '*');
        return $query->find();
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
        return $query->column($field, $key ?: null);
    }

    /**
     * 删除
     */
    public function delete(array|int|string $id, ?string $key = null): int
    {
        try {
            // ThinkPHP destroy 支持 主键、数组主键、闭包条件
            if (is_array($id) && array_is_list($id) && is_null($key)) {
                return (int) $this->getModel()->destroy($id);
            }

            $query = $this->getModel()->db();
            if (is_array($id)) {
                $query->where($id);
            } else {
                $query->where($key ?: $this->getPk(), $id);
            }

            // delete 返回影响行数
            return (int) $query->delete();
        } catch (Throwable $e) {
            throw new Exception("删除失败:" . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * 销毁记录 (直接通过主键)
     */
    public function destroy(mixed $id, bool $force = false): bool
    {
        return $this->getModel()->destroy($id, $force);
    }

    /**
     * 更新
     */
    public function update(string|int|array $id, array $data, ?string $key = null): mixed
    {
        $where = is_array($id) ? $id : [is_null($key) ? $this->getPk() : $key => $id];
        
        // ThinkPHP update 返回影响行数 (int) 或 Model
        // 使用 Query 对象的 update 方法返回 int
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
     */
    public function saveAll(array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        // ThinkPHP saveAll 返回 Collection
        $result = $this->getModel()->saveAll($data);
        return !$result->isEmpty();
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
     * 辅助方法：应用搜索器逻辑 (ThinkPHP withSearch)
     */
    private function applySearchScopes(Query $query, array $where): Query
    {
        [$searchFields, $searchData, $otherWhere] = $this->getSearchData($where);
        if (!empty($searchFields)) {
            $query->withSearch($searchFields, $searchData);
        }
        $filteredWhere = $this->filterWhere($otherWhere);
        if (!empty($filteredWhere)) {
            $this->applyConditions($query, $filteredWhere);
        }
        return $query;
    }

    /**
     * 获取搜索器和搜索条件
     * 适配 ThinkPHP: searchFieldNameAttr
     */
    private function getSearchData(array $where): array
    {
        $searchFields = [];
        $searchData = [];
        $otherWhere = [];
        $model = $this->getModel();
        
        foreach ($where as $key => $value) {
            // ThinkPHP 搜索器方法名规则：searchFieldNameAttr
            $method = 'search' . self::studly($key) . 'Attr';
            
            if (method_exists($model, $method)) {
                $searchFields[] = $key;
                $searchData[$key] = $value;
            } else {
                // 保留特殊字段逻辑
                if (!in_array($key, ['timeKey', 'store_stock', 'integral_time'])) {
                    if (!is_array($value)) {
                        $otherWhere[$key] = $value;
                    } else {
                        // 复杂条件保留，key 可能为数字索引
                        $otherWhere[] = $value;
                    }
                }
            }
        }
        return [$searchFields, $searchData, $otherWhere];
    }

    /**
     * 根据搜索器获取内容 (内部使用)
     */
    protected function withSearchSelect(array $where, bool $search): Query
    {
        return $this->buildQuery($where, $search);
    }

    protected function filterWhere(array $where = []): array
    {
        $fields = $this->getModel()->getTableFields();

        if (empty($fields)) {
            return $where; 
        }

        foreach ($where as $key => $item) {
            if (is_int($key)) {
                if (is_array($item) && isset($item[0]) && !in_array($item[0], $fields)) {
                    unset($where[$key]);
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
    public function search(array $where = [], bool $search = true): Model|Query
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
        $pk = $keyField ?: $this->getPk();
        $query = $this->getModel()->where($pk, $key);
        
        // ThinkPHP Db::raw
        return $query->update([
            $incField => Db::raw("COALESCE($incField, 0) + CAST($inc AS DECIMAL(20, $acc))")
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
     * 适配 ThinkPHP: dec() / inc()
     */
    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);

        // ThinkPHP 链式操作: where(...)->dec(字段, 值)->inc(字段, 值)->update()
        // 必须加上 where stock >= num 保证安全
        $query->where($stock, '>=', $num);

        $result = $query->dec($stock, $num)
              ->inc($sales, $num)
              ->update();
        
        return $result > 0;
    }

    /**
     * 加库存减销量 (原子更新)
     */
    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $query = $this->buildQuery($where);

        // 可选：检查销量是否足够减
        $query->where($sales, '>=', $num);

        $result = $query->inc($stock, $num)
              ->dec($sales, $num)
              ->update();

        return $result > 0;
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

    protected function applyScopeRemoval(Query $query, ?array $scopes): void
    {
        if (empty($scopes)) return;
        // ThinkPHP 的 Global Scope 移除逻辑比较特殊
        // 如果是软删除，通常使用 removeOption('soft_delete') 或 withTrashed()
        // 这里做一个简单的模拟兼容
        foreach ($scopes as $scope) {
            if ($scope === 'soft_delete' || str_contains($scope, 'SoftDelete')) {
                // 假设是想查询包含已删除的数据
                if (method_exists($query, 'withTrashed')) {
                    $query->withTrashed();
                }
            }
        }
    }

    public function tableExists($table): bool
    {
        try {
            $res = Db::query("SHOW TABLES LIKE ?", [$table]);
            return !empty($res);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
