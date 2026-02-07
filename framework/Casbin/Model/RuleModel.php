<?php
declare(strict_types=1);

namespace Framework\Casbin\Model;

use think\Model;
use think\facade\Db;

/**
 * Casbin RuleModel for ThinkPHP
 */
class RuleModel extends Model
{
    public $autoWriteTimestamp = false;

    // 建议显式定义 table 属性，有助于IDE提示也能避免部分魔术方法干扰
    protected $table; 

    protected $schema = [
        'id'    => 'int',
        'ptype' => 'string',
        'v0'    => 'string',
        'v1'    => 'string',
        'v2'    => 'string',
        'v3'    => 'string',
        'v4'    => 'string',
        'v5'    => 'string'
    ];

    protected ?string $driver = null;

    public function __construct(array $data = [], ?string $driver = null)
    {
        // 1. 【关键修改】先调用父类构造函数，初始化 ORM 内部结构
        parent::__construct($data);

        $this->driver = $driver;

        // ---------- 设置表名 ----------
        $tableName = $this->getCasbinConfig('rules_table', 'casbin_rules');

        // 获取默认连接前缀
        $prefix = config('database.connections.' . config('database.default') . '.prefix', '');
        if ($prefix && !str_starts_with($tableName, $prefix)) {
            $tableName = $prefix . $tableName;
        }
        
        // 2. 此时内部结构已初始化，可以安全地设置 table
        $this->table = $tableName;
    }

    protected function getCasbinConfig(?string $key = null, $default = null)
    {
        $driver = $this->driver ?? config('permission.enforcers.default');
        return config("permission.enforcers.{$driver}.{$key}", $default);
    }
}