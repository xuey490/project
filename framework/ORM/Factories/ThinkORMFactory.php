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
use think\Collection;
use think\db\Query;
use think\helper\Str;
use think\Model;

class ThinkORMFactory
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
     * 获取模型实例 (懒加载).
     */
    public function getModel(): Model
    {
        // 如果已经有实例，直接返回（注意：ThinkPHP模型是复用的，如果需要全新查询对象，
        // 外部调用链通常会触发 where() 返回新的 Query 对象，所以这里返回单例是安全的，
        // 除非模型内部有状态污染。稳妥起见，若需要隔离，可 return clone $this->modelInstance）
        if ($this->modelInstance) {
            return $this->modelInstance;
        }

        try {
            $class = $this->modelClass;
            if (! class_exists($class)) {
                throw new Exception($class . ' 不是一个有效的模型类');
            }
            // 使用 App::make 解析模型，支持模型自身的依赖注入
            $this->modelInstance = App::make($class);
            return $this->modelInstance;
        } catch (\Throwable $e) {
            throw new Exception('模型加载失败: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 实现 ORMAdapterInterface 及通用业务逻辑
    // =========================================================================

    public function count(array $where = [], bool $search = true): int
    {
        return $this->search($where, $search)->count();
    }

    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false): ?Collection
    {
        return $this->selectModel($where, $field, $page, $limit, $order, $with, $search)->select();
    }

    public function selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false): Model|Query
    {
        if ($search) {
            $query = $this->search($where);
        } else {
            $query = $this->getModel()->where($where);
        }

        return $query->field($field)
            ->when($page && $limit, function ($q) use ($page, $limit) {
                $q->page($page, $limit);
            })
            ->when($order !== '', function ($q) use ($order) {
                $q->order($order);
            })
            ->when(! empty($with), function ($q) use ($with) {
                $q->with($with);
            });
    }

    public function getCount(array $where): int
    {
        return $this->getModel()->where($where)->count();
    }

    public function getDistinctCount(array $where, $field, bool $search = true): mixed
    {
        $query  = $search ? $this->search($where) : $this->getModel()->where($where);
        $result = $query->field('COUNT(distinct(' . $field . ')) as count')->find();
        return $result['count'] ?? 0;
    }

    public function getTableName(): string
    {
        return $this->getModel()->getName();
    }

    public function get($id, ?array $field = [], ?array $with = [], string $order = ''): mixed
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [$this->getPk() => $id];
        }

        return $this->getModel()->where($where)
            ->when(! empty($with), function ($q) use ($with) {
                $q->with($with);
            })
            ->when($order !== '', function ($q) use ($order) {
                $q->order($order);
            })
            ->field($field ?? ['*'])
            ->find();
    }

    public function be($map, string $field = ''): bool
    {
        if (! is_array($map) && empty($field)) {
            $field = $this->getPk();
        }
        $map = ! is_array($map) ? [$field => $map] : $map;
        return 0 < $this->getModel()->where($map)->count();
    }

    public function getOne(array $where, ?string $field = '*', array $with = []): array|Model|null
    {
        // 注意：get 方法接收的 field 是 array，这里如果是 string 需要分割
        // 但下面的 get() 实现里 field 默认是 array，这里为了兼容原逻辑做个转换
        $fieldArr = is_string($field) ? explode(',', $field) : $field;
        return $this->get($where, $fieldArr, $with);
    }

    public function value($where, ?string $field = ''): mixed
    {
        $pk = $this->getPk();
        return $this->search($this->setWhere($where))->value($field ?: $pk);
    }

    public function getColumn(array $where, string $field, string $key = ''): array
    {
        return $this->getModel()->where($where)->column($field, $key);
    }

    public function delete(array|int|string $id, ?string $key = null): mixed
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }

        return $this->getModel()->where($where)->delete();
    }

    public function destroy(mixed $id, bool $force = false): bool
    {
        return $this->getModel()->destroy($id, $force);
    }

    public function update(array|int|string $id, array $data, ?string $key = null): mixed
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
        }
        // 修正：静态调用 update 需要模型类名，这里改用实例调用 save 或 update
        return $this->getModel()->update($data, $where);
    }

    public function batchUpdate(array $ids, array $data, ?string $key = null): mixed
    {
        return $this->getModel()
            ->whereIn(is_null($key) ? $this->getPk() : $key, $ids)
            ->update($data);
    }

    public function save(array $data): mixed
    {
        return $this->getModel()->create($data);
    }

    public function saveAll(array $data): Collection
    {
        return $this->getModel()->saveAll($data);
    }

    public function getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = []): mixed
    {
        // 假设模型里有这个自定义方法，否则这里会报错
        // 如果是通用方法，应改为:
        // return $this->getModel()->where($where)->where($filed, $value)->value($valueKey ?: $this->getPk());
        return $this->getModel()->getFieldValue($value, $filed, $valueKey, $where);
    }

    public function search(array $where = [], bool $search = true): Model|Query
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
            return $this->search($where)->sum($field);
        }
        return $this->getModel()->where($where)->sum($field);
    }

    public function bcInc($key, string $incField, string $inc, ?string $keyField = null, int $acc = 2): bool
    {
        return $this->bc($key, $incField, $inc, $keyField, 1, $acc);
    }

    public function bcDec($key, string $decField, string $dec, ?string $keyField = null, int $acc = 2): bool
    {
        return $this->bc($key, $decField, $dec, $keyField, 2, $acc);
    }

    public function bc($key, string $incField, string $inc, ?string $keyField = null, int $type = 1, int $acc = 2): bool
    {
        if ($keyField === null) {
            // 使用本类的 get 方法
            $result = $this->get($key);
        } else {
            $result = $this->getOne([$keyField => $key]);
        }

        if (! $result) {
            return false;
        }

        // $result 是 Model 对象
        $currentVal = $result->{$incField} ?? 0;
        $new        = 0;

        if ($type === 1) {
            $new = bcadd((string) $currentVal, (string) $inc, $acc);
        } elseif ($type === 2) {
            if ($currentVal < $inc) {
                return false;
            }
            $new = bcsub((string) $currentVal, (string) $inc, $acc);
        }

        $result->{$incField} = $new;
        return $result->save() !== false;
    }

    public function decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): bool
    {
        $isQuota = false;
        if (isset($where['type']) && $where['type']) {
            $isQuota = true;
            if (count($where) == 2) {
                unset($where['type']);
            }
        }

        $field   = $isQuota ? "{$stock},quota" : $stock;
        $product = $this->getModel()->where($where)->field($field)->find();

        if ($product) {
            // 注意：update() 返回的是受影响行数(int) 或 Model对象，这里根据原逻辑直接返回更新结果
            // ThinkPHP链式调用 dec/inc 后调用 update() 会执行 SQL
            return (bool) $this->getModel()->where($where)
                ->when($isQuota, function ($query) use ($num) {
                    $query->dec('quota', $num);
                })
                ->dec($stock, $num)
                ->inc($sales, $num)
                ->update();
        }
        return false;
    }

    public function incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales'): mixed
    {
        $isQuota = false;
        if (isset($where['type']) && $where['type']) {
            $isQuota = true;
            if (count($where) == 2) {
                unset($where['type']);
            }
        }

        $salesOne = $this->getModel()->where($where)->value($sales);
        if ($salesOne !== null) { // value 可能返回 null
            $salesNum = $num;
            if ($num > $salesOne) {
                $salesNum = $salesOne;
            }
            return $this->getModel()->where($where)
                ->when($isQuota, function ($query) use ($num) {
                    $query->inc('quota', $num);
                })
                ->inc($stock, $num)
                ->dec($sales, $salesNum)
                ->update();
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

    protected function getPk()
    {
        return $this->getModel()->getPk();
    }

    protected function setWhere($where, ?string $key = null): mixed
    {
        if (! is_array($where)) {
            $where = [is_null($key) ? $this->getPk() : $key => $where];
        }
        return $where;
    }

    protected function withSearchSelect($where, $search): Model|Query
    {
        [$with, $otherWhere] = $this->getSearchData($where);

        $query = $this->getModel()->withSearch($with, $where);

        if ($search) {
            $query->where($this->filterWhere($otherWhere));
        }
        return $query;
    }

    protected function filterWhere(array $where = []): array
    {
        $fields = $this->getModel()->getTableFields();
        foreach ($where as $key => $item) {
            if (isset($item[0]) && ! in_array($item[0], $fields)) {
                unset($where[$key]);
            }
        }
        return $where;
    }

    // =========================================================================
    // 搜索器逻辑
    // =========================================================================

    private function getSearchData(array $where): array
    {
        $with       = [];
        $otherWhere = [];

        // 获取当前持有的模型类名
        // 如果 modelInstance 已存在，直接取其类名；否则取 modelClass
        $className = $this->modelInstance ? get_class($this->modelInstance) : $this->modelClass;

        // 防止 $className 为空或无效（尽管 getModel 已校验，但这里可能直接被 search 调用）
        if (! $className || ! class_exists($className)) {
            // 尝试初始化一下
            $this->getModel();
            $className = get_class($this->modelInstance);
        }

        $responses  = new \ReflectionClass($className);
        foreach ($where as $key => $value) {
            $method = 'search' . Str::studly($key) . 'Attr';
            if ($responses->hasMethod($method)) {
                $with[] = $key;
            } else {
                if (! in_array($key, ['timeKey', 'store_stock', 'integral_time'])) {
                    if (! is_array($value)) {
                        $otherWhere[] = [$key, '=', $value];
                    } elseif (count($value) === 3) {
                        $otherWhere[] = $value;
                    }
                }
            }
        }
        return [$with, $otherWhere];
    }
}
