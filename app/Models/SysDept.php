<?php

declare(strict_types=1);

/**
 * 系统部门模型
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
 * SysDept 系统部门模型
 *
 * 部门表模型，支持无限级层级结构
 *
 * @property int         $id          部门ID
 * @property int         $parent_id   父部门ID
 * @property string      $dept_name   部门名称
 * @property string      $dept_code   部门编码
 * @property string      $leader      负责人
 * @property string      $phone       联系电话
 * @property string      $email       邮箱
 * @property int         $sort        排序
 * @property int         $status      状态 0=禁用 1=启用
 * @property string      $remark      备注
 * @property int         $created_by  创建人ID
 * @property int         $updated_by  更新人ID
 * @property \DateTime   $created_at  创建时间
 * @property \DateTime   $updated_at  更新时间
 * @property \DateTime   $deleted_at  删除时间
 *
 * @property-read SysDept    $parent    父部门
 * @property-read SysDept[]  $children  子部门
 * @property-read SysUser[]  $users     部门用户
 */
class SysDept extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_dept';

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
        'dept_name',
        'dept_code',
        'leader',
        'phone',
        'email',
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
     * 父部门
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'parent_id', 'id');
    }

    /**
     * 子部门
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(SysDept::class, 'parent_id', 'id');
    }

    /**
     * 部门下的用户
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(SysUser::class, 'dept_id', 'id');
    }

    // ==================== 业务方法 ====================

    /**
     * 检查部门是否被禁用
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    /**
     * 检查部门是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 获取部门树 (递归)
     *
     * @param int $parentId 父ID
     * @return array
     */
    public static function getDeptTree(int $parentId = 0): array
    {
        $depts = self::where('parent_id', $parentId)
            ->where('status', self::STATUS_ENABLED)
            ->orderBy('sort')
            ->get()
            ->toArray();

        foreach ($depts as &$dept) {
            $dept['children'] = self::getDeptTree($dept['id']);
        }

        return $depts;
    }

    /**
     * 获取所有子部门ID (包含自己)
     *
     * @param int $deptId 部门ID
     * @return array
     */
    public static function getAllChildIds(int $deptId): array
    {
        $ids = [$deptId];
        $children = self::where('parent_id', $deptId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids = array_merge($ids, self::getAllChildIds($childId));
        }

        return $ids;
    }

    /**
     * 获取部门层级路径
     *
     * @return array
     */
    public function getPath(): array
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, [
                'id' => $current->id,
                'dept_name' => $current->dept_name,
            ]);
            $current = $current->parent;
        }

        return $path;
    }

    /**
     * 检查部门编码是否唯一
     *
     * @param string $deptCode  部门编码
     * @param int    $excludeId 排除的部门ID
     * @return bool
     */
    public static function isDeptCodeUnique(string $deptCode, int $excludeId = 0): bool
    {
        $query = self::where('dept_code', $deptCode);

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * 检查是否有子部门
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return self::where('parent_id', $this->id)->exists();
    }

    /**
     * 检查部门下是否有用户
     *
     * @return bool
     */
    public function hasUsers(): bool
    {
        return SysUser::where('dept_id', $this->id)->exists();
    }
}
