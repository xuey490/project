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

class ThinkORMFactory
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
        // 1. 获取基础查询对象 (ThinkPHP中通常用 db() 或 直接静态调用)
        $query = $this->getModel()->db();

        // 2. 移除全局作用域 (ThinkPHP SoftDelete 是通过 withoutField 'delete_time' 或 removeOption 实现)
        // ThinkPHP 的 Global Scope 机制与 Laravel 不同，通常是通过 base query 或 global scope 类
        if (!empty($withoutScopes)) {
            $this->applyScopeRemoval($query, $withoutScopes);
        }

        // 3. 如果启用搜索器模式 (ThinkPHP withSearch)
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
                    continue; 
                }
                // 处理 ['field', '=', 'val'] 格式，ThinkPHP 原生支持
                $query->where([$condition]);
                continue;
            }
            
            // 正常的键值对条件
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
    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): ?Collection
    {
        // 获取 Query 对象
        $query = $this->selectModel($where, $field, $page, $limit, $order, $with, $search, $withoutScopes);

        // 如果 selectModel 已经处理了分页 (ThinkPHP 翻页会返回 Paginator 对象)
        if ($query instanceof Paginator) {
            // 获取 Collection 数据集合
            return $query->getCollection();
        }

        // 正常的列表查询
        return $query->select();
    }

    /**
     * 获取查询构建器或分页结果
     * @return Query|Paginator
     */
    public function selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false, ?array $withoutScopes = null): Query|Paginator
    {
        $query = $this->buildQuery($where, $search, $withoutScopes);

        // 字段选择
        if ($field !== '*') {
            $query->field($field);
        }

        // 关联预加载
        if (!empty($with)) {
            $query->with($with);
        }

        // 排序
        if ($order !== '') {
            $query->orderRaw($order);
        }

        // 分页 (ThinkPHP page 方法: page(页码, 每页数量))
        if ($page > 0 && $limit > 0) {
            // 这里为了返回 Paginator 对象以便获取更多分页信息，使用 paginate
            // 如果仅需要数据，也可以 use page($page, $limit)->select()
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
    public function get($id, ?array $field = null, ?array $with = [], string $order = '', ?array $withoutScopes = null): ?Model
    {
        $where = is_array($id) ? $id : [$this->getPk() => $id];
        
        $query = $this->buildQuery($where, false, $withoutScopes);

        if (!empty($with)) {
            $query->with($with);
        }

        if ($order !== '') {
            $query->orderRaw($order);
        }

        return $query->field($field ?? '*')->find();
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
    public function getOne(array $where, ?string $field = '*', array $with = []): ?Model
    {
        $query = $this->buildQuery($where);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->field($field)->find();
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

        // 1. 调用 ThinkPHP 的 withSearch
        if (!empty($searchFields)) {
            $query->withSearch($searchFields, $searchData);
        }

        // 2. 过滤并应用剩余条件
        $filteredWhere = $this->filterWhere($otherWhere);
        if (!empty($filteredWhere)) {
            // 递归处理一下 $filteredWhere 里的 IN 条件，
            // 但因为 $otherWhere 已经是结构化的了，直接给 where 即可
            // 如果存在 ['id', 'in', [...]] 这种格式，需要 buildQuery 的逻辑
            // 这里简化处理：复用 buildQuery 的部分逻辑，或者直接循环
            foreach ($filteredWhere as $key => $val) {
                 if (is_array($val) && count($val) === 3 && in_array(strtolower($val[1]), ['in', 'not in'])) {
                     $op = strtolower($val[1]);
                     $v = Arr::normalize($val[2]);
                     $op === 'in' ? $query->whereIn($val[0], $v) : $query->whereNotIn($val[0], $v);
                 } else {
                     $query->where([$key => $val]);
                 }
            }
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
        // ThinkPHP 获取表字段: $model->getTableFields()
        // 获取允许写入的字段通常用 getFieldType 结合判断，或者简单的 getTableFields
        $fields = $this->getModel()->getTableFields();

        if (empty($fields)) {
            return $where; 
        }

        foreach ($where as $key => $item) {
            if (is_int($key)) {
                // 处理 [['id', '=', 1]] 格式
                if (is_array($item) && isset($item[0]) && !in_array($item[0], $fields)) {
                     // 字段不存在，跳过
                     // 注意：这里可能需要 unset($where[$key])，视业务宽容度而定
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
        // ThinkPHP 检测表存在: Db::query 或 schema
        try {
            return Db::execute("SHOW TABLES LIKE '{$table}'") > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }
}