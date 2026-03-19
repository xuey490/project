<?php

declare(strict_types=1);

/**
 * 文章模型（数据权限示例）
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Framework\Basic\Traits\DataScopeTrait;
use Framework\Basic\Traits\LaBelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Article 文章模型
 *
 * 演示数据权限功能的示例模型
 * 包含 tenant_id（多租户）、dept_id（部门）、created_by（创建人）字段
 *
 * @property int         $id             文章ID
 * @property int         $tenant_id      租户ID
 * @property string      $title          标题
 * @property string      $content        内容
 * @property string      $summary        摘要
 * @property string      $cover_image    封面图片
 * @property int         $category_id    分类ID
 * @property int         $status         状态
 * @property int         $view_count     浏览次数
 * @property int         $sort           排序
 * @property \DateTime   $published_at   发布时间
 * @property int         $dept_id        所属部门ID（用于数据权限）
 * @property int         $created_by     创建人ID（用于数据权限）
 * @property int         $updated_by     更新人ID
 * @property \DateTime   $created_at     创建时间
 * @property \DateTime   $updated_at     更新时间
 * @property \DateTime   $deleted_at     删除时间
 *
 * @property-read SysUser $creator       创建人
 * @property-read SysDept $dept          所属部门
 */
class Article extends BaseLaORMModel
{
    use SoftDeletes;
    use DataScopeTrait;      // 数据权限 Trait
    use LaBelongsToTenant;   // 多租户 Trait

    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_article';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'tenant_id',
        'title',
        'content',
        'summary',
        'cover_image',
        'category_id',
        'status',
        'sort',
        'published_at',
        'dept_id',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tenant_id' => 'integer',
        'category_id' => 'integer',
        'status' => 'integer',
        'view_count' => 'integer',
        'sort' => 'integer',
        'published_at' => 'datetime',
        'dept_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== 状态常量 ====================

    /** @var int 草稿 */
    public const STATUS_DRAFT = 0;

    /** @var int 已发布 */
    public const STATUS_PUBLISHED = 1;

    /** @var int 已下架 */
    public const STATUS_OFFLINE = 2;

    // ==================== 关联关系 ====================

    /**
     * 创建人
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'created_by', 'id');
    }

    /**
     * 所属部门
     *
     * @return BelongsTo
     */
    public function dept(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'dept_id', 'id');
    }

    /**
     * 更新人
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(SysUser::class, 'updated_by', 'id');
    }

    // ==================== 业务方法 ====================

    /**
     * 是否为草稿
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * 是否已发布
     *
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * 是否已下架
     *
     * @return bool
     */
    public function isOffline(): bool
    {
        return $this->status === self::STATUS_OFFLINE;
    }

    /**
     * 发布文章
     *
     * @return bool
     */
    public function publish(): bool
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = now();
        return $this->save();
    }

    /**
     * 下架文章
     *
     * @return bool
     */
    public function offline(): bool
    {
        $this->status = self::STATUS_OFFLINE;
        return $this->save();
    }

    /**
     * 增加浏览次数
     *
     * @return void
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    // ==================== 查询作用域 ====================

    /**
     * 已发布的文章
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * 按分类筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoryId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * 按部门筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $deptId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDept($query, int $deptId)
    {
        return $query->where('dept_id', $deptId);
    }

    /**
     * 按创建人筛选
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCreator($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    // ==================== 静态方法 ====================

    /**
     * 获取文章状态选项
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            ['value' => self::STATUS_DRAFT, 'label' => '草稿', 'color' => 'default'],
            ['value' => self::STATUS_PUBLISHED, 'label' => '已发布', 'color' => 'success'],
            ['value' => self::STATUS_OFFLINE, 'label' => '已下架', 'color' => 'warning'],
        ];
    }

    /**
     * 获取状态名称
     *
     * @param int $status
     * @return string
     */
    public static function getStatusName(int $status): string
    {
        return match ($status) {
            self::STATUS_DRAFT => '草稿',
            self::STATUS_PUBLISHED => '已发布',
            self::STATUS_OFFLINE => '已下架',
            default => '未知',
        };
    }
}
