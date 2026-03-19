<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: DatabaseAdapter.php
 * @Date: 2026-2-7
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Casbin\Adapter;

use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Casbin\Persist\UpdatableAdapter;
use Casbin\Persist\BatchAdapter;
use Casbin\Persist\FilteredAdapter;
use Casbin\Persist\Adapters\Filter;
use Casbin\Exceptions\InvalidFilterTypeException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use Framework\Casbin\Model\RuleModel;

/**
 * DatabaseAdapter - ThinkPHP 框架 Casbin 数据库适配器
 *
 * 该类实现了 Casbin 的多个持久化接口，提供完整的策略存储功能：
 * - Adapter: 基础策略加载和保存
 * - UpdatableAdapter: 策略更新功能
 * - BatchAdapter: 批量策略操作
 * - FilteredAdapter: 过滤式策略加载
 *
 * @package Framework\Casbin\Adapter
 * @author  techlee@qq.com
 */
class DatabaseAdapter implements Adapter, UpdatableAdapter, BatchAdapter, FilteredAdapter
{
    use AdapterHelper;

    /**
     * 标记当前策略是否经过过滤
     *
     * @var bool
     */
    private bool $filtered = false;

    /**
     * Casbin 规则模型实例
     * 用于操作数据库中的策略规则
     *
     * @var RuleModel
     */
    protected RuleModel $model;

    /**
     * DatabaseAdapter 构造函数
     *
     * 初始化适配器，创建规则模型实例
     *
     * @param string|null $driver 数据库驱动名称，为空则使用默认驱动
     */
    public function __construct(?string $driver = null)
    {
        $this->model = new RuleModel([], $driver);
    }

    /**
     * 过滤规则数组中的空值和尾部无效数据
     *
     * 从数组末尾向前遍历，移除连续的空值和 null 值，
     * 保留有效的策略元素
     *
     * @param array $rule 原始规则数组
     * @return array 过滤后的规则数组
     */
    public function filterRule(array $rule): array
    {
        $rule = array_values($rule);

        $i = count($rule) - 1;
        for (; $i >= 0; $i--) {
            if ($rule[$i] != '' && !is_null($rule[$i])) {
                break;
            }
        }

        return array_slice($rule, 0, $i + 1);
    }

    /**
     * 保存单条策略规则到数据库
     *
     * 将策略类型和规则值组合成数据库记录格式并插入
     *
     * @param string $ptype 策略类型（如 'p' 表示权限策略，'g' 表示角色继承）
     * @param array  $rule  策略规则值数组
     * @return void
     */
    public function savePolicyLine(string $ptype, array $rule): void
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . strval($key) . ''] = $value;
        }
        $this->model->insert($col);
    }

    /**
     * 从数据库加载所有策略规则到模型
     *
     * 查询数据库中的所有策略记录，并将其加载到 Casbin 模型中
     *
     * @param Model $model Casbin 模型实例
     * @throws DataNotFoundException 数据未找到异常
     * @throws DbException 数据库异常
     * @throws ModelNotFoundException 模型未找到异常
     * @return void
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->model->field(['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'])->select()->toArray();
        foreach ($rows as $row) {
            $this->loadPolicyArray($this->filterRule($row), $model);
        }
    }

    /**
     * 将模型中的所有策略规则保存到数据库
     *
     * 遍历模型中的权限策略（p）和角色继承策略（g），
     * 将其全部保存到数据库
     *
     * @param Model $model Casbin 模型实例
     * @return void
     */
    public function savePolicy(Model $model): void
    {
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
     * 添加单条策略规则到数据库（自动保存功能）
     *
     * 该方法是自动保存功能的一部分，在策略变更时自动调用
     *
     * @param string $sec   策略段（'p' 或 'g'）
     * @param string $ptype 策略类型
     * @param array  $rule  策略规则值数组
     * @return void
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * 批量添加策略规则到数据库（自动保存功能）
     *
     * 将多条策略规则一次性插入数据库，提高批量操作效率
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param string[][] $rules 策略规则二维数组
     * @return void
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        $cols = [];
        $i = 0;

        foreach ($rules as $rule) {
            $temp['ptype'] = $ptype;
            foreach ($rule as $key => $value) {
                $temp['v' . strval($key)] = $value;
            }
            $cols[$i++] = $temp;
            $temp = [];
        }
        $this->model->insertAll($cols);
    }

    /**
     * 从数据库移除单条策略规则（自动保存功能）
     *
     * 根据策略类型和规则值精确匹配并删除对应记录
     *
     * @param string $sec   策略段（'p' 或 'g'）
     * @param string $ptype 策略类型
     * @param array  $rule  策略规则值数组
     * @throws DataNotFoundException 数据未找到异常
     * @throws DbException 数据库异常
     * @throws ModelNotFoundException 模型未找到异常
     * @return void
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $count = 0;

        $instance = $this->model->where('ptype', $ptype);

        foreach ($rule as $key => $value) {
            $instance->where('v' . strval($key), $value);
        }

        foreach ($instance->select() as $model) {
            if ($model->delete()) {
                ++$count;
            }
        }
    }

    /**
     * 批量移除策略规则（自动保存功能）
     *
     * 使用事务保证批量删除操作的原子性
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param string[][] $rules 策略规则二维数组
     * @return void
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        Db::transaction(function () use ($sec, $ptype, $rules) {
            foreach ($rules as $rule) {
                $this->removePolicy($sec, $ptype, $rule);
            }
        });
    }

    /**
     * 内部方法：移除匹配过滤条件的策略规则并返回被移除的规则
     *
     * 根据字段索引和字段值过滤策略规则，删除匹配的记录并返回被删除的规则数组
     *
     * @param string      $sec         策略段（'p' 或 'g'）
     * @param string      $ptype       策略类型
     * @param int         $fieldIndex  字段起始索引（0-5）
     * @param string|null ...$fieldValues 字段值列表
     * @return array 被移除的规则数组
     * @throws DbException 数据库异常
     * @throws ModelNotFoundException 模型未找到异常
     * @throws DataNotFoundException 数据未找到异常
     */
    public function _removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, ?string ...$fieldValues): array
    {
        $count = 0;
        $removedRules = [];

        $instance = $this->model->where('ptype', $ptype);
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues)) {
                if ('' != $fieldValues[$value - $fieldIndex]) {
                    $instance->where('v' . strval($value), $fieldValues[$value - $fieldIndex]);
                }
            }
        }

        foreach ($instance->select() as $model) {
            $item = $model->hidden(['id', 'ptype'])->toArray();
            $item = $this->filterRule($item);
            $removedRules[] = $item;
            if ($model->delete()) {
                ++$count;
            }
        }

        return $removedRules;
    }

    /**
     * 移除匹配过滤条件的策略规则（自动保存功能）
     *
     * 根据字段索引和字段值过滤并删除策略规则
     *
     * @param string $sec         策略段（'p' 或 'g'）
     * @param string $ptype       策略类型
     * @param int    $fieldIndex  字段起始索引（0-5）
     * @param string ...$fieldValues 字段值列表
     * @throws DataNotFoundException 数据未找到异常
     * @throws DbException 数据库异常
     * @throws ModelNotFoundException 模型未找到异常
     * @return void
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $this->_removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);
    }

    /**
     * 更新单条策略规则（自动保存功能）
     *
     * 找到旧规则并将其更新为新规则
     *
     * @param string   $sec       策略段（'p' 或 'g'）
     * @param string   $ptype     策略类型
     * @param string[] $oldRule   旧策略规则数组
     * @param string[] $newPolicy 新策略规则数组
     * @throws DataNotFoundException 数据未找到异常
     * @throws DbException 数据库异常
     * @throws ModelNotFoundException 模型未找到异常
     * @return void
     */
    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $instance = $this->model->where('ptype', $ptype);
        foreach ($oldRule as $key => $value) {
            $instance->where('v' . strval($key), $value);
        }
        $instance = $instance->find();

        foreach ($newPolicy as $key => $value) {
            $column = 'v' . strval($key);
            $instance->$column = $value;
        }

        $instance->save();
    }

    /**
     * 批量更新策略规则
     *
     * 使用事务保证批量更新操作的原子性
     *
     * @param string     $sec      策略段（'p' 或 'g'）
     * @param string     $ptype    策略类型
     * @param string[][] $oldRules 旧策略规则二维数组
     * @param string[][] $newRules 新策略规则二维数组
     * @return void
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        Db::transaction(function () use ($sec, $ptype, $oldRules, $newRules) {
            foreach ($oldRules as $i => $oldRule) {
                $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
            }
        });
    }

    /**
     * 更新匹配过滤条件的策略规则
     *
     * 删除匹配过滤条件的旧规则，并添加新的策略规则
     *
     * @param string   $sec          策略段（'p' 或 'g'）
     * @param string   $ptype        策略类型
     * @param array    $newPolicies  新策略规则数组
     * @param int      $fieldIndex   字段起始索引
     * @param string   ...$fieldValues 字段值列表
     * @return array 被删除的旧规则数组
     */
    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {

        $oldRules = [];
        DB::transaction(function () use ($sec, $ptype, $fieldIndex, $fieldValues, $newPolicies, &$oldRules) {
            $oldRules = $this->_removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);
            $this->addPolicies($sec, $ptype, $newPolicies);
        });

        return $oldRules;
    }

    /**
     * 判断当前策略是否已过滤
     *
     * @return bool 如果策略已过滤返回 true，否则返回 false
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * 设置策略过滤状态
     *
     * @param bool $filtered 是否已过滤
     * @return void
     */
    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    /**
     * 加载匹配过滤条件的策略规则
     *
     * 根据过滤条件从数据库加载策略规则，支持多种过滤方式：
     * - 字符串：原生 SQL WHERE 条件
     * - Filter 对象：Casbin 标准过滤器
     * - Closure 闭包：自定义过滤逻辑
     *
     * @param Model $model  Casbin 模型实例
     * @param mixed $filter 过滤条件（字符串/Filter对象/闭包）
     * @throws InvalidFilterTypeException 无效的过滤类型异常
     * @throws DataNotFoundException 数据未找到异常
     * @throws DbException 数据库异常
     * @throws ModelNotFoundException 模型未找到异常
     * @return void
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $instance = $this->model;

        if (is_string($filter)) {
            $instance = $instance->whereRaw($filter);
        } elseif ($filter instanceof Filter) {
            foreach ($filter->p as $k => $v) {
                $instance = $instance->where($v, $filter->g[$k]);
            }
        } elseif ($filter instanceof \Closure) {
            $instance = $instance->where($filter);
        } else {
            throw new InvalidFilterTypeException('invalid filter type');
        }
        $rows = $instance->select()->hidden(['id'])->toArray();
        foreach ($rows as $row) {
            $row = array_filter($row, function ($value) {
                return !is_null($value) && $value !== '';
            });
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && !is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }
        $this->setFiltered(true);
    }
}
