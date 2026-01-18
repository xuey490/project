<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-11
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as LaSoftDeletes;
use Illuminate\Support\Carbon;
use Framework\Utils\Snowflake;
use Framework\Basic\Traits\LaBelongsToTenant;
use Framework\Basic\Casts\TimestampCast;
#use Illuminate\Support\Facades\Cache;
use think\facade\Cache;
use Framework\Schema\SchemaRegistry;

class BaseLaORMModel extends Model
{
    use LaBelongsToTenant;

    // ================= 基础配置 =================

    public $incrementing = false;
    public $timestamps  = true;
    protected $keyType  = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    /**
     * 数据库存储统一使用 Unix 时间戳
     */
    protected $dateFormat = 'U';

    /**
     * 基础日期字段（父类统一定义）
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'create_time',
        'update_time',
        'delete_time',
    ];

    /**
     * 前端精度保护
     */
    protected $casts = [
        'id' => 'string',
    ];

    /**
     * 雪花算法实例
     */
    private static ?Snowflake $snowflake = null;

    /**
     * 主键生成策略
     */
    protected string $pkGenerateType = 'snowflake';

    /**
     * 表字段缓存
     */
    private static array $tableColumns = [];
	
	private const MAX_SCHEMA_CACHE = 128;
	
	private static bool $schemaFrozen = false;



    // ================= 初始化 =================

    protected function initializeBaseLaORMModel(): void
    {
        /**
         * 自动为所有日期字段注册 TimestampCast
         * 不侵入 setAttribute / getAttribute
         */
        foreach ($this->getDates() as $field) {
            $this->casts[$field] = TimestampCast::class;
        }
    }

    // ================= Model Boot =================

    protected static function boot()
    {
        parent::boot();

        // 创建
        static::creating(function ($model) {
            // 1. 雪花ID
            if ($model->pkGenerateType === 'snowflake') {
                if (empty($model->{$model->getKeyName()})) {
                    $model->{$model->getKeyName()} = self::generateSnowflakeID();
                }
            }

            $now = Carbon::now()->timestamp;

            // 2. 自动时间字段
            foreach (['created_at', 'create_time'] as $f) {
                if (empty($model->{$f})) {
                    $model->{$f} = $now;
                }
            }

            foreach (['updated_at', 'update_time'] as $f) {
                if (empty($model->{$f})) {
                    $model->{$f} = $now;
                }
            }

            // 3. 创建人
            $model->fillAuditColumn('created_by');
        });

        // 更新
        static::updating(function ($model) {
            $now = Carbon::now()->timestamp;
            $model->updated_at  = $now;
            $model->update_time = $now;

            $model->fillAuditColumn('updated_by');
        });

        // 删除（软删时间同步）
        static::deleting(function ($model) {
            if ($model::isSoftDeleteEnabled()) {
                $now = Carbon::now()->timestamp;
                $model->deleted_at  = $now;
                $model->delete_time = $now;
            }
        });

        static::deleted(function ($model) {
            self::onAfterDelete($model);
        });
    }

    // ================= 日期体系（核心优化） =================

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
            parent::getDates(),
            $this->dates ?? [],
            $this->extraDates()
        )));
    }

    /**
     * 统一序列化格式
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return Carbon::instance($date)->format('Y-m-d H:i:s');
    }

    // ================= 审计字段 =================

    /**
     * FPM 检查字段是否存在
     */
    protected function hasColumnCached(string $column): bool
    {
        $table = $this->getTable();
		
		// 使用 getTableColumnsCached 取代 getFields
		return in_array(
			$column,
			$this->getTableColumnsCached(),
			true
		);

		/*
        if (!isset(self::$tableColumns[$table])) {
            self::$tableColumns[$table] = $this->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($table);
        }

        return in_array($column, self::$tableColumns[$table], true);
		*/
    }
	
	/*
	* 返回字段缓存
	*/
	protected function getTableColumnsCached(): array
	{
		$connection = $this->getConnection();
		$driver     = $connection->getDriverName();
		
		$table = $this->getTable();
		$cacheKey = 'schema:columns:' . $driver . ':' . $table;

		return Cache::remember($cacheKey, function () use ($table) {
			return $this->getConnection()
				->getSchemaBuilder()
				->getColumnListing($table);
		});
	}	

    /* ===== 字段相关 ===== */

    public function getFields(): array
    {
		if (defined('WORKERMAN_ENV')) {
			return SchemaRegistry::getColumns($this->getTable());
		}else{
			/**
			 * 对外 API：获取模型字段（带缓存、受控）
			 */			
			return $this->getTableColumns();
		}
    }

    protected function hasColumn(string $column): bool
    {
        return SchemaRegistry::hasColumn($this->getTable(), $column);
    }

    /**
     * 【性能优化核心】填充审计字段
     * 避免使用 SHOW COLUMNS，改用 Schema 缓存 或 静态变量
     */
    protected function fillAuditColumn(string $column): void
    {
        $uid = (function_exists('getCurrentUser') && is_callable('getCurrentUser'))
            ? \getCurrentUser()
            : null;

        if (!$uid) return;	
		
		if (defined('WORKERMAN_ENV')) {
			if (!in_array($column, SchemaRegistry::getAuditColumns($this->getTable()), true)) {
				return;
			}
			if ($uid) {
				$this->setAttribute($column, $uid);
			}
		}else{
			$this->hasColumnCached($column);
			if ($this->hasColumnCached($column)) {
				$this->setAttribute($column, $uid);
			}
			
		}
    }
	/* ===== 字段相关 ===== */


	/**
	 * 缓存治理层
	 */
	protected function getTableColumns(): array
	{
		$table = $this->getTable();

		if (isset(self::$tableColumns[$table])) {
			return self::$tableColumns[$table];
		}

		if (self::$schemaFrozen) {
			throw new \RuntimeException(
				"Schema is frozen. Table columns not preloaded: {$table}"
			);
		}

		$columns = $this->loadTableColumnsFromDb($table);

		return $this->rememberTableColumns($table, $columns);
	}

	/**
	 * 真正的 DB I/O（禁止在业务层直接调用）
	 */
	protected function loadTableColumnsFromDb(string $table): array
	{
		$connection = $this->getConnection();
		$driver     = $connection->getDriverName();

		if ($driver === 'mysql') {
			$prefix = $connection->getTablePrefix();
			$rows = $connection->select(
				'SHOW COLUMNS FROM `' . $prefix . $table . '`'
			);

			return array_map(
				static fn($row) => $row->Field,
				$rows
			);
		}

		return $connection
			->getSchemaBuilder()
			->getColumnListing($table);
	}
	
	protected function rememberTableColumns(string $table, array $columns): array
	{
		if (count(self::$tableColumns) >= self::MAX_SCHEMA_CACHE) {
			array_shift(self::$tableColumns); // FIFO
		}

		return self::$tableColumns[$table] = $columns;
	}
	
	public static function freezeSchema(): void
	{
		self::$schemaFrozen = true;
	}

    // ================= 动态字段控制 =================
	
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

    // ================= TP 风格兼容 =================

    public function getData(string $field): mixed
    {
        $value = $this->getAttribute($field);

        if ($value instanceof Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    public function set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }
			

    // ================= 表信息 =================
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
	 * 获取模型对应表的字段列表（复用静态缓存，性能最优）
	 *
	 * @return array
	 */
	public function getFields2(): array
	{
		try {
			$table = $this->getTable();
			// 复用已缓存的字段列表，避免重复调用 Schema
			if (!isset(self::$tableColumns[$table])) {
				self::$tableColumns[$table] = $this->getConnection()->getSchemaBuilder()->getColumnListing($table);
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
     * 是否开启软删
     */
    public static function isSoftDeleteEnabled(): bool
    {
        return in_array(LaSoftDeletes::class, class_uses(static::class));
    }

    // ================= 删除后处理 =================

    public static function onAfterDelete(Model $model)
    {
        if ($model::isSoftDeleteEnabled()) {
            return;
        }

        try {
            // 回收站逻辑预留
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                "Recycle bin error: " . $e->getMessage()
            );
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
