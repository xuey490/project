<?php

declare(strict_types=1);

/**
 * 角色菜单关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SysRoleMenu 角色菜单关联模型
 *
 * 多对多关联表模型
 *
 * @property int         $id         主键ID
 * @property int         $role_id    角色ID
 * @property int         $menu_id    菜单ID
 * @property int         $created_by 创建人ID
 * @property int         $updated_by 更新人ID
 * @property \DateTime   $created_at 创建时间
 * @property \DateTime   $updated_at 更新时间
 */
class SysRoleMenu extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_role_menu';

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
        'role_id',
        'menu_id',
        'created_by',
        'updated_by',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'role_id' => 'integer',
        'menu_id' => 'integer',
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
     * 批量插入角色菜单关联
     *
     * @param int   $roleId    角色ID
     * @param array $menuIds   菜单ID数组
     * @param int   $createdBy 创建人ID
     * @return bool
     */
    public static function batchInsert(int $roleId, array $menuIds, int $createdBy = 0): bool
    {
        if (empty($menuIds)) {
            return false;
        }

        $data = [];
        $now = now();

        foreach ($menuIds as $menuId) {
            $data[] = [
                'role_id' => $roleId,
                'menu_id' => $menuId,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return self::insert($data);
    }

    /**
     * 删除角色的所有菜单关联
     *
     * @param int $roleId 角色ID
     * @return bool
     */
    public static function deleteByRoleId(int $roleId): bool
    {
        return self::where('role_id', $roleId)->delete() !== false;
    }

    /**
     * 删除菜单的所有角色关联
     *
     * @param int $menuId 菜单ID
     * @return bool
     */
    public static function deleteByMenuId(int $menuId): bool
    {
        return self::where('menu_id', $menuId)->delete() !== false;
    }

    /**
     * 同步角色菜单
     *
     * @param int   $roleId    角色ID
     * @param array $menuIds   菜单ID数组
     * @param int   $createdBy 创建人ID
     * @return void
     */
    public static function syncRoleMenus(int $roleId, array $menuIds, int $createdBy = 0): void
    {
        // 先删除旧的关联
        self::deleteByRoleId($roleId);

        // 再插入新的关联
        if (!empty($menuIds)) {
            self::batchInsert($roleId, $menuIds, $createdBy);
        }
    }

    /**
     * 获取角色菜单ID列表
     *
     * @param int $roleId 角色ID
     * @return array
     */
    public static function getMenuIdsByRoleId(int $roleId): array
    {
        return self::where('role_id', $roleId)->pluck('menu_id')->toArray();
    }

    /**
     * 批量获取角色菜单ID列表
     *
     * @param array $roleIds 角色ID数组
     * @return array
     */
    public static function getMenuIdsByRoleIds(array $roleIds): array
    {
        if (empty($roleIds)) {
            return [];
        }

        return self::whereIn('role_id', $roleIds)->pluck('menu_id')->toArray();
    }
}
