<?php

declare(strict_types=1);

namespace Framework\Basic;

use Framework\Basic\Scope\TpTenantScope;
use Framework\Utils\Snowflake;
use think\Model;
use think\model\concern\SoftDelete;
use think\facade\Config;

/**
 * ThinkPHP 模型基类封装
 */
class BaseTpORMModel extends Model
{
    // 引入软删除 (TP自带)
    use SoftDelete;

    // 自动写入时间戳字段 (TP自带功能)
    protected $autoWriteTimestamp = true; // 开启自动写入
    protected $createTime = 'create_time'; // 定义创建时间字段名
    protected $updateTime = 'update_time'; // 定义更新时间字段名
    protected $deleteTime = 'delete_time'; // 定义软删除字段名
    
    // 默认时间格式 (根据你的数据库类型，如果是int存时间戳，这里留空或 'int')
    // protected $dateFormat = 'Y-m-d H:i:s'; 

    // 设置主键类型为 string (因为雪花ID是bigint，前端JS丢失精度，通常转字符串，或者后端用 bigint)
    // 如果 PHP 是 64位，可以保持 int，但建议 string 兼容性更好
    protected $pkType = 'string'; 

    /**
     * 注册全局作用域
     * 这里直接引入 TpTenantScope，所有继承该类的模型都会自动隔离租户
     * @var array
     */
    protected $globalScope = [TpTenantScope::class];

    // 只读字段
    protected $readonly = ['created_by', 'create_time', 'tenant_id'];

    /**
     * 雪花算法单例
     */
    private static ?Snowflake $snowflake = null;

    /**
     * 模型初始化
     * TP模型的入口，相当于 Laravel 的 boot()
     */
    protected static function init()
    {
        parent::init();

        // 注册：新增前 (Insert)
        static::event('before_insert', function (Model $model) {
            // 1. 生成雪花ID
            self::setPrimaryKey($model);
            // 2. 自动设置租户ID
            self::setTenantId($model);
            // 3. 设置创建人
            self::setCreatedBy($model);
        });

        // 注册：写入前 (Insert & Update)
        static::event('before_write', function (Model $model) {
            self::setUpdatedBy($model);
        });

        // 注册：删除后
        static::event('after_delete', function (Model $model) {
            self::onAfterDelete($model);
        });
    }

    /**
     * 获取模型定义的字段列表.
     *
     * @return mixed
     */
    public function getFields(?string $field = null): array
    {
        // 父类原版逻辑
        $array = parent::getFields($field);
        if (! $array) {
            return [];
        }
        return parent::getFields($field);
    }

    /**
     * 是否开启软删.
     */
    public function isSoftDeleteEnabled(): bool
    {
        return in_array(SoftDelete::class, class_uses(static::class));
    }

    /**
     * 获取模型定义的数据库表名【全称】.
     */
    public static function getTableName(): string
    {
        $self = new static();
        $prefix = (string) $self->getConfig('prefix');
        if (!empty($self->table)) {
            return $self->table;
        }
        return $prefix . (string) $self->name;
    }

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $prefix = (string) $this->getConfig('prefix');
        
        if (!empty($this->table) && empty($this->name)) {
            $t = (string) $this->table;
            if ($prefix !== '' && strncmp($t, $prefix, strlen($prefix)) === 0) {
                $this->name = substr($t, strlen($prefix));
                
            } else {
                $this->name = $t;
                if ($prefix !== '') {
                    $this->table = $prefix . $t;
                }
            }
        }
        
        if (!empty($this->name) && empty($this->table)) {
            $this->table = ($prefix !== '' ? $prefix . $this->name : $this->name);
        }
    }

 /**
     * 设置主键 (雪花ID)
     */
    private static function setPrimaryKey(Model $model): void
    {
        $pk = $model->getPk();
        // 如果主键为空，则生成
        if (empty($model->{$pk})) {
            $model->{$pk} = self::generateSnowflakeID();
        }
    }

    /**
     * 设置租户ID (SaaS核心)
     */
    private static function setTenantId(Model $model): void
    {
        // 只有当模型没有手动设置 tenant_id 时才自动填充
        if (!isset($model->tenant_id)) {
            // 假设 getCurrentTenantId() 是全局函数
            $tenantId = function_exists('getCurrentTenantId') ? \getCurrentTenantId() : null;
            
            // 简单检测：如果数据表里没有 tenant_id 字段，不要强行设置，防止报错
            // 注意：schema检测会消耗性能，建议开发规范强制要求业务表必须有 tenant_id
            if ($tenantId) {
                $model->setAttr('tenant_id', $tenantId);
            }
        }
    }

    /**
     * 设置创建人
     */
    private static function setCreatedBy(Model $model): void
    {
        $uid = function_exists('getCurrentUser') ? \getCurrentUser() : null;
        if ($uid) {
            $model->setAttr('created_by', $uid);
        }
    }

    /**
     * 设置更新人
     */
    private static function setUpdatedBy(Model $model): void
    {
        $uid = function_exists('getCurrentUser') ? \getCurrentUser() : null;
        if ($uid) {
            $model->setAttr('updated_by', $uid);
        }
    }

    /**
     * 生成雪花ID
     */
    protected static function generateSnowflakeID(): int
    {
        if (self::$snowflake === null) {
            // 优化：从配置读取，防止硬编码
            $workerId = (int) Config::get('snowflake.worker_id', 1);
            $datacenterId = (int) Config::get('snowflake.data_center_id', 1);
            self::$snowflake = new Snowflake($workerId, $datacenterId);
        }
        return self::$snowflake->nextId();
    }

    /**
     * 删除后逻辑
     */
    public static function onAfterDelete(Model $model)
    {
        // TP的 SoftDelete Trait 只有在 force/destroy(true) 时才是真删
        // 如果你使用了 SoftDelete，普通的 ->delete() 不会触发物理删除，而是更新 delete_time
        
        // 你的回收站逻辑：
        // 如果开启了软删除，这里通常不需要做什么，因为 delete_time 已经标记了
        // 如果需要记录操作日志或移动到归档表，可以在这里写
        
        /* 
        try {
           // ... 你的回收站逻辑
        } catch (\Exception $e) {
           // 记录日志，不要抛出异常中断流程
        } 
        */
    }

    /**
     * 辅助方法：判断是否开启软删
     */
    public function isSoftDeleteEnabled(): bool
    {
        // 检查是否使用了 SoftDelete trait
        return in_array(SoftDelete::class, class_uses(static::class));
    }
}
