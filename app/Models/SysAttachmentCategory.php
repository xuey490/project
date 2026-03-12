<?php

declare(strict_types=1);

/**
 * 附件分类模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SysAttachmentCategory 附件分类模型
 *
 * @property int         $id            分类ID
 * @property int         $parent_id     父分类ID
 * @property string      $category_name 分类名称
 * @property string      $category_code 分类编码
 * @property int         $sort          排序
 * @property int         $status        状态
 * @property string      $remark        备注
 * @property int         $created_by    创建人ID
 * @property int         $updated_by    更新人ID
 * @property \DateTime   $created_at    创建时间
 * @property \DateTime   $updated_at    更新时间
 * @property \DateTime   $deleted_at    删除时间
 *
 * @property-read SysAttachmentCategory $parent   父分类
 * @property-read SysAttachmentCategory[] $children 子分类
 * @property-read SysAttachment[] $attachments 附件列表
 */
class SysAttachmentCategory extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_attachment_category';

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
        'parent_id',
        'category_name',
        'category_code',
        'sort',
        'status',
        'remark',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== 状态常量 ====================

    /** @var int 禁用状态 */
    public const STATUS_DISABLED = 0;

    /** @var int 启用状态 */
    public const STATUS_ENABLED = 1;

    // ==================== 关联关系 ====================

    /**
     * 父分类
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SysAttachmentCategory::class, 'parent_id', 'id');
    }

    /**
     * 子分类
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(SysAttachmentCategory::class, 'parent_id', 'id');
    }

    /**
     * 附件列表
     *
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(SysAttachment::class, 'category_id', 'id');
    }

    // ==================== 业务方法 ====================

    /**
     * 检查是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 获取分类树
     *
     * @param int $parentId 父ID
     * @return array
     */
    public static function getCategoryTree(int $parentId = 0): array
    {
        $categories = self::where('parent_id', $parentId)
            ->where('status', self::STATUS_ENABLED)
            ->orderBy('sort')
            ->get()
            ->toArray();

        foreach ($categories as &$category) {
            $category['children'] = self::getCategoryTree($category['id']);
        }

        return $categories;
    }

    /**
     * 获取所有子分类ID (包含自己)
     *
     * @param int $categoryId 分类ID
     * @return array
     */
    public static function getAllChildIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $children = self::where('parent_id', $categoryId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids = array_merge($ids, self::getAllChildIds($childId));
        }

        return $ids;
    }

    /**
     * 检查是否有子分类
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return self::where('parent_id', $this->id)->exists();
    }

    /**
     * 检查分类下是否有附件
     *
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return SysAttachment::where('category_id', $this->id)->exists();
    }
}
