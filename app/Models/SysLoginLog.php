<?php

declare(strict_types=1);

/**
 * 登录日志模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SysLoginLog 登录日志模型
 *
 * @property int         $id             日志ID
 * @property int         $user_id        用户ID
 * @property string      $username       用户名
 * @property int         $login_status   登录状态
 * @property string      $login_message  登录消息
 * @property string      $login_ip       登录IP
 * @property string      $login_location 登录地点
 * @property \DateTime   $login_time     登录时间
 * @property string      $browser        浏览器
 * @property string      $os             操作系统
 * @property string      $user_agent     用户代理
 * @property \DateTime   $created_at     创建时间
 */
class SysLoginLog extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_login_log';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 是否自增主键
     * @var bool
     */
    public $incrementing = true;

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'user_id',
        'username',
        'login_status',
        'login_message',
        'login_ip',
        'login_location',
        'login_time',
        'browser',
        'os',
        'user_agent',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'login_status' => 'integer',
        'login_time' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * 是否自动维护时间戳
     * @var bool
     */
    public $timestamps = true;

    /**
     * 只使用 created_at
     * @var bool
     */
    public const UPDATED_AT = null;

    // ==================== 状态常量 ====================

    /** @var int 登录失败 */
    public const STATUS_FAIL = 0;

    /** @var int 登录成功 */
    public const STATUS_SUCCESS = 1;

    // ==================== 业务方法 ====================

    /**
     * 检查是否登录成功
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->login_status === self::STATUS_SUCCESS;
    }

    /**
     * 记录登录日志
     *
     * @param array $data 日志数据
     * @return static
     */
    public static function record(array $data): static
    {
        return self::create(array_merge([
            'login_time' => now(),
        ], $data));
    }

    /**
     * 获取登录状态文本
     *
     * @return string
     */
    public function getStatusText(): string
    {
        return $this->isSuccess() ? '成功' : '失败';
    }

    /**
     * 获取指定用户的最近登录记录
     *
     * @param int $userId 用户ID
     * @param int $limit  数量
     * @return array
     */
    public static function getRecentByUserId(int $userId, int $limit = 10): array
    {
        return self::where('user_id', $userId)
            ->orderBy('login_time', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * 清理指定天数之前的日志
     *
     * @param int $days 天数
     * @return int 删除数量
     */
    public static function cleanOldLogs(int $days = 30): int
    {
        $date = now()->subDays($days);
        return self::where('login_time', '<', $date)->delete();
    }
}
