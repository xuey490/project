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

/**
 * UniversalDatabaseAdapter - 通用数据库适配器
 *
 * 该适配器不依赖任何特定框架，使用原生 PDO 实现，
 * 适用于需要独立数据库连接的场景。实现了 Casbin 的多个持久化接口：
 * - Adapter: 基础策略加载和保存
 * - UpdatableAdapter: 策略更新功能
 * - BatchAdapter: 批量策略操作
 * - FilteredAdapter: 过滤式策略加载（部分方法未实现）
 *
 * @package Framework\Casbin\Adapter
 */
class UniversalDatabaseAdapter implements Adapter, UpdatableAdapter, BatchAdapter, FilteredAdapter
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
     * 使用原生 PDO 操作数据库
     *
     * @var CasbinRuleModel
     */
    protected CasbinRuleModel $model;

    /**
     * 构造函数
     *
     * 根据数据库配置初始化适配器
     *
     * @param array $dbConfig 数据库配置数组
     *                        - host: 数据库主机地址
     *                        - port: 数据库端口
     *                        - database: 数据库名称
     *                        - username: 数据库用户名
     *                        - password: 数据库密码
     *                        - charset: 字符集
     *                        - table: 数据表名称（可选）
     */
    public function __construct(array $dbConfig)
    {
        $this->model = new CasbinRuleModel($dbConfig);
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
     * 将策略类型和规则值组合成数据库记录格式，
     * 使用 updateOrCreate 避免重复插入
     *
     * @param string $ptype 策略类型（如 'p' 表示权限策略，'g' 表示角色继承）
     * @param array  $rule  策略规则值数组
     * @return void
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
     * 从数据库加载所有策略规则到模型
     *
     * 查询数据库中的所有策略记录，并将其加载到 Casbin 模型中
     *
     * @param Model $model Casbin 模型实例
     * @return void
     */
    public function loadPolicy(Model $model): void
    {
        $rows = $this->model->getAll();
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
     * 将多条策略规则一次性插入数据库，使用事务保证原子性
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param array      $rules 策略规则二维数组
     * @return void
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
     * 从数据库移除单条策略规则（自动保存功能）
     *
     * 根据策略类型和规则值精确匹配并删除对应记录
     *
     * @param string $sec   策略段（'p' 或 'g'）
     * @param string $ptype 策略类型
     * @param array  $rule  策略规则值数组
     * @return void
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
     * 批量移除策略规则（自动保存功能）
     *
     * 逐条调用 removePolicy 移除规则
     *
     * @param string     $sec   策略段（'p' 或 'g'）
     * @param string     $ptype 策略类型
     * @param array      $rules 策略规则二维数组
     * @return void
     */
    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        foreach ($rules as $rule) {
            $this->removePolicy($sec, $ptype, $rule);
        }
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
     * @return void
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
     * 更新单条策略规则（自动保存功能）
     *
     * 找到旧规则并将其更新为新规则
     *
     * @param string $sec     策略段（'p' 或 'g'）
     * @param string $ptype   策略类型
     * @param array  $oldRule 旧策略规则数组
     * @param array  $newRule 新策略规则数组
     * @return void
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

    /**
     * 批量更新策略规则
     *
     * 逐条调用 updatePolicy 更新规则
     *
     * @param string     $sec      策略段（'p' 或 'g'）
     * @param string     $ptype    策略类型
     * @param array      $oldRules 旧策略规则二维数组
     * @param array      $newRules 新策略规则二维数组
     * @return void
     */
    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        foreach ($oldRules as $i => $oldRule) {
            $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
        }
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
     * @throws RuntimeException 此方法暂未实现
     */
    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {
        throw new RuntimeException("此方法暂未实现");
    }

    /**
     * 加载匹配过滤条件的策略规则
     *
     * @param Model $model  Casbin 模型实例
     * @param mixed $filter 过滤条件
     * @throws RuntimeException 此方法暂未实现
     * @return void
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        throw new RuntimeException("此方法暂未实现");
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
}
