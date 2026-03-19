<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: LaravelDatabaseAdapter.php
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
use Framework\Casbin\Model\LaravelRuleModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Closure;
use Throwable;

/**
 * LaravelDatabaseAdapter - Laravel 框架 Casbin 数据库适配器
 *
 * 该类专为 Laravel 框架设计，移除了其他框架依赖，完善了 Laravel 生态适配。
 * 实现了 Casbin 的多个持久化接口：
 * - Adapter: 基础策略加载和保存
 * - UpdatableAdapter: 策略更新功能
 * - BatchAdapter: 批量策略操作
 * - FilteredAdapter: 过滤式策略加载
 *
 * @package Framework\Casbin\Adapter
 */
class LaravelDatabaseAdapter implements Adapter, UpdatableAdapter, BatchAdapter, FilteredAdapter
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
     * 基于 Laravel Eloquent ORM 实现
     *
     * @var LaravelRuleModel
     */
    protected LaravelRuleModel $model;

    /**
     * LaravelDatabaseAdapter 构造函数
     *
     * 初始化适配器，创建 Laravel 规则模型实例
     *
     * @param string|null $driver 数据库驱动名称，为空则使用默认驱动
     */
    public function __construct(?string $driver = null)
    {
        $this->model = new LaravelRuleModel([], $driver);
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
            if ($rule[$i] !== '' && !is_null($rule[$i])) {
                break;
            }
        }

        return array_slice($rule, 0, $i + 1);
    }

    /**
     * 保存单条策略规则到数据库
     *
     * 使用 Laravel Eloquent 的 updateOrCreate 方法，
     * 避免重复插入相同的策略规则
     *
     * @param string $ptype 策略类型（如 'p' 表示权限策略，'g' 表示角色继承）
     * @param array  $rule  策略规则值数组
     * @return void
     */
    public function savePolicyLine(string $ptype, array $rule): void
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . $key] = $value;
        }
        $this->model->updateOrCreate($col, $col);
    }

    /**
     * 从数据库加载所有策略规则到模型
     *
     * 只查询必要的字段，优化查询效率
     *
     * @param Model $model Casbin 模型实例
     * @return void
     */
    public function loadPolicy(Model $model): void
    {
        // 只查询需要的字段，提升查询效率
        $rows = $this->model->select(['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'])->get()->toArray();
        
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
     * 使用 updateOrCreate 逐条添加，避免重复插入
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param string[][] $rules 策略规则二维数组
     * @return void
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        foreach ($rules as $rule) {
            $temp = ['ptype' => $ptype];
            foreach ($rule as $key => $value) {
                $temp['v' . $key] = $value;
            }
            $this->model->updateOrCreate($temp, $temp);
        }
    }

    /**
     * 从数据库移除单条策略规则（自动保存功能）
     *
     * 根据策略类型和规则值精确匹配并删除对应记录，
     * 使用 Laravel Eloquent 的批量删除优化性能
     *
     * @param string $sec   策略段（'p' 或 'g'）
     * @param string $ptype 策略类型
     * @param array  $rule  策略规则值数组
     * @return void
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $instance = $this->model->where('ptype', $ptype);
        foreach ($rule as $key => $value) {
            $instance->where('v' . $key, $value);
        }
        
        // 优化：直接批量删除，无需遍历
        $instance->delete();
    }

    /**
     * 内部方法：获取并返回要删除的过滤策略规则
     *
     * 根据字段索引和字段值过滤策略规则，返回匹配的规则数组
     * 不执行实际删除操作，仅用于获取被删除的规则
     *
     * @param string      $sec         策略段（'p' 或 'g'）
     * @param string      $ptype       策略类型
     * @param int         $fieldIndex  字段起始索引（0-5）
     * @param string|null ...$fieldValues 字段值列表
     * @return array 被移除的规则数组
     * @throws Throwable
     */
    public function _removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, ?string ...$fieldValues): array
    {
        $removedRules = [];
        $data         = $this->getCollection($ptype, $fieldIndex, $fieldValues);

        foreach ($data as $model) {
            // 隐藏无关字段，只保留规则字段
            $item = $model->makeHidden(['id', 'ptype'])->toArray();
            $item = $this->filterRule($item);
            $removedRules[] = $item;
        }

        return $removedRules;
    }

    /**
     * 批量移除策略规则（自动保存功能）
     *
     * 使用 Laravel 数据库事务保证操作原子性
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param string[][] $rules 策略规则二维数组
     * @return void
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        DB::transaction(function () use ($sec, $ptype, $rules) {
            foreach ($rules as $rule) {
                $this->removePolicy($sec, $ptype, $rule);
            }
        });
    }

    /**
     * 移除匹配过滤条件的策略规则（自动保存功能）
     *
     * 根据字段索引和字段值过滤并批量删除策略规则
     *
     * @param string $sec         策略段（'p' 或 'g'）
     * @param string $ptype       策略类型
     * @param int    $fieldIndex  字段起始索引（0-5）
     * @param string ...$fieldValues 字段值列表
     * @return void
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $data = $this->getCollection($ptype, $fieldIndex, $fieldValues);
        // 优化：批量删除
        if ($data->isNotEmpty()) {
            $data->each->delete();
        }
    }

    /**
     * 更新单条策略规则（自动保存功能）
     *
     * 找到旧规则并将其更新为新规则，
     * 包含空值判断避免空指针异常
     *
     * @param string   $sec       策略段（'p' 或 'g'）
     * @param string   $ptype     策略类型
     * @param string[] $oldRule   旧策略规则数组
     * @param string[] $newPolicy 新策略规则数组
     * @return void
     */
    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $instance = $this->model->where('ptype', $ptype);
        foreach ($oldRule as $key => $value) {
            $instance->where('v' . $key, $value);
        }
        $instance = $instance->first();

        // 空值判断，避免空指针异常
        if (is_null($instance)) {
            return;
        }

        $update = [];
        foreach ($newPolicy as $key => $value) {
            $update['v' . $key] = $value;
        }

        $instance->fill($update);
        $instance->save();
    }

    /**
     * 批量更新策略规则
     *
     * 使用 Laravel 数据库事务保证操作原子性，
     * 包含边界检查确保新规则数组索引存在
     *
     * @param string     $sec      策略段（'p' 或 'g'）
     * @param string     $ptype    策略类型
     * @param string[][] $oldRules 旧策略规则二维数组
     * @param string[][] $newRules 新策略规则二维数组
     * @return void
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        DB::transaction(function () use ($sec, $ptype, $oldRules, $newRules) {
            foreach ($oldRules as $i => $oldRule) {
                // 边界检查：确保新规则数组索引存在
                if (!isset($newRules[$i])) {
                    continue;
                }
                $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
            }
        });
    }

    /**
     * 更新匹配过滤条件的策略规则
     *
     * 删除匹配过滤条件的旧规则，并添加新的策略规则，
     * 使用 Laravel 数据库事务保证操作原子性
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
            // 获取并删除旧规则
            $oldRules = $this->_removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);
            
            // 删除匹配的旧规则
            $this->removeFilteredPolicy($sec, $ptype, $fieldIndex, ...$fieldValues);
            
            // 添加新规则
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
     * @return void
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $instance = $this->model->query(); // 使用 query() 避免模型状态污染

        if (is_string($filter)) {
            $instance->whereRaw($filter);
        }
        elseif ($filter instanceof Filter) {
            $where = [];
            foreach ($filter->p as $k => $v) {
                $where[$v] = $filter->g[$k];
            }
            $instance->where($where);
        }
        elseif ($filter instanceof Closure) {
            $instance = $instance->where($filter);
        }
        else {
            throw new InvalidFilterTypeException('invalid filter type');
        }
        
        // 查询并处理结果
        $rows = $instance->get()->makeHidden(['created_at', 'updated_at', 'id'])->toArray();
        
        if (!empty($rows)) {
            foreach ($rows as $row) {
                // 过滤空值
                $row = array_filter($row, function ($value) {
                    return !is_null($value) && $value !== '';
                });
                
                // 拼接策略行
                $line = implode(
                    ', ',
                    array_filter($row, function ($val) {
                        return $val !== '' && !is_null($val);
                    })
                );
                
                $this->loadPolicyLine(trim($line), $model);
            }
        }
        
        $this->setFiltered(true);
    }

    /**
     * 获取匹配条件的策略集合
     *
     * 根据策略类型和字段值构建查询条件，返回 Laravel Collection
     *
     * @param string $ptype       策略类型
     * @param int    $fieldIndex  字段起始索引（0-5）
     * @param array  $fieldValues 字段值数组
     * @return Collection Laravel Eloquent 集合
     */
    protected function getCollection(string $ptype, int $fieldIndex, array $fieldValues): Collection
    {
        $where = [
            'ptype' => $ptype,
        ];
        
        foreach (range(0, 5) as $value) {
            $offset = $value - $fieldIndex;
            if ($fieldIndex <= $value && $offset < count($fieldValues)) {
                $fieldValue = $fieldValues[$offset];
                if ($fieldValue !== '' && !is_null($fieldValue)) {
                    $where['v' . $value] = $fieldValue;
                }
            }
        }

        return $this->model->where($where)->get();
    }
}
