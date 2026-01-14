<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Framework\Basic\Traits\TpBelongsToTenant;
use Framework\Utils\Snowflake;
use think\Model as TpModel;
use think\model\concern\SoftDelete as TpSoftDelete;
use think\facade\Config;
use Framework\Tenant\TenantContext;

/**
 * ThinkPHP 8 模型基类封装
 * 特性: 雪花ID, 多租户隔离, 自动时间戳(int), 日期格式化, 软删除
 */
class BaseTpORMModel extends TpModel
{
    use \Framework\ORM\Trait\ModelTrait;
    use TpBelongsToTenant;
    //use TpSoftDelete;

    // =========================================================================
    //  核心配置
    // =========================================================================

    // 时间戳自动写入 (int类型)
    protected $autoWriteTimestamp = true;
    
    // 时间字段定义
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $deleteTime = 'delete_time';

    // 软删除字段默认值 (int类型)
    protected $defaultSoftDelete = 0;

    /**
     * 日期字段列表 (子类可覆盖)
     * @var string[]
     */
    protected $dates = [
        'create_time',
        'update_time',
        'delete_time',
		'created_at',
		'updated_at',
		'deleted_at',		
    ];

    /**
     * 日期输出格式
     */
    public const DATE_OUTPUT_FORMAT = 'Y-m-d H:i:s';
	
    // 主键类型
    protected string $pkType = 'string';

    // 全局作用域 (多租户)
    protected array $globalScope = ['tenant'];

    // 只读字段
    protected $readonly = ['created_by', 'tenant_id'];

    /**
     * 雪花算法单例
     * @var Snowflake|null
     */
    private static ?Snowflake $snowflake = null;

    // 主键策略配置（核心：支持雪花ID）
    protected string $pkGenerateType = 'snowflake'; // auto=自增，snowflake=雪花ID
	
    /**
     * 模型初始化
     * 注意：TP8 中 init() 是静态方法，用于注册事件
     * @return void
     */
    protected function init()
    {
		parent::init(); // 先调用父类 init 方法，避免丢失父类逻辑
    }

    /**
     * 构造函数
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);

        // 兼容表前缀
        if (empty($this->name) && empty($this->table)) {
            $prefix = (string) $this->getConfig('prefix');
            $this->name = $this->getName();
            if ($prefix) {
                $this->table = $prefix . $this->name;
            }
        }
    }
	
    // 支持手动切换主键策略
    public function setPkGenerateType(string $type): void
    {
        $this->pkGenerateType = in_array($type, ['auto','snowflake']) ? $type : 'auto';
    }

    /**
     * 新增前钩子：主键生成+自动时间戳（修改：适配自定义时间字段）
     */
    protected static function beforeInsert(TpModel $model): void
    {
        
        // 关键修复：直接读取模型的 $createTime/$updateTime 属性（字段名）
        $createTimeField = $model->createTime; // 直接获取子类配置的字段名，如果子类未定义直接获取父类（如 created_at）
        $updateTimeField = $model->updateTime; // 直接获取子类配置的字段名，如果子类未定义直接获取父类（如 updated_at）

        // 自动填充int类型时间戳
        if (!empty($model->createTime)) {
            $model->setAttr($createTimeField, time()); // 用 setAttr 安全赋值
        }
        if (!empty($model->updateTime)) {
            $model->setAttr($updateTimeField, time()); // 用 setAttr 安全赋值
        }
    }
    

    /**
     * 模型事件：新增前 https://doc.thinkphp.cn/@think-orm/v4_0/model_event.html
     */
    public static function onBeforeInsert(TpModel $model): void
    {
		#$static = new static;
        try {
			self::beforeInsert($model); // 恢复调用（之前被注释了）
			if ($model->pkGenerateType === 'snowflake'){
				self::setPrimaryKey($model);
			}
			self::setTenantId($model);
			
			self::setCreatedBy($model);
			
			self::normalizeDateFields($model);
			
        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
    }
    /**
     * 模型事件：更新前事件
     */
    public static function onBeforeUpdate(TpModel $model): void
    {
        // 1. 检查是否越权（仅针对已存在的模型对象操作）
        self::checkTenantAccess($model);
        
        // 2. 自动填充更新人
        self::setUpdatedBy($model);
		
		self::normalizeDateFields($model);
    }
	
	/*
	* 模型事件：删除前校验（物理 & 软删通吃）
	*/
    public static function onBeforeDelete(TpModel $model): void
    {
        // 1. 检查是否越权
        self::checkTenantAccess($model);
    }

    /**
     * 模型事件：更新后事件
     */
    public static function onAfterUpdate(TpModel $model): void
    {
        
    }

    /**
     * 模型事件：删除后
     */
    public static function onAfterDelete(TpModel $model): void
    {
        if ($model->isSoftDeleteEnabled()) {
            return;
        }
        $table     = $model->getName();
        $tableData = $model->getData();
        $prefix    = $model->getConfig('prefix');
		
        try {
            // 你的删除后逻辑（如果有）
        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
    }
	
	/**
	 * 模型事件:查询后
	 */
	public static function onAfterRead(\think\Model $model): void
	{
		// 只处理模型实例
		if (!($model instanceof self)) {
			return;
		}
		$model->formatDateFields();
	}

    /**
     * 子类可扩展日期字段（推荐方式）
     */
    protected function extraDates(): array
    {
        return [];
    }

    /**
     * 统一日期字段入口
     */
    public function getDates(): array
    {
        return array_values(array_unique(array_merge(
            $this->dates ?? [],
            $this->extraDates()
        )));
    }

    /**
     * 获取字段列表 (安全处理)
     * @param string|null $field
     * @return array|mixed
     */
    public function getFields(?string $field = null): mixed
    {
        $res = parent::getFields($field);
        return $field ? $res : ($res ?: []);
    }

    /**
     * 判断是否开启软删除
     * @return bool
     */
    public function isSoftDeleteEnabled(): bool
    {
        return method_exists($this, 'delete');
    }

    /**
     * 强制物理删除
     * @param mixed $id
     * @return bool
     */
    public static function forceDeleteById($id): bool
    {
        return self::withTrashed()->where((new static)->getPk(), $id)->delete(true);
    }

    /**
     * 恢复软删除
     * @param mixed $id
     * @return bool
     */
    public static function restoreById($id): bool
    {
        $model = self::onlyTrashed()->find($id);
        return $model ? $model->restore() : false;
    }

    // =========================================================================
    //  辅助方法
    // =========================================================================
		/**
     * 安全的 Join 方法，自动追加租户ID
     * @param string $joinTable  关联表名 (如 'oa_order')
     * @param string $alias      关联表别名 (如 'o')
     * @param string $condition  关联条件 (如 'o.user_id = u.id')
     * @param string $type       JOIN类型 (LEFT, INNER等)
     */
	 /*// 使用封装好的 scopeJoinTenant
	$list = User::alias('u')
    ->joinTenant('oa_order', 'o', 'o.user_id = u.id') // 自动补全 tenant_id
    ->select();*/
    public function scopeJoinTenant($query, string $joinTable, string $alias, string $condition, string $type = 'LEFT')
    {
        $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;
        
        // 只有当存在租户ID时，才追加限制
        if ($tenantId) {
            $condition .= " AND {$alias}.tenant_id = {$tenantId}";
        }
        
        // 执行原生 join
        $query->join("{$joinTable} {$alias}", $condition, $type);
    }

    /**
     * 获取完整表名
     */
    public static function getTableName(): string
    {
        return (new static)->getTable();
    }



    /**
     * 格式化日期字段 (时间戳 -> 字符串)
     * @return void
     */
    private function formatDateFields(): void
    {
        foreach ($this->getDates() as $field) {
            $value = $this->getData($field);
            if ($value > 0) {
                // 直接设置内部数据，避免触发获取器循环
                #$this->$field = date(self::DATE_OUTPUT_FORMAT, (int)$value);
				$this->setAttr($field, date(self::DATE_OUTPUT_FORMAT, (int)$value));
            }
        }
    }
	
	/**
	 * 格式化日期字段 姊妹篇（不破坏原始 data）
	 */
	protected function formatDateFields1(): void
	{
		foreach ($this->getDates() as $field) {

			// 原始值（int）
			$raw = $this->getData($field);

			if (!$raw || !is_numeric($raw)) {
				continue;
			}

			// 放到 append 中作为展示字段
			$this->append([$field . '_text']);

			// 动态设置虚拟字段值
			$this->setAttr($field . '_text', date(self::DATE_OUTPUT_FORMAT, (int)$raw));
		}
	}
	
    /**
     * 通用方法：将日期字符串/时间戳转为 int 时间戳
     * @param mixed $value 输入值
     * @return int
     */
    private function convertToTimestamp($value)
    {
        if (is_numeric($value)) {
            return (int)$value;
        } else {
            $timestamp = strtotime($value);
            return $timestamp !== false ? (int)$timestamp : 0;
        }
    }
	
	/**
	 * 将所有日期字段统一转为 int 时间戳
	 */
	protected static function normalizeDateFields(\think\Model $model): void
	{
		foreach ($model->getDates() as $field) {

			// 如果模型里根本没有这个字段，跳过
			if (!$model->hasData($field)) {
				continue;
			}

			$value = $model->getData($field);

			// 已经是 int，直接跳过
			if (is_int($value) || ctype_digit((string)$value)) {
				continue;
			}

			// 空值处理
			if ($value === null || $value === '') {
				continue;
			}

			// 尝试解析字符串日期
			$timestamp = strtotime((string)$value);

			if ($timestamp !== false) {
				$model->setAttr($field, $timestamp);
			}
		}
	}
	

    /**
     * 获取当前租户ID (封装函数依赖)
     * @return string|null
     */
    protected static function getCurrentTenantId(): ?string
    {
        // 这里可以优先检查 TenantContext，其次检查辅助函数
        if (class_exists(TenantContext::class)) {
            return (string)TenantContext::getTenantId()?? null;
        }
        
    }

    /**
     * 获取当前用户ID
     * @return mixed
     */
    protected static function getCurrentUser()
    {
        return function_exists('getCurrentUser') ? getCurrentUser() : null;
    }

    /**
     * 数据安全检查 (防止越权)
     * 在更新和删除前触发
     * @param TpModel $model
     * @return void
     * @throws \think\exception\ValidateException
     */
    protected static function checkTenantAccess(TpModel $model): void
    {
        $currentTenantId = self::getCurrentTenantId();
        if (!$currentTenantId) {
            return; // 无租户上下文，跳过
        }

        // 获取数据原始的 tenant_id
        $dataTenantId = $model->getOrigin('tenant_id');

        if ($dataTenantId && (string)$dataTenantId !== (string)$currentTenantId) {
            throw new \think\exception\ValidateException('无权操作此条数据（租户不匹配）');
        }
    }

    // =========================================================================
    //  雪花ID生成
    // =========================================================================

    /**
     * 生成雪花ID
     * @return int
     */
    protected static function generateSnowflakeID(): int
    {
        if (self::$snowflake === null) {
            $workerId =1;
            $datacenterId = 1;
            self::$snowflake = new Snowflake($workerId, $datacenterId);
        }
        return self::$snowflake->nextId();
    }
	
	
    // =========================================================================
    //  辅助私有方法
    // =========================================================================
	

    // =========================================================================
    //  2. 修复 setPrimaryKey 方法（仅雪花ID模式生效）
    // =========================================================================
    private static function setPrimaryKey(TpModel $model): void
    {       
        $pk = $model->getPk();
        if ( is_string($pk) && empty($model->{$pk})) {
            $model->{$pk} = (string) self::generateSnowflakeID();
        }
    }

    private static function setTenantId(TpModel $model): void
    {
        if (!isset($model->tenant_id)) {
            $tenantId = self::getCurrentTenantId();
            if ($tenantId) {
                $model->setAttr('tenant_id', $tenantId);
            }
        }
    }

    private static function setCreatedBy(TpModel $model): void
    {
        $uid = function_exists('getCurrentUser') ? \getCurrentUser() : null;
        if ($uid) {
            $model->setAttr('created_by', $uid);
        }
    }

    private static function setUpdatedBy(TpModel $model): void
    {
        $uid = function_exists('getCurrentUser') ? \getCurrentUser() : null;
		$model->setAttr($model->updateTime, time());
        if ($uid) {
            $model->setAttr('updated_by', $uid);
        }
    }
	
	
}