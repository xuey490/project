<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: CasbinRuleModel.php
 * @Date: 2026-2-7
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Casbin\Model;

use PDO;
use PDOException;
use RuntimeException;

/**
 * CasbinRuleModel - 基于 PDO 的 Casbin 规则模型
 *
 * 该类使用原生 PDO 实现 Casbin 策略规则的数据库操作，
 * 不依赖任何框架，适用于需要独立数据库连接的场景。
 * 提供策略规则的增删改查等基础操作。
 *
 * @package Framework\Casbin\Model
 */
class CasbinRuleModel
{
    /**
     * 是否启用时间戳自动维护
     * Casbin 规则表通常不需要时间戳字段
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的字段列表
     * 包含策略类型和 6 个规则值字段
     *
     * @var array
     */
    protected $fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];

    /**
     * PDO 数据库连接实例
     *
     * @var PDO|null
     */
    protected ?PDO $pdo = null;

    /**
     * 策略规则表名
     *
     * @var string
     */
    protected string $table = 'casbin_rule';

    /**
     * 数据库驱动配置数组
     *
     * @var array
     */
    protected array $dbConfig;

    /**
     * 构造函数
     *
     * 初始化数据库配置并建立连接
     *
     * @param array $dbConfig 数据库配置数组
     *                        - host: 数据库主机地址
     *                        - port: 数据库端口
     *                        - database: 数据库名称
     *                        - username: 数据库用户名
     *                        - password: 数据库密码
     *                        - charset: 字符集（默认 utf8mb4）
     *                        - table: 数据表名称（可选，默认 casbin_rule）
     */
    public function __construct(array $dbConfig)
    {
        $this->dbConfig = $dbConfig;
        $this->table = $dbConfig['table'] ?? 'casbin_rule';
        $this->connect();
    }

    /**
     * 建立数据库连接
     *
     * 使用 PDO 连接 MySQL 数据库，配置异常错误模式和关联数组 fetch 模式
     *
     * @throws RuntimeException 数据库连接失败时抛出异常
     * @return void
     */
    protected function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->dbConfig['host']};port={$this->dbConfig['port']};dbname={$this->dbConfig['database']};charset={$this->dbConfig['charset']}";
            $this->pdo = new PDO(
                $dsn,
                $this->dbConfig['username'],
                $this->dbConfig['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException("数据库连接失败: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 查询所有策略规则
     *
     * 从数据库中获取所有策略规则记录
     *
     * @return array 策略规则数组，每条记录包含 ptype 和 v0-v5 字段
     */
    public function getAll(): array
    {
        $sql = "SELECT ptype, v0, v1, v2, v3, v4, v5 FROM {$this->table}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 新增或更新规则
     *
     * 检查规则是否存在，如果存在则忽略，不存在则新增
     * 使用数据库查询实现"存在则跳过"的逻辑
     *
     * @param array $data 规则数据数组，包含 ptype 和规则值字段
     * @return bool 操作成功返回 true
     */
    public function updateOrCreate(array $data): bool
    {
        // 过滤空值
        $data = array_filter($data, function ($val) {
            return $val !== '' && $val !== null;
        });

        // 构建查询条件
        $where = [];
        $params = [];
        foreach ($data as $key => $val) {
            $where[] = "{$key} = :{$key}";
            $params[":{$key}"] = $val;
        }

        // 检查是否存在
        $checkSql = "SELECT 1 FROM {$this->table} WHERE " . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($checkSql);
        $stmt->execute($params);
        if ($stmt->fetch()) {
            return true;
        }

        // 新增规则
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_keys($params));
        $insertSql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->pdo->prepare($insertSql);
        return $stmt->execute($params);
    }

    /**
     * 删除规则
     *
     * 根据条件删除匹配的策略规则
     *
     * @param array $where 删除条件数组，键为字段名，值为字段值
     * @return bool 操作成功返回 true
     */
    public function delete(array $where): bool
    {
        $whereClauses = [];
        $params = [];
        foreach ($where as $key => $val) {
            $whereClauses[] = "{$key} = :{$key}";
            $params[":{$key}"] = $val;
        }

        $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $whereClauses);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * 批量添加规则
     *
     * 使用数据库事务批量添加多条策略规则，
     * 如果任何一条失败则回滚所有操作
     *
     * @param array $rules 规则二维数组
     * @return bool 操作成功返回 true
     * @throws RuntimeException 批量添加失败时抛出异常
     */
    public function batchAdd(array $rules): bool
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($rules as $rule) {
                $this->updateOrCreate($rule);
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("批量添加规则失败: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * 更新规则
     *
     * 根据旧规则条件更新为新规则值
     *
     * @param array $oldRule 旧规则条件数组
     * @param array $newRule 新规则值数组
     * @return bool 操作成功返回 true
     */
    public function update(array $oldRule, array $newRule): bool
    {
        // 构建更新语句
        $setClauses = [];
        $params = [];
        foreach ($newRule as $key => $val) {
            $setClauses[] = "{$key} = :new_{$key}";
            $params[":new_{$key}"] = $val;
        }

        // 构建条件
        $whereClauses = [];
        foreach ($oldRule as $key => $val) {
            $whereClauses[] = "{$key} = :old_{$key}";
            $params[":old_{$key}"] = $val;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE " . implode(' AND ', $whereClauses);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * 关闭数据库连接
     *
     * 释放 PDO 连接资源
     *
     * @return void
     */
    public function close(): void
    {
        $this->pdo = null;
    }
}
