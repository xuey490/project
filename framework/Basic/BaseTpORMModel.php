<?php

declare(strict_types=1);

namespace Framework\Basic;

use Framework\Basic\Scope\TpTenantScope;
use Framework\Utils\Snowflake;
use think\Model as TpModel;
use think\model\concern\SoftDelete as TpSoftDelete;
use think\facade\Config;

/**
 * ThinkPHP 模型基类封装 (适配 TP6.0 / TP8.0)
 */
class BaseTpORMModel extends TpModel
{
    // [重要] 如果 ModelTrait 里面有 use Illuminate\... 或者定义了 restore() 方法，会再次报错！
    // 建议先排查 ModelTrait，确认无误后再开启。
    use \Framework\ORM\Trait\ModelTrait;
    
    // 引入 ThinkPHP 自带的软删除
    #use TpSoftDelete;

    // =========================================================================
    //  基础配置
    // =========================================================================

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true; 
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
    protected $globalScope = [TpTenantScope::class];

    // 只读字段
    protected $readonly = ['created_by', 'create_time', 'tenant_id'];

    /**
     * 雪花算法单例
     */
    private static ?Snowflake $snowflake = null;

    // =========================================================================
    //  模型事件 (ThinkPHP 6/8 标准静态方法)
    // =========================================================================

    /**
     * 模型事件：新增前
     * 注意：这里必须用 TpModel，因为上面 use ... as TpModel
     */
    public static function onBeforeInsert(TpModel $model): void
    {
        try {
			self::setPrimaryKey($model);
			self::setTenantId($model);
			self::setCreatedBy($model);
        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
		
    }

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



        } catch (\Exception $e) {
            throw new \BadMethodCallException($e->getMessage());
        }
		
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
    }

    /**
     * 初始化 (非静态)
     */
    protected function init()
    {
        parent::init();
        // 如果需要默认过滤软删除，TpSoftDelete 会自动处理，无需手动 withScope
    }


	/**
     * 获取模型定义的字段列表
     * 修正：移除 :array 强类型限制，因为当传入 $field 时，父类会返回 string
     *
     * @param string|null $field
     * @return mixed
     */
    public function getFields(?string $field = null):mixed
    {
        $res = parent::getFields($field);
        
        // 如果查询具体字段，直接返回结果（可能是字符串）
        if ($field) {
            return $res;
        }
        
        // 如果查询全部字段，确保返回数组
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
        // withTrashed() 是 ThinkPHP SoftDelete 提供的
        return self::withTrashed()->where((new static)->getPk(), $id)->delete(true);
    }
    
    /**
     * 恢复软删除数据
     * 注意：不要命名为 restore，会和 Trait 冲突。这里叫 restoreById
     */
    public static function restoreById($id): bool
    {
        // TP 的 SoftDelete trait 已经自带了 restore() 方法用于实例调用
        // 这里是静态封装
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