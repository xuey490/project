<?php

declare(strict_types=1);

namespace Framework\Repository;

use Framework\Database\DatabaseFactory;
use RuntimeException;
use think\facade\Db as ThinkDb;
use Illuminate\Database\Capsule\Manager as IlluminateDb;
use Framework\DI\Injectable;
use Framework\Core\App;
use Framework\Tenant\Tenant;

/**
 * Class BaseRepository
 * 核心数据库操作基类（兼容 Illuminate ORM & ThinkPHP 8 ORM）
 * 提供通用的CRUD、统计、事务等操作，屏蔽底层ORM差异，子类直接继承使用
 * @package Framework\Repository
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * 模型类全名（子类必须覆盖定义，如：\App\Models\User::class）
     * @var string
     */
    protected string $modelClass = '';

    /**
     * 是否启用 Illuminate Eloquent ORM（由 DatabaseFactory 初始化）
     * @var bool
     */
    protected bool $isEloquent = false;


    /**
     * 当前租户ID（从容器/上下文自动获取，无需手动赋值）
     * @var string|int|null
     */
    protected mixed $tenantId = null;

    /**
     * 多租户筛选开关（默认开启，自动拼接 tenant_id 条件）
     * 超管可临时关闭，取消数据隔离
     * @var bool
     */
    protected bool $tenantFilterEnabled = true;

    /**
     * 手动覆盖租户条件标识（避免自动筛选与手动条件冲突） （如需多租户功能，可在子类覆盖，默认：tenant_id）
     * 若查询条件中已包含 tenant_id，自动跳过自动筛选
     * @var string
     */
    protected string $tenantField = 'tenant_id';

    /**
     * 临时关闭多租户筛选的标记（用于超管全局临时操作）
     * 静态属性：支持跨仓库实例共享超管状态
     * @var bool
     */
    protected static bool $superAdminTempDisable = false;

    // 引入依赖注入能力
    use Injectable;

    /**
     * BaseRepository 构造函数
     * 初始化ORM类型、模型校验、依赖注入、租户信息
     * @param DatabaseFactory $factory 数据库工厂（提供ORM类型判断和实例创建）
     * @throws RuntimeException 当未定义 $modelClass 时抛出异常
     */
    public function __construct(protected DatabaseFactory $factory)
    {
        // 执行依赖注入（Injectable trait 提供）
        $this->inject();

        // 校验模型类是否定义
        if (empty($this->modelClass)) {
            throw new RuntimeException('Repository 必须定义 $modelClass 属性（指定对应模型类全名）');
        }

        // 初始化ORM类型标识
        $this->isEloquent = $this->factory->isEloquent();

        // 初始化租户ID（从应用容器获取，可根据项目实际情况调整获取方式）
		//如果模型继承了两个基类，下面可以打开
        $this->initTenantId();

        // 子类初始化钩子（子类可覆盖实现自定义初始化逻辑）
        $this->initialize();
    }

    /**
     * 子类初始化钩子方法
     * 子类可覆盖此方法，实现自定义初始化逻辑（如：默认查询条件、字段过滤等）
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * 初始化租户ID
     * 从应用容器获取租户实例，提取租户ID（多租户场景使用，非多租户场景可忽略）
     * @return void
     */
    protected function initTenantId(): void
    {
        try {
            // 尝试从容器获取租户实例，若不存在则忽略（非多租户场景不报错）
            $this->tenantId = App()->make(Tenant::class)->getId() ?? null;
        } catch (\Exception $e) {
            $this->tenantId = null;
        }
    }
	
	
	/**
	 * 应用多租户筛选条件（优化版：避免冲突，支持开关控制）
	 * 自动判断是否需要拼接 tenant_id 条件，解决与模型层的双重筛选冲突
	 * @param mixed $query 查询构造器
	 * @param array $criteria 查询条件
	 * @return void
	 */
	protected function applyTenantFilter(mixed $query, array $criteria): void
	{

		// 3大不筛选场景：1.开关关闭 2.超管临时关闭 3.手动已传 tenant_id 条件
		if (
			!$this->tenantFilterEnabled // 实例级开关关闭
			|| self::$superAdminTempDisable // 超管全局临时关闭
			|| isset($criteria[$this->tenantField]) // 查询条件中已手动携带 tenant_id，避免重复
		) {
			return; // 直接返回，不拼接 tenant_id 条件
		}

		// 获取租户ID（原有逻辑）
		$tenant = App()->make(Tenant::class);
		$tenantId = $tenant->getId();

		// 租户ID不存在时，不拼接条件
		if (is_null($tenantId)) {
			return;
		}
		
		// 拼接租户筛选条件（仅执行一次，无重复冲突）
		$query->where($this->tenantField, $this->tenantId);
	}

    /**
     * 判断是否配置了有效的模型类
     * 检查 $modelClass 对应的类是否存在
     * @return bool 存在返回true，否则返回false
     */
    public function isModelClass(): bool
    {
        return class_exists($this->modelClass);
    }

    /**
     * 获取模型实例（容器实例化，保证单例和依赖注入一致性）
     * 等价于 new $this->modelClass()，但通过容器实例化更灵活
     * @return mixed 模型实例（Illuminate\Model 或 think\Model）
     * @throws RuntimeException 当模型类不存在时抛出异常
     */
    protected function getModel(): mixed
    {
        // 再次校验模型类是否存在，避免非法调用
        if (!class_exists($this->modelClass)) {
            return ($this->factory)->make($this->modelClass);
			//throw new RuntimeException("模型类 {$this->modelClass} 不存在，请检查类名配置");
        }
		// 通过应用容器实例化模型，支持依赖注入和单例管理
		return App()->make($this->modelClass);
    }

    /**
     * 获取查询构造器（统一模型实例转构造器，屏蔽ORM差异）
     * 等价于 newQuery()，是核心查询入口
     * @return mixed 查询构造器（EloquentBuilder 或 ThinkQuery）
     */
    protected function newQuery(): mixed
    {
        // 先获取模型实例，再转为查询构造器，统一逻辑
        return $this->getBuilder($this->getModel());
    }

    /**
     * 语法糖：$repo() 直接获取底层查询构造器
     * 简化调用：$userRepo() 等价于 $userRepo->newQuery()
     * @param string|null $modelClass 自定义模型类（可选，默认使用当前 $modelClass）
     * @return mixed 查询构造器
     */
    public function __invoke(?string $modelClass = null): mixed
    {
        return $this->factory->make($modelClass ?? $this->modelClass);
    }

    /**
     * 统一处理关联预加载（Eager Loading）
     * 兼容双ORM的 with 方法，纯表名模式下自动忽略（无关联查询能力）
     * @param mixed $query 查询构造器/模型实例
     * @param array $with 关联关系数组（如：['orders', 'profile']）
     * @return mixed 处理后的查询构造器
     */
    protected function applyWith(mixed $query, array $with = []): mixed
    {
        // 无关联关系，直接返回原查询
        if (empty($with)) {
            return $query;
        }

        // 纯表名模式（非模型类），不支持关联查询，直接返回原查询
        if (!$this->isModelClass()) {
            return $query;
        }

        // 双ORM均支持 with 方法，存在则调用
        if (method_exists($query, 'with')) {
            return $query->with($with);
        }

        // 不支持 with 方法时，返回原查询
        return $query;
    }

    /**
     * 统一获取查询构造器（模型实例转构造器，屏蔽双ORM差异）
     * 解决模型实例直接调用查询方法导致的返回值不一致问题
     * @param mixed $query 模型实例/查询构造器
     * @return mixed 标准化的查询构造器（EloquentBuilder 或 ThinkQuery）
     */
    protected function getBuilder(mixed $query): mixed
    {
        // 非模型类场景，直接返回原查询（表名模式）
        if (!$this->isModelClass()) {
            return $query;
        }

        // Illuminate ORM：模型实例转为 EloquentBuilder
        if ($this->isEloquent) {
            if ($query instanceof \Illuminate\Database\Eloquent\Model) {
                return $query->newQuery();
            }
            return $query;
        }

        // ThinkPHP ORM：模型实例转为 ThinkQuery
        if ($query instanceof \think\Model) {
            return $query->db();
        }
        return $query;
    }

    /**
     * 统一获取主键名（屏蔽双ORM主键获取差异）
     * 模型模式自动获取自定义主键，表名模式默认返回 'id'
     * @return string 主键字段名
     */
    protected function getPrimaryKey(): string
    {
        // 表名模式，默认主键为 'id'
        if (!$this->isModelClass()) {
            return 'id';
        }

        // 获取模型实例
        $model = $this->getModel();

        // Illuminate ORM：使用 getKeyName() 获取主键
        if ($this->isEloquent) {
            return $model->getKeyName();
        }

        // ThinkPHP ORM：使用 getPk() 获取主键
        return $model->getPk();
    }

    // --- 通用查询方法 ---

    /**
     * 根据主键ID查询单条记录
     * 兼容模型模式和表名模式，支持关联预加载
     * @param int|string $id 主键ID值
     * @param array $with 关联关系数组（可选）
     * @return mixed 单条记录（模型实例/数组/NULL）
     */
	public function findById(int|string $id, array $with = []): mixed
	{
		// 1. 获取查询构造器并应用关联预加载
		$query = $this->newQuery();
		$query = $this->applyWith($query, $with);

		// 2. 模型模式：直接使用 find 方法（双ORM兼容）
		if ($this->isModelClass()) {
			// 两个框架模型都支持 find($id)
			return $query->find($id); 
		}

		$primaryKey = $this->getPrimaryKey();
		// Eloquent Builder 没有 find() 方法，只有 find($id) 是 Model 的 helper
		if ($this->isEloquent) {
			 return $query->where($primaryKey, $id)->first();
		}else{
			// 表名模式
			return $query->where($primaryKey, $id)->find(); // TP find 返回 array|null, Eloquent first 返回 object|null
		}
	}

    /**
     * 根据主键ID查询批量记录
     * 兼容模型模式和表名模式，支持关联预加载
     * @param array $id 主键ID值
     * @param array $with 关联关系数组（可选）
     * @return mixed N条记录（模型实例/数组/NULL）
     */
	public function findByArrayId(array $id, array $with = []): mixed
	{
		// 1. 获取查询构造器并应用关联预加载
		$query = $this->newQuery();
		$query = $this->applyWith($query, $with);

		$primaryKey = $this->getPrimaryKey();
		if ($this->isModelClass()) {
			// Eloquent Builder 没有 find() 方法，只有 find($id) 是 Model 的 helper
			if ($this->isEloquent) {
				 return $query->find($id);
			}else{
				 return $query->whereIn($primaryKey, $id)->select(); 
			}
			return $result;
		}else{
			if ($this->isEloquent) {
				 return $query->whereIn($primaryKey, $id)->get();
			}else{
				 return $query->whereIn($primaryKey, $id)->select(); 
			}
			return $result;
		}
	}
	
    /**
     * 根据条件查询单条记录
     * 支持复杂查询条件，兼容关联预加载
     * @param array $criteria 查询条件（如：['status' => 1, 'age' => ['>', 18]]）
     * @param array $with 关联关系数组（可选）
     * @return mixed 单条记录（模型实例/数组/NULL）
     */
    public function findOneBy(array $criteria, array $with = []): mixed
    {
        // 1. 构建查询条件并应用关联预加载
        $query = $this->buildQuery($this->newQuery(), $criteria);
        $query = $this->applyWith($query, $with);

        // 2. 双ORM差异处理：获取单条记录
        if ($this->isEloquent) {
            // Illuminate ORM：使用 first() 方法
            return $query->first();
        }
        // ThinkPHP ORM：使用 find() 方法，无数据返回NULL
        return $query->find() ?: null;
    }

    /**
     * 根据条件查询多条记录
     * 支持排序、分页（限制条数）、关联预加载
     * @param array $criteria 查询条件（可选）
     * @param array $orderBy 排序条件（如：['id' => 'desc', 'age' => 'asc']）
     * @param int|null $limit 返回条数限制（可选，NULL表示无限制）
     * @param array $with 关联关系数组（可选）
     * @return mixed 多条记录（集合/数组）
     */
    public function findAll(array $criteria = [], array $orderBy = [], ?int $limit = null, array $with = []): mixed
    {
        // 1. 构建查询条件和排序，应用关联预加载
        $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
        $query = $this->applyWith($query, $with);

        // 2. 设置返回条数限制
        if ($limit) {
            $query->limit($limit);
        }

        // 3. 双ORM差异处理：获取多条记录
        if ($this->isEloquent) {
            // Illuminate ORM：使用 get() 方法返回 Collection
            return $query->get();
        }
        // ThinkPHP ORM：使用 select() 方法返回数组
        return $query->select();
    }

    /**
     * 分页查询
     * 兼容双ORM的分页方法，返回标准化分页结果
     * @param array $criteria 查询条件（可选）
     * @param int $perPage 每页条数（默认15条）
     * @param array $orderBy 排序条件（可选）
     * @param array $with 关联关系数组（可选）
     * @return mixed 分页结果（Illuminate\Pagination\LengthAwarePaginator 或 think\Paginator）
     */
    public function paginate(array $criteria = [], int $perPage = 15, array $orderBy = [], array $with = []): mixed
    {
        // 1. 构建查询条件和排序，应用关联预加载
        $query = $this->buildQuery($this->newQuery(), $criteria, $orderBy);
        $query = $this->applyWith($query, $with);

        // 2. 双ORM均支持 paginate 方法，直接调用
        return $query->paginate($perPage);
    }

    // --- 字段增减方法 ---

    /**
     * 字段自增操作（通用兼容双ORM）
     * 支持同时更新其他额外字段，返回操作是否成功
     * @param int|string $id 主键ID
     * @param string $field 要自增的字段名
     * @param int $amount 自增数量（默认1）
     * @param array $extra 同时更新的其他字段（可选，如：['update_time' => time()]）
     * @return bool 操作成功返回true，失败返回false
     */
    public function increment(int|string $id, string $field, int $amount = 1, array $extra = []): bool
    {
        // 1. 拼接主键查询条件，避免全表更新
        $primaryKey = $this->getPrimaryKey();
        $query = $this->newQuery()->where($primaryKey, $id);

        // 2. 双ORM差异处理
        if ($this->isEloquent) {
            // Illuminate ORM：increment 方法直接执行，返回受影响行数，转为布尔值
            return (bool) $query->increment($field, $amount, $extra);
        } else {
            // ThinkPHP ORM：inc 方法标记自增，需调用 update 执行，返回布尔值
            return (bool) $query->inc($field, $amount)->update($extra);
        }
    }

    /**
     * 字段自减操作（通用兼容双ORM）
     * 复用 increment 方法，通过传入负数实现自减，简化维护
     * @param int|string $id 主键ID
     * @param string $field 要自减的字段名
     * @param int $amount 自减数量（默认1，自动转为正数）
     * @param array $extra 同时更新的其他字段（可选）
     * @return bool 操作成功返回true，失败返回false
     */
    public function decrement(int|string $id, string $field, int $amount = 1, array $extra = []): bool
    {
        // 传入负数给 increment 方法，实现自减功能，统一逻辑
        return $this->increment($id, $field, -abs($amount), $extra);
    }

    // --- 写入操作（新增/更新/保存） ---

    /**
     * 新增记录
     * 兼容模型模式（支持批量赋值）和表名模式（支持自增ID/雪花ID）
     * @param array $data 新增数据（键值对数组）
     * @return mixed 新增后的记录（模型实例/数组/NULL）
     */
    public function create(array $data): mixed
    {
        // 1. 模型模式：优先使用模型的 create 方法，支持批量赋值和雪花ID
        if ($this->isModelClass()) {
            return forward_static_call([$this->modelClass, 'create'], $data);
        }

        // 2. 表名模式：区分自增ID和雪花ID
        $primaryKey = $this->getPrimaryKey();
        // 雪花ID场景：已传入主键，直接插入后查询
        if (isset($data[$primaryKey]) && !empty($data[$primaryKey])) {
            $insertResult = $this->isEloquent
                ? $this->newQuery()->insert($data)
                : $this->newQuery()->insert($data);
            // 插入成功则返回该记录，失败返回NULL
            return $insertResult ? $this->findById($data[$primaryKey]) : null;
        }

        // 自增ID场景：插入获取ID后查询
        if ($this->isEloquent) {
            $id = $this->newQuery()->insertGetId($data);
        } else {
            $id = $this->newQuery()->insert($data, true);
        }

        return $this->findById($id);
    }

    /**
     * 通用保存方法（支持新增和更新，兼容批量赋值）
     * 有主键则更新，无主键则新增，统一保存逻辑
     * @param array $data 保存数据（包含主键则更新，不包含则新增）
     * @return mixed 保存后的记录（模型实例/布尔值/数组）
     */
    public function save(array $data)
    {
        // 1. 表名模式：兼容无模型场景
        if (!$this->isModelClass()) {
            $primaryKey = $this->getPrimaryKey();
            // 有主键则更新，无主键则新增
            if (isset($data[$primaryKey]) && !empty($data[$primaryKey])) {
                $updateCount = $this->updateBy([$primaryKey => $data[$primaryKey]], $data);
                return $updateCount > 0;
            }
            return $this->create($data);
        }

        // 2. 模型模式：兼容双ORM，自动获取主键名
        $model = $this->getModel();
        $primaryKey = $this->isEloquent ? $model->getKeyName() : $model->getPk();

		// 无主键：新增记录（使用模型 create 方法，支持批量赋值）
		if (!isset($data[$primaryKey]) || empty($data[$primaryKey])) {
			//return forward_static_call([$this->modelClass, 'create'], $data);
			if ($this->isEloquent) {
				return $this->modelClass::create($data);
			}
			return $this->modelClass::create($data); // ThinkPHP 8 模型也支持 create
		}

		// 更新
		$id = $data[$primaryKey];
		// 兼容查找逻辑
		$instance = $this->findById($id);
		
		if (!$instance) {
			throw new RuntimeException("Record with ID {$id} not found.");
		}

		// Illuminate ORM：使用 fill 方法，遵循 $fillable 配置
		if ($this->isEloquent) {
			$instance->fill($data);
		} else {
			// ThinkPHP 模型赋值
			$instance->save($data); // ThinkPHP save可以直接传数据进行更新
			return $instance;
		}

		$instance->save();
		return $instance;
    }

    /**
     * 根据主键ID更新记录
     * 支持模型实例更新（批量赋值）和表名模式更新
     * @param int|string $id 主键ID
     * @param array $data 更新数据（键值对数组）
     * @return bool 更新成功返回true，失败返回false
     */
    public function update(int|string $id, array $data): bool
    {
        // 1. 先查询记录是否存在
        $item = $this->findById($id);
        if (!$item) {
            return false;
        }

        // 2. 非对象类型（表名模式查询结果）：直接执行更新
        if (!is_object($item)) {
            $primaryKey = $this->getPrimaryKey();
            $updateCount = $this->newQuery()->where($primaryKey, $id)->update($data);
            return $updateCount > 0;
        }

        // 3. 对象类型（模型实例）：统一批量赋值后保存
        if ($this->isEloquent) {
            // Illuminate ORM：fill 方法赋值
            $item->fill($data);
        } else {
            // ThinkPHP ORM：data 方法赋值
            $item->data($data);
        }

        // 保存并判断是否成功
        $saveResult = $item->save();
        return $saveResult !== false;
    }

    /**
     * 根据条件批量更新记录
     * 返回受影响的记录条数
     * @param array $criteria 更新条件（如：['status' => 0]）
     * @param array $data 更新数据（键值对数组）
     * @return int 受影响的记录条数
     */
    public function updateBy(array $criteria, array $data): int
    {
        // 构建查询条件并执行更新
        $query = $this->buildQuery($this->newQuery(), $criteria);
        return (int) $query->update($data);
    }

    // --- 删除操作 ---

    /**
     * 根据主键ID删除记录
     * 兼容模型模式（destroy 方法）和表名模式（直接删除）
     * @param int|string $id 主键ID
     * @return bool 删除成功返回true，失败返回false
     */
    public function delete(int|string $id): bool
    {
        // 1. 模型模式：使用 destroy 方法（双ORM兼容）
        if ($this->isModelClass()) {
            return (bool) forward_static_call([$this->modelClass, 'destroy'], $id);
        }

        // 2. 表名模式：拼接主键条件删除
        $primaryKey = $this->getPrimaryKey();
        return (bool) $this->newQuery()->where($primaryKey, $id)->delete();
    }

    /**
     * 根据条件批量删除记录
     * 返回受影响的记录条数
     * @param array $criteria 删除条件（如：['status' => -1]）
     * @return int 受影响的记录条数
     */
    public function deleteBy(array $criteria): int
    {
        // 构建查询条件并执行删除
        $query = $this->buildQuery($this->newQuery(), $criteria);
        return (int) $query->delete();
    }

    // --- 统计与原生SQL操作 ---

    /**
     * 聚合查询（count/sum/max/min/avg）
     * 统一返回数值类型，屏蔽双ORM返回值差异
     * @param string $type 聚合类型（count/sum/max/min/avg）
     * @param array $criteria 查询条件（可选）
     * @param string $field 聚合字段（默认*，count时有效）
     * @return int|float 聚合结果（数值类型）
     */
    public function aggregate(string $type, array $criteria = [], string $field = '*'): int|float
    {
        // 构建查询条件
        $query = $this->buildQuery($this->newQuery(), $criteria);

        // 执行对应聚合操作
        $result = match (strtolower($type)) {
            'count' => $query->count($field),
            'sum'   => $query->sum($field),
            'max'   => $query->max($field),
            'min'   => $query->min($field),
            'avg'   => $query->avg($field),
            default => 0,
        };

        // 统一转为数值类型，避免字符串返回导致的类型错误
        return is_numeric($result) ? (float)$result : 0;
    }

    /**
     * 事务操作（兼容双ORM）
     * 传入闭包执行事务逻辑，自动处理事务提交和回滚
     * @param \Closure $callback 事务逻辑闭包
     * @return mixed 闭包执行结果
     * @throws \Exception 闭包内抛出的异常会触发事务回滚
     */
    public function transaction(\Closure $callback): mixed
    {
        if ($this->isEloquent) {
            // Illuminate ORM：使用 Capsule Manager 的 transaction 方法
            return IlluminateDb::transaction($callback);
        }
        // ThinkPHP ORM：使用 Db 门面的 transaction 方法
        return ThinkDb::transaction($callback);
    }

    /**
     * 执行原生查询SQL
     * 返回查询结果数组，屏蔽双ORM返回值差异（Illuminate返回对象数组，转为关联数组）
     * @param string $sql 原生SQL语句
     * @param array $bindings SQL绑定参数（可选，防止SQL注入）
     * @return array 查询结果数组
     */
    public function query(string $sql, array $bindings = []): array
    {
        if ($this->isEloquent) {
            // Illuminate ORM：执行查询并转为关联数组
            $result = IlluminateDb::select($sql, $bindings);
            return array_map(fn($item) => (array) $item, $result);
        }
        // ThinkPHP ORM：直接返回关联数组
        return ThinkDb::query($sql, $bindings);
    }

    /**
     * 执行原生执行SQL（新增/更新/删除等）
     * 返回受影响的记录条数
     * @param string $sql 原生SQL语句
     * @param array $bindings SQL绑定参数（可选，防止SQL注入）
     * @return int 受影响的记录条数
     */
    public function execute(string $sql, array $bindings = []): int
    {
        if ($this->isEloquent) {
            // Illuminate ORM：执行影响行语句并返回条数
            return IlluminateDb::affectingStatement($sql, $bindings);
        }
        // ThinkPHP ORM：执行并返回受影响条数（转为int类型）
        return (int) ThinkDb::execute($sql, $bindings);
    }

    // --- 核心查询条件构建器 ---

    /**
     * 构建查询条件（核心DSL解析，屏蔽双ORM差异）
     * 支持复杂条件：WHERE/JOIN/GROUP BY/HAVING/ORDER BY 等
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件（DSL数组）
     * @param array $orderBy 排序条件（可选）
     * @return mixed 构建后的查询构造器
     */
    protected function buildQuery(mixed $query, array $criteria, array $orderBy = []): mixed
    {
        // 1. 统一转为查询构造器
        $query = $this->getBuilder($query);

        // 2. 多租户自动筛选（非多租户场景自动忽略）
        $this->applyTenantFilter($query, $criteria);

        // 3. SELECT 指定字段
        if (!empty($criteria['select'])) {
            $query->select($criteria['select']);
            unset($criteria['select']);
        }

        // 4. DISTINCT 去重
        if (!empty($criteria['distinct'])) {
            $query->distinct();
            unset($criteria['distinct']);
        }

        // 5. LOCK 悲观锁（for update）
        if (!empty($criteria['lock'])) {
            if ($this->isEloquent) {
                $query->lockForUpdate();
            } else {
                $query->lock(true);
            }
            unset($criteria['lock']);
        }

        // 6. JOIN 关联查询（支持 join/leftJoin/rightJoin）
        $this->applyJoins($query, $criteria);

        // 7. WHERE NULL / NOT NULL
        $this->applyWhereNull($query, $criteria);

        // 8. WHERE IN / NOT IN（显式Key方式）
        $this->applyWhereIn($query, $criteria);

        // 9. GROUP BY & HAVING
        $this->applyGroupByAndHaving($query, $criteria);

        // 10. OR 分组查询（WHERE (A OR B OR C)）
        $this->applyOrGroup($query, $criteria);

        // 11. 基础 WHERE 条件（包含 OR/AND/RAW/GROUP）
        $this->applyBasicWhere($query, $criteria);

        // 12. ORDER BY 排序
        $this->applyOrderBy($query, $orderBy);

        return $query;
    }

    /**
     * 应用多租户筛选条件
     * 自动拼接租户字段条件，避免手动在每个查询中添加
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyTenantFilter1(mixed $query, array $criteria): void
    {
        // 租户ID不存在、租户字段未配置、已手动传入租户条件，均不自动拼接
        if (!$this->tenantId || !$this->tenantField || isset($criteria[$this->tenantField])) {
            return;
        }

        // 自动拼接租户筛选条件
        $query->where($this->tenantField, $this->tenantId);
    }

    /**
     * 应用JOIN关联查询
     * 支持 join/leftJoin/rightJoin，兼容双ORM的JOIN语法差异
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyJoins(mixed $query, array &$criteria): void
    {
        // 遍历支持的JOIN类型
        foreach (['join', 'leftJoin', 'rightJoin'] as $joinType) {
            if (empty($criteria[$joinType]) || !is_array($criteria[$joinType])) {
                continue;
            }

            // 遍历每个JOIN条件
            foreach ($criteria[$joinType] as $join) {
                $table = $join[0] ?? null;
                $field1 = $join[1] ?? null;
                $operator = $join[2] ?? '=';
                $field2 = $join[3] ?? null;

                // 必要参数校验
                if (!$table || !$field1) {
                    continue;
                }

                // 自动补全默认操作符（=）
                if ($field2 === null && isset($join[2])) {
                    $field2 = $join[2];
                    $operator = '=';
                }

                // 双ORM JOIN语法兼容
                if (!$this->isEloquent) {
                    // ThinkPHP ORM：join('table', 'a=b')
                    $query->$joinType($table, "{$field1} {$operator} {$field2}");
                } else {
                    // Illuminate ORM：join('table', 'a', '=', 'b')
                    $query->$joinType($table, $field1, $operator, $field2);
                }
            }

            // 移除已处理的JOIN条件
            unset($criteria[$joinType]);
        }
    }

    /**
     * 应用 WHERE NULL / NOT NULL 条件
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyWhereNull(mixed $query, array &$criteria): void
    {
        // WHERE NULL
        if (!empty($criteria['whereNull'])) {
            foreach ((array)$criteria['whereNull'] as $field) {
                $query->whereNull($field);
            }
            unset($criteria['whereNull']);
        }

        // WHERE NOT NULL
        if (!empty($criteria['whereNotNull'])) {
            foreach ((array)$criteria['whereNotNull'] as $field) {
                $query->whereNotNull($field);
            }
            unset($criteria['whereNotNull']);
        }
    }

    /**
     * 应用 WHERE IN / NOT IN 条件（显式Key方式）
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyWhereIn(mixed $query, array &$criteria): void
    {
        // WHERE IN
        if (!empty($criteria['whereIn'])) {
            foreach ($criteria['whereIn'] as $field => $values) {
                $query->whereIn($field, $values);
            }
            unset($criteria['whereIn']);
        }

        // WHERE NOT IN
        if (!empty($criteria['whereNotIn'])) {
            foreach ($criteria['whereNotIn'] as $field => $values) {
                $query->whereNotIn($field, $values);
            }
            unset($criteria['whereNotIn']);
        }
    }

    /**
     * 应用 GROUP BY & HAVING 条件
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyGroupByAndHaving(mixed $query, array &$criteria): void
    {
        // GROUP BY
        if (!empty($criteria['groupBy'])) {
            $groupBy = (array) $criteria['groupBy'];
            // 双ORM均支持变长参数或数组传入
            $query->groupBy(...$groupBy);
            unset($criteria['groupBy']);
        }

        // HAVING
        if (!empty($criteria['having']) && is_array($criteria['having'])) {
            foreach ($criteria['having'] as $cond) {
                if (count($cond) === 3) {
                    $query->having($cond[0], $cond[1], $cond[2]);
                } elseif (count($cond) === 2) {
                    $query->having($cond[0], '=', $cond[1]);
                }
            }
            unset($criteria['having']);
        }

        // HAVING RAW
        if (!empty($criteria['havingRaw'])) {
            $query->havingRaw($criteria['havingRaw']);
            unset($criteria['havingRaw']);
        }
    }

    /**
     * 应用 OR 分组查询（WHERE (A OR B OR C)）
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyOrGroup(mixed $query, array &$criteria): void
    {
        if (empty($criteria['or_group']) || !is_array($criteria['or_group'])) {
            return;
        }

        $orGroup = $criteria['or_group'];
        // 构建分组OR条件
        $query->where(function ($subQuery) use ($orGroup) {
            $this->buildQuery($subQuery, $orGroup);
        }, null, null, 'or');

        // 移除已处理的or_group条件
        unset($criteria['or_group']);
    }

    /**
     * 应用基础 WHERE 条件（包含 OR/AND/RAW/GROUP）
     * @param mixed $query 查询构造器
     * @param array $criteria 查询条件
     * @return void
     */
    protected function applyBasicWhere(mixed $query, array &$criteria): void
    {
        foreach ($criteria as $field => $value) {
            // 忽略分页相关参数（由上层方法处理）
            if (in_array($field, ['page', 'limit', 'per_page'])) {
                continue;
            }

            // 处理 OR 条件
            if ($field === 'or' && is_array($value)) {
                $callback = function ($q) use ($value) {
                    $this->buildQuery($q, $value);
                };

                if ($this->isEloquent) {
                    $query->orWhere($callback);
                } else {
                    $query->whereOr($callback);
                }
                continue;
            }

            // 处理 AND 条件
            if ($field === 'and' && is_array($value)) {
                $callback = function ($q) use ($value) {
                    $this->buildQuery($q, $value);
                };

                if ($this->isEloquent) {
                    $query->where($callback);
                } else {
                    $query->where($callback);
                }
                continue;
            }

            // 处理自定义 GROUP 条件（闭包）
            if ($field === 'group' && is_callable($value)) {
                $query->where(function ($q) use ($value) {
                    $value($q);
                });
                continue;
            }

            // 处理 RAW 原生条件
            if ($field === 'raw') {
                $query->whereRaw($value);
                continue;
            }

            // 处理普通键值对/数组条件
            if (is_array($value)) {
                // 兼容数组长度不足的情况，设置默认值
                $op = $value[0] ?? '=';
                $val = $value[1] ?? $value[0];

                // 处理各种操作符
                switch (strtolower($op)) {
                    case 'in':
                        $query->whereIn($field, $val);
                        break;
                    case 'not in':
                    case 'not_in':
                        $query->whereNotIn($field, $val);
                        break;
                    case 'between':
                        $query->whereBetween($field, $val);
                        break;
                    case 'not between':
                        $query->whereNotBetween($field, $val);
                        break;
                    case 'like':
                        $query->where($field, 'like', $val);
                        break;
                    case 'not like':
                        $query->where($field, 'not like', $val);
                        break;
                    case 'null':
                        $query->whereNull($field);
                        break;
                    case 'not null':
                        $query->whereNotNull($field);
                        break;
                    case 'exists':
                        // 支持闭包或子查询
                        if ($val instanceof \Closure) {
                            $query->whereExists($val);
                        }
                        break;
                    default:
                        // 默认转为 where(field, op, value)
                        $query->where($field, $op, $val);
                }
            } else {
                // 普通键值对：where(field, value)
                $query->where($field, $value);
            }
        }
    }

    /**
     * 应用 ORDER BY 排序条件
     * 兼容双ORM的排序方法（orderBy vs order）
     * @param mixed $query 查询构造器
     * @param array $orderBy 排序条件
     * @return void
     */
    protected function applyOrderBy(mixed $query, array $orderBy): void
    {
        foreach ($orderBy as $field => $direction) {
            // 统一排序方向为大写（ASC/DESC），兼容小写输入
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

            if ($this->isEloquent) {
                // Illuminate ORM：使用 orderBy 方法
                $query->orderBy($field, $direction);
            } else {
                // ThinkPHP ORM：使用 order 方法（兼容所有版本）
                $query->order($field, $direction);
            }
        }
    }
	
	/**
	 * 实例级：开启/关闭多租户筛选（单个仓库实例生效）
	 * 适用于：单个业务方法中临时关闭筛选（如超管查询单个租户数据）
	 * @param bool $enabled true=开启，false=关闭
	 * @return $this
	 */
	public function setTenantFilterEnabled(bool $enabled): self
	{
		$this->tenantFilterEnabled = $enabled;
		return $this;
	}

	/**
	 * 实例级：获取当前多租户筛选开关状态
	 * @return bool
	 */
	public function isTenantFilterEnabled(): bool
	{
		return $this->tenantFilterEnabled;
	}

	/**
	 * 静态级：超管临时全局关闭多租户筛选（所有仓库实例生效）
	 * 适用于：超管查看所有租户数据、批量操作所有租户数据等场景
	 * @return void
	 */
	public static function superAdminDisableTenantFilter(): void
	{
		self::$superAdminTempDisable = true;
	}

	/**
	 * 静态级：超管操作完成后，恢复全局多租户筛选
	 * 【重要】使用后必须手动调用恢复，避免影响后续业务
	 * @return void
	 */
	public static function superAdminRestoreTenantFilter(): void
	{
		self::$superAdminTempDisable = false;
	}

	/**
	 * 静态级：获取超管临时关闭状态
	 * @return bool
	 */
	public static function isSuperAdminTempDisabled(): bool
	{
		return self::$superAdminTempDisable;
	}
}