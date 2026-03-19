<?php

declare(strict_types=1);

/**
 * 用户菜单关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SysUserMenu 用户菜单关联模型
 *
 * 多对多关联表模型，用于用户个人菜单权限
 *
 * @property int         $id         主键ID
 * @property int         $user_id    用户ID
 * @property int         $menu_id    菜单ID
 * @property int         $created_by 创建人ID
 * @property int         $updated_by 更新人ID
 * @property \DateTime   $created_at 创建时间
 * @property \DateTime   $updated_at 更新时间
 */
class SysUserMenu extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_user_menu';

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
        'user_id' => 'integer',
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
     * 批量插入用户菜单关联
     *
     * @param int   $userId    用户ID
     * @param array $menuIds   菜单ID数组
     * @param int   $createdBy 创建人ID
     * @return bool
     */
    public static function batchInsert(int $userId, array $menuIds, int $createdBy = 0): bool
    {
        if (empty($menuIds)) {
            return false;
        }

        $data = [];
        $now = now();

        foreach ($menuIds as $menuId) {
            $data[] = [
                'user_id' => $userId,
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
     * 删除用户的所有菜单关联
     *
     * @param int $userId 用户ID
     * @return bool
     */
    public static function deleteByUserId(int $userId): bool
    {
        return self::where('user_id', $userId)->delete() !== false;
    }

    /**
     * 删除菜单的所有用户关联
     *
     * @param int $menuId 菜单ID
     * @return bool
     */
    public static function deleteByMenuId(int $menuId): bool
    {
        return self::where('menu_id', $menuId)->delete() !== false;
    }

    /**
     * 同步用户菜单
     *
     * @param int   $userId    用户ID
     * @param array $menuIds   菜单ID数组
     * @param int   $createdBy 创建人ID
     * @return void
     */
    public static function syncUserMenus(int $userId, array $menuIds, int $createdBy = 0): void
    {
        // 先删除旧的关联
        self::deleteByUserId($userId);

        // 再插入新的关联
        if (!empty($menuIds)) {
            self::batchInsert($userId, $menuIds, $createdBy);
        }
    }

    /**
     * 获取用户菜单ID列表
     *
     * @param int $userId 用户ID
     * @return array
     */
    public static function getMenuIdsByUserId(int $userId): array
    {
        return self::where('user_id', $userId)->pluck('menu_id')->toArray();
    }
}
