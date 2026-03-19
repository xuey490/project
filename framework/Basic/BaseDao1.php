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
use RuntimeException;
use Throwable;
use Framework\Database\DatabaseFactory;

/**
 * BaseDao - 数据访问对象基类
 *
 * 提供统一的数据访问接口，屏蔽底层 ORM 差异。
 * 支持 Laravel Eloquent ORM 和 ThinkPHP ORM 两种模式。
 *
 * 通过魔术方法代理调用 ORM 适配器，实现以下功能：
 * - CRUD 操作（增删改查）
 * - 聚合查询（count, sum, max, min）
 * - 批量操作（saveAll, batchUpdate）
 * - 库存操作（decStockIncSales, incStockDecSales）
 *
 * 子类需实现 setModel() 方法返回模型类名。
 *
 * @method count(array $where = [], bool $search = true) 统计记录数
 * @method selectList(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false) 查询列表
 * @method selectModel(array $where, string $field = '*', int $page = 0, int $limit = 0, string $order = '', array $with = [], bool $search = false) 查询模型集合
 * @method getCount(array $where) 获取计数
 * @method getDistinctCount(array $where, $field, bool $search = true) 获取去重计数
 * @method getPk() 获取主键名
 * @method getTableName() 获取表名
 * @method get($id, ?array $field = [], ?array $with = [], string $order = '') 根据ID获取记录
 * @method be($map, string $field = '') 检查记录是否存在
 * @method getOne(array $where, ?string $field = '*', array $with = []) 获取单条记录
 * @method value($where, ?string $field = '') 获取单个字段值
 * @method getColumn(array $where, string $field, string $key = '') 获取字段列
 * @method delete(array|int|string $id, ?string $key = null) 删除记录
 * @method destroy(mixed $id, bool $force = false) 销毁记录
 * @method update(string|int|array $id, array $data, ?string $key = null) 更新记录
 * @method setWhere($where, ?string $key = null) 设置查询条件
 * @method batchUpdate(array $ids, array $data, ?string $key = null) 批量更新
 * @method save(array $data) 保存记录
 * @method saveAll(array $data) 批量保存
 * @method getFieldValue($value, string $filed, ?string $valueKey = '', ?array $where = []) 获取字段值
 * @method search(array $where = [], bool $search = true) 搜索
 * @method sum(array $where, string $field, bool $search = false) 求和
 * @method bcInc($key, string $incField, string $inc, string $keyField = null, int $acc = 2) 高精度增加
 * @method bcDec($key, string $decField, string $dec, string $keyField = null, int $acc = 2) 高精度减少
 * @method getMax(array $where = [], string $field = '') 获取最大值
 * @method getMin(array $where = [], string $field = '') 获取最小值
 * @method decStockIncSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 减库存增销量
 * @method incStockDecSales(array $where, int $num, string $stock = 'stock', string $sales = 'sales') 增库存减销量
 *
 * @package Framework\Basic
 */
abstract class BaseDao
{
    use Injectable;

    /**
     * ORM 适配器实例
     * 如 LaravelORMFactory 或 ThinkphpORMFactory
     * @var mixed
     */
    protected mixed $instance = null;

    /**
     * ORM 模式
     * @var string|null
     */
    protected ?string $mode = null;

    /**
     * 模型类名
     * @var string
     */
    protected string $modelClass = '';

    /**
     * 构造函数
     *
     * 初始化 ORM 适配器。
     *
     * @param string|null $mode ORM 模式，null 则从配置读取
     * @param object|string|null $modelClass 模型类，null 则调用 setModel() 获取
     */
    public function __construct(?string $mode = null, object|string|null $modelClass = null)
    {
        $this->inject();

        // 1. 获取 ORM 模式
        if ($mode == null) {
            $mode = config('database.engine', 'thinkORM') ?? env('ORM_DRIVER');
        }

        $db = app('db');

        $this->mode = $mode;

        // 2. 获取模型类
        $modelClass = $modelClass ?? $this->setModel();

        // 3. 创建适配器
        $this->instance = ORMAdapterFactory::createAdapter($mode, $modelClass);

        $this->initialize();
    }

    /**
     * 获取底层 ORM 适配器实例
     *
     * @return mixed ORM 适配器实例
     */
    public function getAdapter(): mixed
    {
        return $this->instance;
    }

    /**
     * 魔术方法：动态代理调用
     *
     * 将所有方法调用转发给 ORM 适配器处理。
     *
     * @param string $name 方法名
     * @param array $arguments 方法参数
     * @return mixed 方法返回值
     * @throws RuntimeException 适配器未初始化或方法不存在时抛出
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!$this->instance) {
            throw new RuntimeException(
                sprintf(
                    '[DAO ERROR] %s 未初始化 ORM 适配器',
                    static::class
                )
            );
        }

        // 检查适配器是否支持该方法
        if (!method_exists($this->instance, $name)) {
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

    /**
     * 获取模型实例
     *
     * @return mixed 模型实例
     * @throws RuntimeException 适配器不支持 getModel 时抛出
     */
    public function getModel(): mixed
    {
        if (method_exists($this->instance, 'getModel')) {
            return $this->instance->getModel();
        }

        throw new RuntimeException('当前 ORM 适配器不支持 getModel()');
    }

    /**
     * 子类初始化钩子
     *
     * 子类可根据需要覆盖此方法进行初始化操作。
     *
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * 设置模型类名
     *
     * 子类必须实现此方法返回对应的模型类名。
     *
     * @return string 模型类名
     */
    abstract protected function setModel(): string;
}
