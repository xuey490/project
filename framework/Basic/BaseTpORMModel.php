<?php

declare(strict_types=1);

namespace Framework\Basic;

use Framework\Basic\Scopes\TpTenantScope;
use Framework\Basic\Traits\TpBelongsToTenant;
use Framework\Utils\Snowflake;
use think\Model as TpModel;
use think\model\concern\SoftDelete as TpSoftDelete;
use think\facade\Config;
use think\db\Query;
use Framework\Tenant\TenantContext;

/**
 * ThinkPHP 模型基类封装 (适配 TP6.0 / TP8.0)
 */
class BaseTpORMModel extends TpModel
{
    use \Framework\ORM\Trait\ModelTrait;
    use TpBelongsToTenant;
    #use TpSoftDelete;

    // =========================================================================
    //  基础配置（修改：让子类可覆盖）
    // =========================================================================

    // 自动写入时间戳字段（改为 int 类型，适配数据库 int(11)）
    protected $autoWriteTimestamp = 'int'; 
    // 默认时间字段（子类可覆盖）
    protected $createTime = 'create_time'; 
    protected $updateTime = 'update_time'; 
    protected $deleteTime = 'delete_time'; 
    
    // 软删除字段默认值
    protected $defaultSoftDelete = null;

    // 设置主键类型 (雪花ID需设为 string 避免 JS 精度丢失)
    protected $pkType = 'string'; 

    /**
     * 注册全局作用域 (实现 SaaS 多租户隔离)
     */
    #protected $globalScope = [TpTenantScope::class];
	protected $globalScope = ['tenant'];

    // 只读字段（修改：用变量引用，支持子类覆盖）
    protected $readonly = ['created_by', 'tenant_id'];

    /**
     * 雪花算法单例
     */
    private static ?Snowflake $snowflake = null;

    // =========================================================================
    //  模型事件 (ThinkPHP 6/8 标准静态方法)
    // =========================================================================

    // 主键策略配置（核心：支持雪花ID）
    protected $pkGenerateType = 'auto'; // auto=自增，snowflake=雪花ID
    
    /**
     * 新增前钩子：主键生成+自动时间戳（修改：适配自定义时间字段）
     */
    protected function beforeInsert(TpModel $model): void
    {
        // 雪花ID生成逻辑
        if ($this->pkGenerateType === 'snowflake' && empty($model->{$model->getPk()})) {
            $model->{$model->getPk()} = (string) self::generateSnowflakeID();
        }
        
        // 关键修复：直接读取模型的 $createTime/$updateTime 属性（字段名）
        $createTimeField = $this->createTime; // 直接获取子类配置的字段名，如果子类未定义直接获取父类（如 created_at）
		
        $updateTimeField = $this->updateTime; // 直接获取子类配置的字段名，如果子类未定义直接获取父类（如 updated_at）
        
        // 自动填充int类型时间戳
        if (empty($model->$createTimeField)) {
            $model->setAttr($createTimeField, time()); // 用 setAttr 安全赋值
        }
        if (empty($model->$updateTimeField)) {
            $model->setAttr($updateTimeField, time()); // 用 setAttr 安全赋值
        }
    }
    
    /**
     * 更新前钩子：自动填充更新时间（修改：适配自定义时间字段）
     */
    protected function beforeUpdate(): void
    {
        $updateTimeField = $this->getUpdateTime(); // 获取子类配置的更新时间字段名
        $this->$updateTimeField = time(); // 赋值 int 时间戳
    }
    
    // 支持手动切换主键策略
    public function setPkGenerateType(string $type): void
    {
        $this->pkGenerateType = in_array($type, ['auto','snowflake']) ? $type : 'auto';
    }

    /**
     * 模型事件：新增前
     */
    public static function onBeforeInsert(TpModel $model): void
    {
		$static = new static;
		
        try {
			$static->beforeInsert($model); // 恢复调用（之前被注释了）
			self::setPrimaryKey($model);
			self::setTenantId($model);
			self::setCreatedBy($model);
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

        // 3. 执行原有的 beforeUpdate 逻辑（处理时间戳等）
        // 注意：静态事件中调用非静态方法需要 trick，或者将 beforeUpdate 逻辑挪到这里
        // TP的标准做法是 $model->beforeUpdate() 是内部回调，这里是事件
        // 如果你依赖 $model->beforeUpdate()，请确保它被调用
    }
	 /* 此函数用于带上下文的操作
	public static function onBeforeUpdate(TpModel $model): void
	{
		// 超管可绕过
		if (!TenantContext::shouldApplyTenant()) {
			return;
		}

		// 没有 tenant_id 字段，不参与租户校验
		if (!array_key_exists('tenant_id', $model->getData())) {
			return;
		}
	
		$currentTenant = TenantContext::getTenantId();
		
		$recordTenant  = $model->getData()['tenant_id'] ?? null;

		// 🚫 尝试更新不属于当前租户的数据
		if ($recordTenant != $currentTenant) {
			throw new \Exception('Tenant access denied (update)', 403);
		}
	}
	*/
	
	/*
	* 模型事件：删除前校验（物理 & 软删通吃）
	*/
    public static function onBeforeDelete(TpModel $model): void
    {
        // 1. 检查是否越权
        self::checkTenantAccess($model);
    }
	
	/* 此函数用于带上下文的操作
	public static function onBeforeDelete(TpModel $model): void
	{

		if (!TenantContext::shouldApplyTenant()) {
			return;
		}

		if (!array_key_exists('tenant_id', $model->getData())) {
			return;
		}

		$currentTenant = TenantContext::getTenantId();
		
		$recordTenant  = $model->getData()['tenant_id'] ?? null;
		
		if ($recordTenant != $currentTenant) {
			throw new Exception('Tenant access denied (delete)', 403);
		}
	}*/

    /**
     * 模型事件：更新后事件
     */
    public static function onAfterUpdate(TpModel $model): void
    {
        self::setUpdatedBy($model);
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
     * 【可选】查询结束后自动重置租户隔离标识（避免静态属性污染）
     * 可在 select/find 等方法后调用，或通过模型事件自动重置
     */
    public function afterQuery(): void
    {
        static::restoreTenant();
    }
	
	//可用，依赖上下文传递类
	public function scopeTenant2($query): void
	{
		// 1. 当前上下文不启用租户隔离
		if (!TenantContext::shouldApplyTenant()) {
			return;
		}

		// 2. 当前模型没有 tenant_id 字段
		if (!in_array('tenant_id', array_keys($this->getFields()))) {
			return;
		}

		// 3. 正常加租户条件
		$query->where(
			$this->getTable() . '.tenant_id',
			TenantContext::getTenantId()
		);
	}

	//可用不严谨
	public function scopeTenant1($query): void
	{

		$tenantId = function_exists('getCurrentTenantId')
			? getCurrentTenantId()
			: 1001;
		
		if ($tenantId && in_array('tenant_id' , array_keys($this->getFields()) ) ) {
			$query->where(
				$this->getTable() . '.tenant_id',
				$tenantId
			);
		}
	}
	
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
	
    // =========================================================================
    //  核心方法
    // =========================================================================

    /**
     * 构造函数
     * 兼容处理表前缀逻辑
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        
        if (empty($this->name) && empty($this->table)) {
            $prefix = (string) $this->getConfig('prefix');
            $this->name = $this->getName();
            if ($prefix) {
                $this->table = $prefix . $this->name;
            }
        }
		
		#static::initTpBelongsToTenant();
    }

    /**
     * TP8 模型初始化方法（非静态，实例化时触发）
     * 主动调用 Trait 的初始化方法，确保多租户逻辑生效
     */
    protected function init()
    {
        parent::init(); // 先调用父类 init 方法，避免丢失父类逻辑
    }	

	/**
     * 获取模型定义的字段列表
     */
    public function getFields(?string $field = null):mixed
    {
        $res = parent::getFields($field);
        
        if ($field) {
            return $res;
        }
        
        return $res ?: [];
    }

    /**
     * 判断是否开启软删
     */
    public function isSoftDeleteEnabled(): bool
    {
        return in_array(TpSoftDelete::class, class_uses(static::class));
    }

    /**
     * 强制物理删除
     */
    public static function forceDeleteById($id): bool
    {
        return self::withTrashed()->where((new static)->getPk(), $id)->delete(true);
    }

    
    /**
     * 恢复软删除数据
     */
    public static function restoreById($id): bool
    {
        $model = self::onlyTrashed()->find($id);
        if ($model) {
            return $model->restore();
        }
        return false;
    }

    /**
     * 获取完整表名
     */
    public static function getTableName(): string
    {
        return (new static)->getTable();
    }

    // =========================================================================
    //  辅助私有方法
    // =========================================================================
	
    /**
     * 【新增】安全检查：防止越权操作
     * 场景：管理员A查询了数据，然后切换了租户身份，或者Session混乱时尝试修改数据
     */
    protected static function checkTenantAccess(TpModel $model): void
    {
        // 获取当前租户
        $currentTenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;
        
        // 如果没有开启多租户或当前是超管模式，跳过
        if (!$currentTenantId) {
            return;
        }

        // 获取数据原本的 tenant_id
        // getOrigin() 获取原始数据，防止被修改后的数据欺骗
        $dataTenantId = $model->getOrigin('tenant_id');

        // 如果数据本身有 tenant_id，且不等于当前租户ID，抛出异常
        if ($dataTenantId && (string)$dataTenantId !== (string)$currentTenantId) {
            // 这里抛出异常，前端会收到 500 错误，保护数据
            throw new \think\exception\ValidateException('无权操作此条数据（租户不匹配）');
        }
    }
	

    private static function setPrimaryKey(TpModel $model): void
    {
        $pk = $model->getPk();
        if (is_string($pk) && empty($model->{$pk})) {
            $model->{$pk} = (string) self::generateSnowflakeID();
        }
    }

    private static function setTenantId(TpModel $model): void
    {
        if (!isset($model->tenant_id)) {
            $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;
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
		$model->setAttr('update_time', time());
        if ($uid) {
            $model->setAttr('updated_by', $uid);
        }
    }

    protected static function generateSnowflakeID(): int
    {
        if (self::$snowflake === null) {
            $workerId =1;
            $datacenterId = 1;
            self::$snowflake = new Snowflake($workerId, $datacenterId);
        }
        return self::$snowflake->nextId();
    }
}