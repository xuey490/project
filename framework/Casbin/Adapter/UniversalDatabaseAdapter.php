<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: UniversalDatabaseAdapter.php
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
use Framework\Casbin\Model\CasbinRuleModel;
use RuntimeException;

class UniversalDatabaseAdapter implements Adapter, UpdatableAdapter, BatchAdapter, FilteredAdapter
{
    use AdapterHelper;

    /**
     * 是否已过滤
     * @var bool
     */
    private bool $filtered = false;

    /**
     * 规则模型实例
     * @var CasbinRuleModel
     */
    protected CasbinRuleModel $model;

    /**
     * 构造函数
     * @param array $dbConfig 数据库配置
     */
    public function __construct(array $dbConfig)
    {
        $this->model = new CasbinRuleModel($dbConfig);
    }

    /**
     * 过滤规则
     * @param array $rule
     * @return array
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
     * 保存单条规则
     * @param string $ptype
     * @param array $rule
     */
    public function savePolicyLine(string $ptype, array $rule): void
    {
        $data['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $data["v{$key}"] = $value;
        }
        $this->model->updateOrCreate($data);
    }

    /**
     * 加载所有策略
     * @param Model $model
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->model->getAll();
        foreach ($rows as $row) {
            $this->loadPolicyArray($this->filterRule($row), $model);
        }
    }

    /**
     * 保存所有策略
     * @param Model $model
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
     * 添加单条策略
     * @param string $sec
     * @param string $ptype
     * @param array $rule
     */
    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    /**
     * 批量添加策略
     * @param string $sec
     * @param string $ptype
     * @param array $rules
     */
    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        $batchData = [];
        foreach ($rules as $rule) {
            $data['ptype'] = $ptype;
            foreach ($rule as $key => $value) {
                $data["v{$key}"] = $value;
            }
            $batchData[] = $data;
        }
        $this->model->batchAdd($batchData);
    }

    /**
     * 移除单条策略
     * @param string $sec
     * @param string $ptype
     * @param array $rule
     */
    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $where['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $where["v{$key}"] = $value;
        }
        $this->model->delete($where);
    }

    /**
     * 批量移除策略
     * @param string $sec
     * @param string $ptype
     * @param array $rules
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        foreach ($rules as $rule) {
            $this->removePolicy($sec, $ptype, $rule);
        }
    }

    /**
     * 移除过滤后的策略
     * @param string $sec
     * @param string $ptype
     * @param int $fieldIndex
     * @param string ...$fieldValues
     */
    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $where['ptype'] = $ptype;
        foreach (range(0, 5) as $i) {
            if ($fieldIndex <= $i && $i < $fieldIndex + count($fieldValues)) {
                $where["v{$i}"] = $fieldValues[$i - $fieldIndex];
            }
        }
        $this->model->delete($where);
    }

    /**
     * 更新策略
     * @param string $sec
     * @param string $ptype
     * @param array $oldRule
     * @param array $newRule
     */
    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newRule): void
    {
        $oldData['ptype'] = $ptype;
        foreach ($oldRule as $key => $value) {
            $oldData["v{$key}"] = $value;
        }

        $newData['ptype'] = $ptype;
        foreach ($newRule as $key => $value) {
            $newData["v{$key}"] = $value;
        }

        $this->model->update($oldData, $newData);
    }

    // 以下方法为接口必需，如需使用可补充实现
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        foreach ($oldRules as $i => $oldRule) {
            $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
        }
    }

    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {
        throw new RuntimeException("此方法暂未实现");
    }

    public function loadFilteredPolicy(Model $model, $filter): void
    {
        throw new RuntimeException("此方法暂未实现");
    }

    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }
}