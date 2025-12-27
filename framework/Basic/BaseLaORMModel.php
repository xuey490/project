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
    public $timestamps = true;
	
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
        'id'         => 'string', // 前端JS精度丢失问题，建议序列化时转string		
    ];

    /**
     * 动态隐藏字段
     */
    protected array $dynamicHidden = [];

    /**
     * 雪花算法实例
     */
    private static ?Snowflake $snowflake = null;

    // 【优化】静态内存缓存表字段，避免重复查询数据库
    private static array $tableColumns = [];
	
    protected static function boot1()
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


	protected static function boot()
	{
		parent::boot();

		// 注册创建事件
		static::creating(function ($model) {
			// 1. 生成雪花 ID
			if (empty($model->{$model->getKeyName()})) {
				$model->{$model->getKeyName()} = self::generateSnowflakeID();
			}
			// 2. 自动填充创建人（使用优化后的 fillAuditColumn 方法）
			$model->fillAuditColumn('created_by');
		});

		// 注册更新事件
		static::updating(function ($model) {
			$model->fillAuditColumn('updated_by');
		});

		// 注册删除事件
		static::deleted(function ($model) {
			self::onAfterDelete($model);
		});
	}

    /**
     * 【性能优化核心】填充审计字段
     * 避免使用 SHOW COLUMNS，改用 Schema 缓存 或 静态变量
     */
    protected function fillAuditColumn(string $column): void
    {
		// 更安全的函数存在性检查
		$uid = (function_exists('getCurrentUser') && is_callable('getCurrentUser')) 
			? \getCurrentUser() 
			: null;
		if (!$uid) return;

        // 方案A：如果使用了 Schema 缓存 (Laravel默认支持)
        // if (Schema::hasColumn($this->getTable(), $column)) {
        //     $this->setAttribute($column, $uid);
        // }

        // 方案B：内存级缓存 (当前请求内有效，性能最高)
        if ($this->hasColumnCached($column)) {
            $this->setAttribute($column, $uid);
        }
    }

    /**
     * 检查字段是否存在
     */
	protected function hasColumnCached(string $column): bool
	{
		$table = $this->getTable();
		
		if (!isset(self::$tableColumns[$table])) {
			// 修改点：通过当前模型的连接对象获取 SchemaBuilder
			// 这样不依赖全局容器，非常稳定
			self::$tableColumns[$table] = $this->getConnection()
											   ->getSchemaBuilder()
											   ->getColumnListing($table);
		}

		return in_array($column, self::$tableColumns[$table]);
	}

    /**
     * 准备日期序列化格式 (API返回JSON时会自动调用)
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->setTimezone(config('app.time_zone', 'PRC'))->format('Y-m-d H:i:s');
    }

	/**
	 * 兼容 TP: 动态隐藏字段（语义优化，避免与原生 $hidden 冲突）
	 */
	public function dynamicHidden(array $fields): static
	{
		$this->makeHidden($fields);
		return $this;
	}

	/**
	 * 补充：动态显示字段（双向兼容，功能更完整）
	 */
	public function dynamicVisible(array $fields): static
	{
		$this->makeVisible($fields);
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
     * 获取模型的字段列表 兼容性不高
     *
     * @return array
     */
    public function getFields_2(): array
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
	 * 获取模型对应表的字段列表（复用静态缓存，性能最优）
	 *
	 * @return array
	 */
	public function getFields(): array
	{
		try {
			$table = $this->getTable();
			// 复用已缓存的字段列表，避免重复调用 Schema
			if (!isset(self::$tableColumns[$table])) {
				self::$tableColumns[$table] = $this->getConnection()
												   ->getSchemaBuilder()
												   ->getColumnListing($table);
			}
			return self::$tableColumns[$table];
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
    public static function generateSnowflakeID1(): int
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
	
	/**
	 * 生成雪花ID（公共静态方法，支持外部调用）
	 */
	public static function generateSnowflakeID(): string
	{
		// 懒加载雪花算法实例，全局只初始化一次
		if (self::$snowflake === null) {
			self::$snowflake = self::createSnowflakeInstance();
		}

		// 生成雪花ID并转为字符串（避免精度丢失）
		return (string) self::$snowflake->nextId();
	}

	/**
	 * 创建雪花算法实例（独立方法，便于扩展和自定义）
	 */
	private static function createSnowflakeInstance(): Snowflake
	{
		// 从Laravel配置文件读取worker_id和datacenter_id，避免硬编码
		// 建议在config/app.php中添加：'snowflake_worker_id' => 0, 'snowflake_datacenter_id' => 0
		$workerId = (int) config('app.snowflake_worker_id', 0);
		$dataCenterId = (int) config('app.snowflake_datacenter_id', 0);

		// 校验worker_id和datacenter_id范围（雪花算法标准：0-31）
		if ($workerId < 0 || $workerId > 31 || $dataCenterId < 0 || $dataCenterId > 31) {
			throw new \InvalidArgumentException("雪花算法WorkerId和DataCenterId必须在0-31之间");
		}

		return new Snowflake($workerId, $dataCenterId);
	}

	/**
	 * 重置雪花算法实例（可选，供子类/特殊场景自定义）
	 */
	public static function resetSnowflakeInstance(?Snowflake $snowflake = null): void
	{
		if ($snowflake === null) {
			self::$snowflake = self::createSnowflakeInstance();
		} else {
			self::$snowflake = $snowflake;
		}
	}
		
	/**
	 * 获取表字段的详细结构信息（基于 Schema，无原生 SQL 依赖）
	 *
	 * @return array 格式：[字段名 => [type: 字段类型, nullable: 是否允许为空, default: 默认值, ...]]
	 */
	public function getFieldDetails(): array
	{
		try {
			$table = $this->getTable();
			$connection = $this->getConnection();
			$schema = $connection->getSchemaBuilder();
			$columns = $schema->getColumns($table);
			
			// 1. 先获取当前表的所有主键字段（复合主键也支持）
			$primaryKeys = $this->getPrimaryKeys();
			#return $columns;
			$fieldDetails = [];
			foreach ($columns as $column) {
				$fieldName = $column['name'] ?? '';
				if (empty($fieldName)) {
					continue; // 过滤无效字段
				}
				$fieldDetails[$fieldName] = [
					'auto_increment' => $column['auto_increment'],
					'type_name'     => $column['type_name'] ?? 'unknown',
					'type'     		=> $column['type'] ?? 'unknown',
					'nullable' 		=> $column['nullable'] ?? null,
					'default'  		=> $column['default'] ?? null,
					'length'   		=> $column['length'] ?? null,
					'comment'  		=> $column['comment'] ?? null,
					'primary'  		=> in_array($fieldName, $primaryKeys), // 2. 通过主键列表判断，无兼容问题
				];
			}

			return $fieldDetails;
		} catch (\Exception $e) {
			#\Illuminate\Support\Facades\Log::error("获取表结构失败：{$e->getMessage()}");
			return [];
		}
	}

	/**
	 * 检查指定字段是否为目标类型（基于 Schema，兼容性强）
	 *
	 * @param string $column 字段名
	 * @param string|array $targetTypes 目标类型（如 'varchar'、['int', 'bigint']）
	 * @return bool
	 */
	public function isFieldType(string $column, string|array $targetTypes): bool
	{
		if (!is_array($targetTypes)) {
			$targetTypes = [$targetTypes];
		}

		$fieldDetails = $this->getFieldDetails();
		if (!isset($fieldDetails[$column])) {
			return false;
		}

		// 忽略大小写，提升兼容性
		$fieldType = strtolower($fieldDetails[$column]['type']);
		$targetTypes = array_map('strtolower', $targetTypes);

		return in_array($fieldType, $targetTypes);
	}
	
	/**
	 * 获取表的主键字段（基于 Schema） 获取表的主键字段（独立方法，供关联使用）
	 *
	 * @return array 主键字段列表（复合主键返回多个，单主键返回单个元素数组）
	 */
	public function getPrimaryKeys1(): array
	{
		$fieldDetails = $this->getFieldDetails();
		$primaryKeys = [];

		foreach ($fieldDetails as $fieldName => $details) {
			if ($details['primary']) {
				$primaryKeys[] = $fieldName;
			}
		}

		return $primaryKeys;
	}
	/**
	 * 获取表的主键字段（独立方法，供关联使用）
	 */
	public function getPrimaryKeys(): array
	{
		try {
			$table = $this->getTable();
			$connection = $this->getConnection();
			$schema = $connection->getSchemaBuilder();
			
			// 获取主键信息（兼容所有 Laravel 版本和数据库驱动）
			$indexes = $schema->getIndexes($table);
			$primaryKeys = [];

			foreach ($indexes as $index) {
				if ($index['primary']) {
					// 复合主键会返回多个字段，普通主键仅一个
					$primaryKeys = array_merge($primaryKeys, $index['columns']);
					break; // 主键索引唯一，找到后直接退出循环
				}
			}

			return $primaryKeys;
		} catch (\Exception $e) {
			\Illuminate\Support\Facades\Log::error("获取主键失败：{$e->getMessage()}");
			return [];
		}
	}
	
	
	
}