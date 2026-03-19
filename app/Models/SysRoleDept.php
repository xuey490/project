<?php

declare(strict_types=1);

/**
 * 角色-部门关联模型（数据权限自定义部门）
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SysRoleDept 角色-部门关联模型
 *
 * 用于存储角色的自定义数据权限部门
 *
 * @property int         $id          主键ID
 * @property int         $role_id     角色ID
 * @property int         $dept_id     部门ID
 * @property int         $created_by  创建人ID
 * @property \DateTime   $created_at  创建时间
 *
 * @property-read SysRole $role       关联角色
 * @property-read SysDept $dept       关联部门
 */
class SysRoleDept extends BaseLaORMModel
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_role_dept';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 是否自动维护时间戳
     * 只记录创建时间
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'role_id',
        'dept_id',
        'created_by',
        'created_at',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'role_id' => 'integer',
        'dept_id' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'datetime',
    ];

    // ==================== 关联关系 ====================

    /**
     * 关联角色
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(SysRole::class, 'role_id', 'id');
    }

    /**
     * 关联部门
     *
     * @return BelongsTo
     */
    public function dept(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'dept_id', 'id');
    }

    // ==================== 查询方法 ====================

    /**
     * 获取角色的自定义部门ID列表
     *
     * @param int $roleId 角色ID
     * @return array
     */
    public static function getDeptIdsByRole(int $roleId): array
    {
        return self::where('role_id', $roleId)
            ->pluck('dept_id')
            ->toArray();
    }

    /**
     * 获取多个角色的自定义部门ID列表（合并去重）
     *
     * @param array $roleIds 角色ID数组
     * @return array
     */
    public static function getDeptIdsByRoles(array $roleIds): array
    {
        if (empty($roleIds)) {
            return [];
        }

        return self::whereIn('role_id', $roleIds)
            ->pluck('dept_id')
            ->unique()
            ->toArray();
    }

    /**
     * 获取部门的角色ID列表
     *
     * @param int $deptId 部门ID
     * @return array
     */
    public static function getRoleIdsByDept(int $deptId): array
    {
        return self::where('dept_id', $deptId)
            ->pluck('role_id')
            ->toArray();
    }

    /**
     * 检查角色是否有自定义部门
     *
     * @param int $roleId 角色ID
     * @return bool
     */
    public static function hasCustomDepts(int $roleId): bool
    {
        return self::where('role_id', $roleId)->exists();
    }

    /**
     * 检查角色是否包含指定部门
     *
     * @param int $roleId 角色ID
     * @param int $deptId 部门ID
     * @return bool
     */
    public static function hasDept(int $roleId, int $deptId): bool
    {
        return self::where('role_id', $roleId)
            ->where('dept_id', $deptId)
            ->exists();
    }

    // ==================== 修改方法 ====================

    /**
     * 同步角色的自定义部门
     *
     * @param int $roleId 角色ID
     * @param array $deptIds 部门ID数组
     * @param int $createdBy 创建人ID
     * @return void
     */
    public static function syncRoleDepts(int $roleId, array $deptIds, int $createdBy = 0): void
    {
        // 删除该角色的所有部门关联
        self::where('role_id', $roleId)->delete();

        // 插入新的部门关联
        if (!empty($deptIds)) {
            $data = [];
            $now = now();

            foreach ($deptIds as $deptId) {
                $data[] = [
                    'role_id' => $roleId,
                    'dept_id' => $deptId,
                    'created_by' => $createdBy,
                    'created_at' => $now,
                ];
            }

            self::insert($data);
        }
    }

    /**
     * 为角色添加单个部门
     *
     * @param int $roleId 角色ID
     * @param int $deptId 部门ID
     * @param int $createdBy 创建人ID
     * @return self|null
     */
    public static function addDept(int $roleId, int $deptId, int $createdBy = 0): ?self
    {
        // 检查是否已存在
        if (self::hasDept($roleId, $deptId)) {
            return null;
        }

        return self::create([
            'role_id' => $roleId,
            'dept_id' => $deptId,
            'created_by' => $createdBy,
            'created_at' => now(),
        ]);
    }

    /**
     * 移除角色的单个部门
     *
     * @param int $roleId 角色ID
     * @param int $deptId 部门ID
     * @return bool
     */
    public static function removeDept(int $roleId, int $deptId): bool
    {
        return self::where('role_id', $roleId)
            ->where('dept_id', $deptId)
            ->delete() > 0;
    }

    /**
     * 删除角色的所有部门关联
     *
     * @param int $roleId 角色ID
     * @return bool
     */
    public static function deleteByRoleId(int $roleId): bool
    {
        return self::where('role_id', $roleId)->delete() !== false;
    }

    /**
     * 删除部门的所有角色关联
     *
     * @param int $deptId 部门ID
     * @return bool
     */
    public static function deleteByDeptId(int $deptId): bool
    {
        return self::where('dept_id', $deptId)->delete() !== false;
    }

    /**
     * 批量添加部门到角色
     *
     * @param int $roleId 角色ID
     * @param array $deptIds 部门ID数组
     * @param int $createdBy 创建人ID
     * @return int 成功添加的数量
     */
    public static function batchAddDepts(int $roleId, array $deptIds, int $createdBy = 0): int
    {
        $count = 0;
        foreach ($deptIds as $deptId) {
            if (self::addDept($roleId, $deptId, $createdBy)) {
                $count++;
            }
        }
        return $count;
    }

    // ==================== 统计方法 ====================

    /**
     * 获取角色的自定义部门数量
     *
     * @param int $roleId 角色ID
     * @return int
     */
    public static function getDeptCount(int $roleId): int
    {
        return self::where('role_id', $roleId)->count();
    }

    /**
     * 获取部门被哪些角色使用
     *
     * @param int $deptId 部门ID
     * @return array
     */
    public static function getRoleListByDept(int $deptId): array
    {
        return self::where('dept_id', $deptId)
            ->with('role')
            ->get()
            ->pluck('role.role_name', 'role.id')
            ->toArray();
    }
}
