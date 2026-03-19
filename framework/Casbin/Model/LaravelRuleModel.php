<?php
declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: LaravelRuleModel.php
 * @Date: 2026-2-7
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Casbin\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * LaravelRuleModel - Laravel 框架 Casbin 规则模型
 *
 * 该类基于 Laravel Eloquent ORM 实现，用于操作 Casbin 策略规则表。
 * 自动适配 Laravel 的数据库配置，支持多数据库连接和表前缀。
 *
 * @package Framework\Casbin\Model
 * @property string $ptype 策略类型
 * @property string $v0    规则值字段 0
 * @property string $v1    规则值字段 1
 * @property string $v2    规则值字段 2
 * @property string $v3    规则值字段 3
 * @property string $v4    规则值字段 4
 * @property string $v5    规则值字段 5
 */
class LaravelRuleModel extends Model
{
    /**
     * 是否启用时间戳自动维护
     * Casbin 规则表通常不需要 created_at 和 updated_at 字段
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可批量赋值的字段列表
     * 包含策略类型和 6 个规则值字段
     *
     * @var array
     */
    protected $fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];

    /**
     * Casbin 驱动名称
     * 用于读取特定驱动的配置，为空时使用默认驱动
     *
     * @var string|null
     */
    protected ?string $driver = null;

    /**
     * 构造函数
     *
     * 初始化模型并设置数据库连接和表名
     *
     * @param array        $attributes 模型属性初始值
     * @param string|null  $driver     Casbin 驱动名称（可选）
     */
    public function __construct(array $attributes = [], ?string $driver = null)
    {
        $this->driver = $driver;

        parent::__construct($attributes);

        $this->initConnectionAndTable();
    }

    /**
     * 初始化数据库连接和表名
     *
     * 自动检测并设置正确的数据库连接，同时根据配置设置表名和前缀。
     * 包含连接存在性检测和 fallback 机制。
     *
     * @return void
     */
    protected function initConnectionAndTable(): void
    {
        // ---------- 1. 获取目标 connection ----------
        $connectionName = app('config')->get('database.default', 'default');

        // ---------- 2. 检测 connection 是否存在 ----------
        try {
            // 获取 Capsule DatabaseManager
            $dbManager = Capsule::getFacadeRoot() ?? Capsule::connection();
            // 尝试获取 connection（不存在会抛异常）
            Capsule::connection($connectionName);
        } catch (\Throwable $e) {

            // fallback 到 default
            $connectionName = 'default';
        }

        // 设置连接
        $this->setConnection($connectionName);

        // ---------- 3. 设置表名 ----------
        $tableName = $this->getConfig('database.rules_table', 'casbin_rules');

        if (!is_string($tableName) || trim($tableName) === '') {
            $tableName = 'casbin_rules';
        }
		

        // 自动前缀
        $prefix = app('config')->get("database.connections.{$connectionName}.prefix", '');
		
        if ($prefix && !str_starts_with($tableName, $prefix)) {
            $tableName = $prefix . $tableName;
        }

        $this->setTable($tableName);
    }

    /**
     * 获取 Casbin 配置项
     *
     * 根据驱动名称从配置文件中读取对应的配置值
     *
     * @param string|null $key     配置键名（支持点分隔的多级配置）
     * @param mixed       $default 默认值
     * @return mixed 配置值
     */
    protected function getConfig(?string $key = null, $default = null)
    {
        $driver = $this->driver ?? app('config')->get('casbin.permission.default');

        $configKey = "casbin.permission.{$driver}.{$key}";

        return app('config')->get($configKey, $default);
    }
}
