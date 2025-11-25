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

namespace Framework\Basic;

use Framework\Core\App;
use Framework\ORM\Adapter\ORMAdapterFactory;

/**
 * @method count(array $where = [], bool $search = true)
 * @method selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
 * @method selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
 * @method getCount(array $where)
 * @method getDistinctCount(array $where, $field, bool $search = true)
 * @method getPk()
 * @method getTableName()
 * @method get($id, ?array $field = [], ?array $with = [], string $order = '')
 * @method be($map, string $field = '')
 * @method getOne(array $where, ?string $field = '*', array $with = [])
 * @method value($where, ?string $field = '')
 * @method getColumn(array $where, string $field, string $key = '')
 * @method delete(array|int|string $id, ?string $key = null)
 * @method destroy(mixed $id, bool $force = false)
 * @method update(string|int|array $id, array $data, ?string $key = null)
 * @method setWhere($where, ?string $key = null)
 * @method batchUpdate(array $ids, array $data, ?string $key = null)
 * @method save(array $data)
 * @method saveAll(array $data)
 * @method getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = [])
 * @method search(array $where = [], bool $search = true)
 * @method sum(array $where, string $field, bool $search = false)
 * @method bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2)
 * @method bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2)
 * @method getMax(array $where = [], string $field = '')
 * @method getMin(array $where = [], string $field = '')
 * @method decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
 * @method incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales')
 */
abstract class BaseDao
{
    protected mixed $instance;

    public function __construct(?string $mode = null, object|string|null $modelClass = null)
    {
        // 1. 获取 ORM 模式
        if ($mode === null) {
            $mode = config('database.engine' , 'thinkORM') ?? env('ORM_DRIVER');
        }

        // 2. 获取模型类
        $modelClass = $modelClass ?? $this->setModel();

        // 3. 创建适配器
        $this->instance = ORMAdapterFactory::createAdapter($mode, $modelClass);
        // dump($this->instance);
        // dump("created model: " . get_class($this->instance));
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->instance, $name)) {
            return $this->instance->{$name}(...$arguments);
        }
        throw new \BadMethodCallException("Method {$name} does not exist in " . get_class($this->instance));
    }

    /**
     * 获取当前模型类名.
     */
    abstract protected function setModel(): string;
}
