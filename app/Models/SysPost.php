<?php

declare(strict_types=1);

/**
 * 系统岗位模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * SysPost 系统岗位模型
 *
 * 岗位表模型，管理系统中岗位信息
 *
 * @property int         $id          岗位ID
 * @property string      $post_code   岗位编码
 * @property string      $post_name   岗位名称
 * @property int         $post_sort   岗位排序
 * @property int         $enabled     状态 0=禁用 1=启用
 * @property string      $del_flag    删除标志 0=正常 1=删除
 * @property string      $remark      备注
 * @property int         $created_by  创建人ID
 * @property int         $updated_by  更新人ID
 * @property \DateTime   $created_at  创建时间
 * @property \DateTime   $updated_at  更新时间
 *
 * @property-read SysUser[]  $users   岗位下的用户
 */
class SysPost extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_post';

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
        'post_code',
        'post_name',
        'post_sort',
        'enabled',
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
        'post_sort' => 'integer',
        'enabled' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== 状态常量 ====================

    /** @var int 禁用状态 */
    public const ENABLED_DISABLED = 0;

    /** @var int 启用状态 */
    public const ENABLED_ENABLED = 1;

    // ==================== 关联关系 ====================

    /**
     * 岗位下的用户 (多对多)
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            SysUser::class,
            'sys_user_post',
            'post_id',
            'user_id'
        )->withTimestamps();
    }

    // ==================== 业务方法 ====================

    /**
     * 检查岗位是否被禁用
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->enabled === self::ENABLED_DISABLED;
    }

    /**
     * 检查岗位是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled === self::ENABLED_ENABLED;
    }

    /**
     * 检查岗位编码是否唯一
     *
     * @param string $postCode  岗位编码
     * @param int    $excludeId 排除的岗位ID
     * @return bool
     */
    public static function isPostCodeUnique(string $postCode, int $excludeId = 0): bool
    {
        $query = self::where('post_code', $postCode);

        if ($excludeId > 0) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * 检查岗位下是否有用户
     *
     * @return bool
     */
    public function hasUsers(): bool
    {
        return SysUserPost::where('post_id', $this->id)->exists();
    }

    /**
     * 获取岗位下的用户ID列表
     *
     * @return array
     */
    public function getUserIds(): array
    {
        return SysUserPost::where('post_id', $this->id)->pluck('user_id')->toArray();
    }
}
