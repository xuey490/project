<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-12-6
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Repository;

/**
 * Interface RepositoryInterface
 * 定义通用的数据仓库操作标准
 */
interface RepositoryInterface
{
    /**
     * 根据主键查找
     * @param array $relations 预加载关联模型，如 ['profile', 'orders']
     */
    public function findById(int|string $id, array $relations = []): mixed;

    /**
     * 根据条件查找单条
     */
    public function findOneBy(array $criteria, array $relations = []): mixed;

    /**
     * 根据条件查找多条记录
     * @param array $criteria 查询条件 ['status' => 1]
     * @param array $orderBy  排序 ['id' => 'desc']
     * @param int|null $limit 限制条数
     */
    public function findAll(array $criteria = [], array $orderBy = [], ?int $limit = null, array $relations = []): mixed;

    /**
     * 分页查询
     */
    public function paginate(array $criteria = [], int $perPage = 15, array $orderBy = [], array $relations = []): mixed;


    /**
     * 创建数据
     */
    public function create(array $data): mixed;

    /**
     * 更新数据
     * @param int|string $id 主键
     * @param array $data 更新内容
     */
    public function update(int|string $id, array $data): bool;

    /**
     * 删除数据
     */
    public function delete(int|string $id): bool;
    
    /**
     * 聚合统计 (count, sum, max)
     * @param string $type 统计类型
     * @param string $field 字段名
     */
    public function aggregate(string $type, array $criteria = [], string $field = '*'): string|int|float;

    /**
     * 数据库事务闭包
     */
    public function transaction(\Closure $callback): mixed;
	
    /**
     * 执行原生 SQL 查询 (SELECT)
     * @return array 返回数组结果集
     */
    public function query(string $sql, array $bindings = []): array;

    /**
     * 执行原生 SQL 指令 (INSERT, UPDATE, DELETE)
     * @return int 返回受影响的行数
     */
    public function execute(string $sql, array $bindings = []): int;
}