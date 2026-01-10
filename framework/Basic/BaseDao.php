<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: BaseDao.php
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Framework\DI\Injectable;
use Framework\ORM\Adapter\ORMAdapterFactory;
use Framework\Basic\Traits\TenantFilterTrait; // 引入租户Trait
use RuntimeException;
use Throwable;

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
    use Injectable;
	
    use TenantFilterTrait;

    protected mixed $instance = null;
	
    protected ?string $mode = null;
	
    protected string $modelClass = '';

    // --- 核心：通用查询入口，统一添加租户条件 ---
    /**
     * 封装通用查询前置处理（租户过滤 + 其他规则）
     * @param string $method 适配器方法名
     * @param array $arguments 方法参数
     * @return array 处理后的参数
     */
    protected function beforeQuery(string $method, array $arguments): array
    {
        // 1. 提取 where 条件（适配不同方法的参数位置）
        $whereIndex = match ($method) {
            'selectList', 'getCount', 'getOne', 'search', 'sum', 'getMax', 'getMin' => 0,
            'get' => [$this->getPk() => $arguments[0]], // get 方法第一个参数是 ID
            'delete', 'update' => is_array($arguments[0]) ? 0 : [$this->getPk() => $arguments[0]],
            default => -1,
        };

        // 2. 添加租户条件
        if ($whereIndex !== -1) {
            if (is_array($whereIndex)) {
                $arguments[0] = $this->autoAddTenantCondition($whereIndex);
            } else {
                $arguments[$whereIndex] = $this->autoAddTenantCondition($arguments[$whereIndex] ?? []);
            }
        }

        return $arguments;
    }

    // --- 重写核心方法，统一调用前置处理 ---
    public function selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false)
    {
        $args = $this->beforeQuery(__FUNCTION__, func_get_args());
        return $this->instance->selectList(...$args);
    }

    public function getCount(array $where)
    {
        $args = $this->beforeQuery(__FUNCTION__, func_get_args());
        return $this->instance->getCount(...$args);
    }

    public function getOne(array $where, ?string $field = '*', array $with = [])
    {
        $args = $this->beforeQuery(__FUNCTION__, func_get_args());
        return $this->instance->getOne(...$args);
    }

    public function get($id, ?array $field = [], ?array $with = [], string $order = '')
    {
        $args = $this->beforeQuery(__FUNCTION__, func_get_args());
        return $this->instance->getOne($args[0], $field, $with);
    }

    public function delete(array|int|string $id, ?string $key = null)
    {
        $args = $this->beforeQuery(__FUNCTION__, func_get_args());
        return $this->instance->delete(...$args);
    }

    public function update(string|int|array $id, array $data, ?string $key = null)
    {
        $args = $this->beforeQuery(__FUNCTION__, func_get_args());
        return $this->instance->update(...$args);
    }


    // --- 原有构造函数/初始化方法不变 ---
    public function __construct(?string $mode = null, object|string|null $modelClass = null)
    {
        $this->inject();
        $this->mode = $mode ?? config('database.engine', 'thinkORM') ?? env('ORM_DRIVER');
        $modelClass = $modelClass ?? $this->setModel();
        $this->instance = ORMAdapterFactory::createAdapter($this->mode, $modelClass);
        $this->initialize();
    }

    public function getAdapter(): mixed
    {
        return $this->instance;
    }


    /**
     * 动态代理调用 —— 将所有方法转发给 ORM Adapter.
     *
     * @throws RuntimeException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (! $this->instance) {
            throw new RuntimeException(
                sprintf(
                    '[DAO ERROR] %s 未初始化 ORM 适配器',
                    static::class
                )
            );
        }

        // 检查适配器是否支持该方法
        if (! method_exists($this->instance, $name)) {
            throw new RuntimeException(
                sprintf(
                    "[DAO ERROR] 方法不存在: %s::%s()\nAdapter: %s\nModel: %s",
                    static::class,
                    $name,
                    get_class($this->instance),
                    $this->modelClass
                )
            );
        }

        // 对未重写的方法，自动应用租户条件
        $arguments = $this->beforeQuery($name, $arguments);

        try {
            return $this->instance->{$name}(...$arguments);
        } catch (Throwable $e) {
            throw new RuntimeException(
                sprintf(
                    "[DAO ERROR] 调用 %s::%s() 时发生异常\nAdapter: %s\nModel: %s\nMessage: %s",
                    static::class,
                    $name,
                    $this->mode,
                    $this->modelClass,
                    $e->getMessage()
                ),
                0,
                $e
            );
        }
    }

    public function getModel(): mixed
    {
        if (method_exists($this->instance, 'getModel')) {
            return $this->instance->getModel();
        }

        throw new RuntimeException('当前 ORM 适配器不支持 getModel()');
    }

    /**
     * 子类可根据需要覆盖 lifecycle.
     */
    protected function initialize(): void
    {
    }

    /**
     * 获取当前模型类名.
     */
    abstract protected function setModel(): string;
}
