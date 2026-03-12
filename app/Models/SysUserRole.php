<?php

declare(strict_types=1);

/**
 * 用户角色关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SysUserRole 用户角色关联模型
 *
 * 多对多关联表模型
 *
 * @property int         $id         主键ID
 * @property int         $user_id    用户ID
 * @property int         $role_id    角色ID
 * @property int         $created_by 创建人ID
 * @property int         $updated_by 更新人ID
 * @property \DateTime   $created_at 创建时间
 * @property \DateTime   $updated_at 更新时间
 */
class SysUserRole extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_user_role';

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
        'user_id',
        'role_id',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'role_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 是否自动维护时间戳
     * @var bool
     */
    public $timestamps = true;

    // ==================== 业务方法 ====================

    /**
     * 批量插入用户角色关联
     *
     * @param int   $userId  用户ID
     * @param array $roleIds 角色ID数组
     * @param int   $createdBy 创建人ID
     * @return bool
     */
    public static function batchInsert(int $userId, array $roleIds, int $createdBy = 0): bool
    {
        if (empty($roleIds)) {
            return false;
        }

        $data = [];
        $now = now();

        foreach ($roleIds as $roleId) {
            $data[] = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return self::insert($data);
    }

    /**
     * 删除用户的所有角色关联
     *
     * @param int $userId 用户ID
     * @return bool
     */
    public static function deleteByUserId(int $userId): bool
    {
        return self::where('user_id', $userId)->delete() !== false;
    }

    /**
     * 删除角色的所有用户关联
     *
     * @param int $roleId 角色ID
     * @return bool
     */
    public static function deleteByRoleId(int $roleId): bool
    {
        return self::where('role_id', $roleId)->delete() !== false;
    }

    /**
     * 同步用户角色
     *
     * @param int   $userId  用户ID
     * @param array $roleIds 角色ID数组
     * @param int   $createdBy 创建人ID
     * @return void
     */
    public static function syncUserRoles(int $userId, array $roleIds, int $createdBy = 0): void
    {
        // 先删除旧的关联
        self::deleteByUserId($userId);

        // 再插入新的关联
        if (!empty($roleIds)) {
            self::batchInsert($userId, $roleIds, $createdBy);
        }
    }
}
