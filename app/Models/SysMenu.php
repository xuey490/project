<?php

declare(strict_types=1);

/**
 * 系统菜单模型
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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * SysMenu 系统菜单模型
 *
 * 菜单表模型，支持无限级层级结构
 * 菜单类型: 1=目录, 2=菜单, 3=按钮, 4=外链
 *
 * @property int         $id          菜单ID
 * @property int         $parent_id   父菜单ID
 * @property string      $menu_name   菜单名称
 * @property int         $menu_type   菜单类型: 1=目录 2=菜单 3=按钮 4=外链
 * @property string      $path        路由路径
 * @property string      $component   组件路径
 * @property string      $permission  权限标识
 * @property string      $icon        菜单图标
 * @property int         $sort        排序
 * @property int         $visible     是否可见: 0=隐藏 1=显示
 * @property int         $status      状态: 0=禁用 1=启用
 * @property int         $is_frame    是否外链: 0=否 1=是
 * @property int         $is_cache    是否缓存: 0=否 1=是
 * @property string      $remark      备注
 * @property int         $created_by  创建人ID
 * @property int         $updated_by  更新人ID
 * @property \DateTime   $created_at  创建时间
 * @property \DateTime   $updated_at  更新时间
 * @property \DateTime   $deleted_at  删除时间
 *
 * @property-read SysMenu    $parent    父菜单
 * @property-read SysMenu[]  $children  子菜单
 * @property-read SysRole[]  $roles     拥有此菜单的角色
 */
class SysMenu extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_menu';

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
        'menu_name',
        'menu_type',
        'path',
        'component',
        'permission',
        'icon',
        'sort',
        'visible',
        'status',
        'is_frame',
        'is_cache',
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
        'menu_type' => 'integer',
        'sort' => 'integer',
        'visible' => 'integer',
        'status' => 'integer',
        'is_frame' => 'integer',
        'is_cache' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== 菜单类型常量 ====================

    /** @var int 目录类型 */
    public const TYPE_DIRECTORY = 1;

    /** @var int 菜单类型 */
    public const TYPE_MENU = 2;

    /** @var int 按钮类型 */
    public const TYPE_BUTTON = 3;

    /** @var int 外链类型 */
    public const TYPE_LINK = 4;

    // ==================== 状态常量 ====================

    /** @var int 禁用状态 */
    public const STATUS_DISABLED = 0;

    /** @var int 启用状态 */
    public const STATUS_ENABLED = 1;

    /** @var int 隐藏 */
    public const VISIBLE_HIDDEN = 0;

    /** @var int 显示 */
    public const VISIBLE_SHOWN = 1;

    // ==================== 关联关系 ====================

    /**
     * 父菜单
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SysMenu::class, 'parent_id', 'id');
    }

    /**
     * 子菜单
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(SysMenu::class, 'parent_id', 'id');
    }

    /**
     * 拥有此菜单的角色 (多对多)
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            SysRole::class,
            'sys_role_menu',
            'menu_id',
            'role_id'
        )->withTimestamps();
    }

    // ==================== 业务方法 ====================

    /**
     * 检查菜单是否被禁用
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    /**
     * 检查菜单是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 检查是否为目录
     *
     * @return bool
     */
    public function isDirectory(): bool
    {
        return $this->menu_type === self::TYPE_DIRECTORY;
    }

    /**
     * 检查是否为菜单
     *
     * @return bool
     */
    public function isMenu(): bool
    {
        return $this->menu_type === self::TYPE_MENU;
    }

    /**
     * 检查是否为按钮
     *
     * @return bool
     */
    public function isButton(): bool
    {
        return $this->menu_type === self::TYPE_BUTTON;
    }

    /**
     * 检查是否为外链
     *
     * @return bool
     */
    public function isLink(): bool
    {
        return $this->menu_type === self::TYPE_LINK;
    }

    /**
     * 检查是否可见
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible === self::VISIBLE_SHOWN;
    }

    /**
     * 获取菜单树 (递归)
     *
     * @param int $parentId 父ID
     * @return array
     */
    public static function getMenuTree(int $parentId = 0): array
    {
        $menus = self::where('parent_id', $parentId)
            ->where('status', self::STATUS_ENABLED)
            ->orderBy('sort')
            ->get()
            ->toArray();

        foreach ($menus as &$menu) {
            $menu['children'] = self::getMenuTree($menu['id']);
        }

        return $menus;
    }

    /**
     * 获取所有子菜单ID (包含自己)
     *
     * @param int $menuId 菜单ID
     * @return array
     */
    public static function getAllChildIds(int $menuId): array
    {
        $ids = [$menuId];
        $children = self::where('parent_id', $menuId)->pluck('id')->toArray();

        foreach ($children as $childId) {
            $ids = array_merge($ids, self::getAllChildIds($childId));
        }

        return $ids;
    }

    /**
     * 检查是否有子菜单
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return self::where('parent_id', $this->id)->exists();
    }

    /**
     * 获取菜单层级路径
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
                'menu_name' => $current->menu_name,
            ]);
            $current = $current->parent;
        }

        return $path;
    }

    /**
     * 获取菜单类型名称
     *
     * @return string
     */
    public function getMenuTypeName(): string
    {
        return match ($this->menu_type) {
            self::TYPE_DIRECTORY => '目录',
            self::TYPE_MENU => '菜单',
            self::TYPE_BUTTON => '按钮',
            self::TYPE_LINK => '外链',
            default => '未知',
        };
    }
}
