<?php
declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: RuleModel.php
 * @Date: 2026-2-7
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Casbin\Model;

use think\Model;
use think\facade\Db;

/**
 * RuleModel - ThinkPHP 框架 Casbin 规则模型
 *
 * 该类基于 ThinkPHP ORM 实现，用于操作 Casbin 策略规则表。
 * 自动适配 ThinkPHP 的数据库配置，支持多数据库连接和表前缀。
 *
 * @package Framework\Casbin\Model
 * @property int    $id    主键 ID
 * @property string $ptype 策略类型
 * @property string $v0    规则值字段 0
 * @property string $v1    规则值字段 1
 * @property string $v2    规则值字段 2
 * @property string $v3    规则值字段 3
 * @property string $v4    规则值字段 4
 * @property string $v5    规则值字段 5
 */
class RuleModel extends Model
{
    /**
     * 是否启用时间戳自动维护
     * 设置为 false 表示不自动维护 created_at 和 updated_at 字段
     *
     * @var bool
     */
    public $autoWriteTimestamp = false;

    /**
     * 数据表名
     * 在构造函数中根据配置动态设置
     *
     * @var string
     */
    protected $table; 

    /**
     * 数据表字段结构定义
     * 定义各字段的类型，有助于 IDE 提示和避免魔术方法干扰
     *
     * @var array
     */
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
     * 初始化模型并设置数据库表名。
     * 注意：先调用父类构造函数初始化 ORM 内部结构，
     * 再设置 table 属性以避免潜在问题。
     *
     * @param array        $data   模型数据初始值
     * @param string|null  $driver Casbin 驱动名称（可选）
     */
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

    /**
     * 获取 Casbin 配置项
     *
     * 根据驱动名称从配置文件中读取对应的配置值
     *
     * @param string|null $key     配置键名（支持点分隔的多级配置）
     * @param mixed       $default 默认值
     * @return mixed 配置值
     */
    protected function getCasbinConfig(?string $key = null, $default = null)
    {
        $driver = $this->driver ?? config('permission.enforcers.default');
        return config("permission.enforcers.{$driver}.{$key}", $default);
    }
}
