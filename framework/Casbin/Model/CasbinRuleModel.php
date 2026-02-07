<?php
/**
 * @desc 通用 Casbin 规则模型（原生 PDO 实现，无框架依赖）
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/01/12 10:37
 */
declare(strict_types=1);

namespace Framework\Casbin\Model;

use PDO;
use PDOException;
use RuntimeException;

class CasbinRuleModel
{
    /**
     * 禁用时间戳
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值字段
     * @var array
     */
    protected $fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];

    /**
     * 数据库连接
     * @var PDO|null
     */
    protected ?PDO $pdo = null;

    /**
     * 数据表名
     * @var string
     */
    protected string $table = 'casbin_rule';

    /**
     * 数据库驱动配置
     * @var array
     */
    protected array $dbConfig;

    /**
     * 构造函数
     * @param array $dbConfig 数据库配置
     * [
     *     'host' => '127.0.0.1',
     *     'port' => 3306,
     *     'database' => 'your_db',
     *     'username' => 'root',
     *     'password' => 'your_pwd',
     *     'charset' => 'utf8mb4',
     *     'table' => 'casbin_rule'
     * ]
     */
    public function __construct(array $dbConfig)
    {
        $this->dbConfig = $dbConfig;
        $this->table = $dbConfig['table'] ?? 'casbin_rule';
        $this->connect();
    }

    /**
     * 连接数据库
     * @throws RuntimeException
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
     * 查询所有规则
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT ptype, v0, v1, v2, v3, v4, v5 FROM {$this->table}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * 新增/更新规则（不存在则新增，存在则忽略）
     * @param array $data
     * @return bool
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
     * @param array $where
     * @return bool
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
     * @param array $rules
     * @return bool
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
     * @param array $oldRule
     * @param array $newRule
     * @return bool
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
     * 关闭连接
     */
    public function close(): void
    {
        $this->pdo = null;
    }
}