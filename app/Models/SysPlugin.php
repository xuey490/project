<?php

declare(strict_types=1);

/**
 * 系统插件模型
 *
 * @package App\Models
 */

namespace App\Models;

use Framework\Utils\BaseModel;

/**
 * SysPlugin 模型
 */
class SysPlugin extends BaseModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'sys_plugins';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'version',
        'description',
        'author',
        'namespace',
        'path',
        'status',
        'is_system',
        'config',
        'installed_at',
    ];

    /**
     * 字段类型转换
     *
     * @var array
     */
    protected $casts = [
        'status' => 'integer',
        'is_system' => 'boolean',
        'config' => 'array',
        'installed_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_NOT_INSTALLED = 0;
    const STATUS_INSTALLED = 1;
    const STATUS_ENABLED = 2;

    /**
     * 获取状态列表
     *
     * @return array
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_NOT_INSTALLED => '未安装',
            self::STATUS_INSTALLED => '已安装',
            self::STATUS_ENABLED => '已启用',
        ];
    }

    /**
     * 获取状态文本
     *
     * @return string
     */
    public function getStatusText(): string
    {
        return self::getStatusList()[$this->status] ?? '未知';
    }

    /**
     * 作用域：已安装
     *
     * @param mixed $query
     * @return mixed
     */
    public function scopeInstalled($query)
    {
        return $query->where('status', '>=', self::STATUS_INSTALLED);
    }

    /**
     * 作用域：已启用
     *
     * @param mixed $query
     * @return mixed
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 检查是否已安装
     *
     * @return bool
     */
    public function isInstalled(): bool
    {
        return $this->status >= self::STATUS_INSTALLED;
    }

    /**
     * 检查是否已启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }
}
