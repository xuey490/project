<?php
declare(strict_types=1);

namespace Framework\Casbin\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class LaravelRuleModel extends Model
{
    public $timestamps = false;

    protected $fillable = ['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'];

    /**
     * Casbin driver（不是 DB connection）
     */
    protected ?string $driver = null;

    public function __construct(array $attributes = [], ?string $driver = null)
    {
        $this->driver = $driver;

        parent::__construct($attributes);

        $this->initConnectionAndTable();
    }

    /**
     * 初始化连接与表名
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
     * Casbin config
     */
    protected function getConfig(?string $key = null, $default = null)
    {
        $driver = $this->driver ?? app('config')->get('casbin.permission.default');

        $configKey = "casbin.permission.{$driver}.{$key}";

        return app('config')->get($configKey, $default);
    }
}
