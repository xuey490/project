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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as LaSoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Framework\Utils\Snowflake;
use Framework\Basic\Traits\LaBelongsToTenant; // 引入多租户Trait
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BaseLaORMModel extends Model
{
    // 如果你的所有业务表都需要多租户，可以直接在这里引入。
    // 如果系统表不需要，建议在具体的业务 Model 里引入这个 Trait。
    use LaBelongsToTenant; 
	
	//use LaSoftDeletes;

    /**
     * 指明模型的ID是否自动递增 (雪花算法不是自增).
     *
     * @var bool
     */
    public $incrementing = false;

    // 时间戳自动管理（默认true，自动维护created_at/updated_at）
    //public $timestamps = true;
	
    /**
     * 主键类型
     */
    protected $keyType = 'int'; // 雪花ID通常是bigint，PHP端作为int处理

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    // 软删除字段（默认deleted_at，可自定义）
    #protected $dates = ['deleted_at'];
	
    /**
     * 模型日期字段的存储格式 (时间戳).
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * 自动转换日期格式
     * 让 created_at 和 updated_at 拿出来时自动变成 Carbon 对象，
     * 配合 serializeDate 可以控制输出格式
     */
    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    /**
     * 动态隐藏字段
     */
    protected array $dynamicHidden = [];

    /**
     * 雪花算法实例
     */
    private static ?Snowflake $snowflake = null;

    protected static function boot()
    {
        parent::boot();

        // 注册创建事件
        static::creating(function ($model) {
            // 1. 生成雪花 ID
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = self::generateSnowflakeID();
            }
            // 2. 自动填充创建人
            self::setCreatedBy($model);
        });

        // 注册更新事件
        static::updating(function ($model) {
            self::setUpdatedBy($model);
        });

        // 注册删除事件
        static::deleted(function ($model) {
            self::onAfterDelete($model);
        });
    }

    /**
     * 准备日期序列化格式 (API返回JSON时会自动调用)
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->setTimezone(config('app.time_zone', 'PRC'))->format('Y-m-d H:i:s');
    }

    /**
     * 兼容 TP: 动态隐藏字段
     */
    public function hidden(array $fields): static
    {
        $this->makeHidden($fields); // Eloquent 原生支持 makeHidden
        return $this;
    }

    /**
     * 兼容 TP: 获取数据
     */
    public function getData(string $field): mixed
    {
        return $this->getAttribute($field);
    }

    /**
     * 兼容 TP: 设置数据
     */
    public function set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    /**
     * 获取表字段列表 (使用 Schema 门面，兼容性更好)
     */
    public function getFields_1(): array
    {
		
		$tableName = $this->getConnection()->getTablePrefix().$this->getTable();
		#dump(Schema::getColumnListing($this->getTable()));#
        try {
            return Schema::getColumnListing($this->getTable());
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getTable(): string
    {
        if (!empty($this->table)) {
            return $this->table;
        }
        if (property_exists($this, 'name') && !empty($this->name)) {
            return (string) $this->name;
        }
        return parent::getTable();
    }

    /**
     * 获取模型定义的数据库表名【全称】.
     */
    public static function getTableName(): string
    {
        $self = new static();

        return $self->getTable();
    }

    /**
     * 获取模型的字段列表
     *
     * @return array
     */
    public function getFields(): array
    {
        try {
            $tableName     = $this->getTable();
            $connection    = $this->getConnection();	//or DB::connection();
            $prefix        = $connection->getTablePrefix();
            $fullTableName = $prefix . $tableName;
			//$fields        = DB::select("SHOW COLUMNS FROM `{$fullTableName}`");
            $fields        = $connection->select("SHOW COLUMNS FROM `{$fullTableName}`");
            return array_map(function ($column) {
                return $column->Field;
            }, $fields);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取主键名称
     */
    public function getPk(): string
    {
        return $this->getKeyName();
    }

    /**
     * 是否开启软删
     */
    public static function isSoftDeleteEnabled(): bool
    {
        return in_array(LaSoftDeletes::class, class_uses(static::class));
    }

    /**
     * 自动设置创建人
     */
    private static function setCreatedBy(Model $model): void
    {
		$self = new static();
        // 需确保 getCurrentUser() 存在且安全
        $uid = function_exists('getCurrentUser') ? \getCurrentUser() : null;

        // 检查 created_by 是否在 fillable 中或数据库有此字段，防止报错
        // 这里假设只要数据库有这个字段就填，不强制 fillable
        if ($uid && in_array('created_by', $self->getFields())) {
            $model->setAttribute('created_by', $uid);
        }
    }

    /**
     * 自动设置更新人
     */
    private static function setUpdatedBy(Model $model): void
    {
		$self = new static();
        $uid = function_exists('getCurrentUser') ? \getCurrentUser() : null;
        
        if ($uid && in_array('updated_by' ,$self->getFields())) {
             $model->setAttribute('updated_by', $uid);
        }
    }

    /**
     * 处理删除后的逻辑 (回收站)
     */
    public static function onAfterDelete(Model $model)
    {
        // 如果开启了软删除，Eloquent 会自动处理 deleted_at，
        // 这里通常用于处理“真删除”时的归档，或者同步到额外的回收站表
        if ($model::isSoftDeleteEnabled()) {
            return;
        }

        try {
            // 这里建议使用 Facade 或依赖注入，不要直接 new Service
            // 假设 SysRecycleBinService 已经绑定
            /*
            $service = app(\app\services\system\SystemRecycleBinService::class);
            $config = $service->getTableConfig($model->getTable());
            if ($config['enabled']) {
                // 保存逻辑...
            }
            */
        } catch (\Exception $e) {
            // 记录日志，不要抛出异常打断删除流程，除非业务必须
            \Illuminate\Support\Facades\Log::error("Recycle bin error: " . $e->getMessage());
        }
    }

    // ================= 雪花算法部分优化 =================

    /**
     * 生成雪花ID
     */
    public static function generateSnowflakeID(): int
    {
        if (self::$snowflake === null) {
            // 优化：从配置读取 WorkerId 和 DataCenterId
            // 确保不同服务器配置不同，否则ID会冲突
            $workerId = (int) config('app.snowflake_worker_id', 1);
            $dataCenterId = (int) config('app.snowflake_datacenter_id', 1);
            
            self::$snowflake = new Snowflake($workerId, $dataCenterId);
        }
        return self::$snowflake->nextId();
    }
}