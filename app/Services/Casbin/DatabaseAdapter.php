<?php

declare(strict_types=1);

/**
 * Casbin 适配器
 *
 * @package App\Services\Casbin
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services\Casbin;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Illuminate\Support\Facades\DB;

/**
 * DatabaseAdapter Casbin 数据库适配器
 *
 * 实现 Casbin 的数据库存储适配器
 */
class DatabaseAdapter implements Adapter
{
    use AdapterHelper;

    /**
     * 表名
     * @var string
     */
    protected string $tableName;

    /**
     * 数据库连接
     * @var string|null
     */
    protected ?string $connection;

    /**
     * 构造函数
     *
     * @param string      $tableName  表名
     * @param string|null $connection 数据库连接
     */
    public function __construct(string $tableName = 'casbin_rule', ?string $connection = null)
    {
        $this->tableName = $tableName;
        $this->connection = $connection;
    }

    /**
     * 获取数据库查询构建器
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::connection($this->connection)->table($this->tableName);
    }

    /**
     * 加载所有策略规则
     *
     * @param Model $model 模型
     * @return void
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->getQuery()->get();

        foreach ($rows as $row) {
            $line = $this->rowToLine($row);
            if ($line !== '') {
                $this->loadPolicyLine($line, $model);
            }
        }
    }

    /**
     * 保存所有策略规则
     *
     * @param Model $model 模型
     * @return void
     */
    public function savePolicy(Model $model): void
    {
        $this->getQuery()->truncate();

        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    /**
     * 添加策略规则
     *
     * @param string $sec  区域
     * @param string $ptype 策略类型
     * @param array  $rule  规则
     * @return void
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * 移除策略规则
     *
     * @param string $sec  区域
     * @param string $ptype 策略类型
     * @param array  $rule  规则
     * @return void
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $query = $this->getQuery()->where('ptype', $ptype);

        foreach ($rule as $key => $value) {
            $query->where('v' . $key, $value);
        }

        $query->delete();
    }

    /**
     * 移除过滤后的策略规则
     *
     * @param string $sec   区域
     * @param string $ptype 策略类型
     * @param int    $field 字段索引
     * @param string ...$values 值
     * @return void
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $field, string ...$values): void
    {
        $query = $this->getQuery()->where('ptype', $ptype);

        foreach ($values as $key => $value) {
            $query->where('v' . ($field + $key), $value);
        }

        $query->delete();
    }

    /**
     * 将数据库行转换为策略行
     *
     * @param object $row 数据库行
     * @return string
     */
    protected function rowToLine(object $row): string
    {
        $line = $row->ptype;

        for ($i = 0; $i < 6; $i++) {
            $field = 'v' . $i;
            if (isset($row->$field) && $row->$field !== '') {
                $line .= ', ' . $row->$field;
            }
        }

        return $line;
    }

    /**
     * 保存策略行
     *
     * @param string $ptype 策略类型
     * @param array  $rule  规则
     * @return void
     */
    protected function savePolicyLine(string $ptype, array $rule): void
    {
        $data = ['ptype' => $ptype];

        foreach ($rule as $key => $value) {
            $data['v' . $key] = $value;
        }

        // 填充空值
        for ($i = count($rule); $i < 6; $i++) {
            $data['v' . $i] = '';
        }

        $this->getQuery()->insert($data);
    }
}
