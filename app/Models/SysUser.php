<?php

declare(strict_types=1);

/**
 * 系统用户模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;
use Framework\Tenant\TenantContext;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SysUser 系统用户模型
 *
 * 用户表模型，包含用户基本信息、状态管理、角色关联等
 * 支持多租户：用户可属于多个租户，在不同租户拥有不同角色
 *
 * @property int         $id             用户ID
 * @property string      $username       用户名
 * @property string      $password       密码
 * @property string      $nickname       昵称
 * @property string      $email          邮箱
 * @property string      $mobile         手机号
 * @property string      $avatar         头像
 * @property int         $dept_id        部门ID
 * @property int         $status         状态 0=禁用 1=启用
 * @property int         $is_admin       是否超级管理员
 * @property string      $last_login_ip  最后登录IP
 * @property \DateTime   $last_login_time 最后登录时间
 * @property string      $remark         备注
 * @property int         $created_by     创建人ID
 * @property int         $updated_by     更新人ID
 * @property \DateTime   $created_at     创建时间
 * @property \DateTime   $updated_at     更新时间
 * @property \DateTime   $deleted_at     删除时间
 *
 * @property-read SysRole[]   $roles      用户角色列表（当前租户）
 * @property-read SysMenu[]   $menus      用户个人菜单（当前租户）
 * @property-read SysDept     $dept       所属部门（当前租户）
 * @property-read SysTenant[] $tenants    用户所属的所有租户
 */
class SysUser extends BaseLaORMModel
{
    use SoftDeletes;

    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_user';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'password',
        'deleted_at',
    ];

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'nickname',
        'email',
        'mobile',
        'avatar',
        'dept_id',
        'status',
        'is_admin',
        'last_login_ip',
        'last_login_time',
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
        'dept_id' => 'integer',
        'status' => 'integer',
        'is_admin' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'last_login_time' => 'datetime',
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
     * 用户所属部门
     *
     * @return BelongsTo
     */
    public function dept(): BelongsTo
    {
        return $this->belongsTo(SysDept::class, 'dept_id', 'id');
    }

    /**
     * 用户拥有的角色 (多对多) - 当前租户
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $tenantId = TenantContext::getTenantId();

        return $this->belongsToMany(
            SysRole::class,
            'sys_user_role',
            'user_id',
            'role_id'
        )
        ->wherePivot('tenant_id', $tenantId ?? 0)
        ->withTimestamps();
    }

    /**
     * 获取指定租户的角色
     *
     * @param int $tenantId 租户ID
     * @return BelongsToMany
     */
    public function rolesByTenant(int $tenantId): BelongsToMany
    {
        return $this->belongsToMany(
            SysRole::class,
            'sys_user_role',
            'user_id',
            'role_id'
        )
        ->wherePivot('tenant_id', $tenantId)
        ->withTimestamps();
    }

    /**
     * 用户所属的所有租户
     *
     * @return HasMany
     */
    public function tenantRelations(): HasMany
    {
        return $this->hasMany(SysUserTenant::class, 'user_id', 'id');
    }

    /**
     * 用户所属的所有租户（通过关联表）
     *
     * @return BelongsToMany
     */
    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(
            SysTenant::class,
            'sys_user_tenant',
            'user_id',
            'tenant_id'
        )->withPivot('is_default', 'join_time')
         ->withTimestamps();
    }

    /**
     * 用户个人菜单 (多对多)
     *
     * @return BelongsToMany
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(
            SysMenu::class,
            'sys_user_menu',
            'user_id',
            'menu_id'
        )->withTimestamps();
    }

    // ==================== 业务方法 ====================

    /**
     * 检查用户是否被禁用
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->status === self::STATUS_DISABLED;
    }

    /**
     * 检查用户是否启用
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 检查是否为超级管理员
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_admin === 1;
    }

    /**
     * 验证密码
     *
     * @param string $password 明文密码
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * 设置密码 (加密)
     *
     * @param string $password 明文密码
     * @return void
     */
    public function setPasswordAttribute(string $password): void
    {
        $this->attributes['password'] = password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * 获取用户的所有角色编码（当前租户）
     *
     * @return array
     */
    public function getRoleCodes(): array
    {
        return $this->roles()->where('status', SysRole::STATUS_ENABLED)->pluck('role_code')->toArray();
    }

    /**
     * 获取用户的所有角色ID（当前租户）
     *
     * @return array
     */
    public function getRoleIds(): array
    {
        return $this->roles()->where('status', SysRole::STATUS_ENABLED)->pluck('id')->toArray();
    }

    /**
     * 获取用户在指定租户的角色编码
     *
     * @param int $tenantId 租户ID
     * @return array
     */
    public function getRoleCodesByTenant(int $tenantId): array
    {
        return $this->rolesByTenant($tenantId)
            ->where('status', SysRole::STATUS_ENABLED)
            ->pluck('role_code')
            ->toArray();
    }

    /**
     * 获取用户在指定租户的角色ID
     *
     * @param int $tenantId 租户ID
     * @return array
     */
    public function getRoleIdsByTenant(int $tenantId): array
    {
        return $this->rolesByTenant($tenantId)
            ->where('status', SysRole::STATUS_ENABLED)
            ->pluck('id')
            ->toArray();
    }

    /**
     * 获取用户可访问的所有租户ID
     *
     * @return array
     */
    public function getTenantIds(): array
    {
        return SysUserTenant::getTenantIdsByUser($this->id);
    }

    /**
     * 获取用户的默认租户ID
     *
     * @return int|null
     */
    public function getDefaultTenantId(): ?int
    {
        return SysUserTenant::getDefaultTenantId($this->id);
    }

    /**
     * 检查用户是否属于指定租户
     *
     * @param int $tenantId 租户ID
     * @return bool
     */
    public function belongsToTenant(int $tenantId): bool
    {
        return SysUserTenant::isUserInTenant($this->id, $tenantId);
    }

    /**
     * 获取用户的合并菜单ID列表 (角色菜单 + 个人菜单) - 当前租户
     *
     * @return array
     */
    public function getMergedMenuIds(): array
    {
        $tenantId = TenantContext::getTenantId();

        // 超级管理员拥有当前租户的所有菜单
        if ($this->isSuperAdmin()) {
            return SysMenu::where('tenant_id', $tenantId ?? 0)
                ->where('status', SysMenu::STATUS_ENABLED)
                ->pluck('id')
                ->toArray();
        }

        // 1. 获取当前租户的角色菜单ID
        $roleIds = $this->getRoleIds();
        $roleMenuIds = SysRoleMenu::whereIn('role_id', $roleIds)
            ->where('tenant_id', $tenantId ?? 0)
            ->pluck('menu_id')
            ->toArray();

        // 2. 获取当前租户的用户个人菜单ID
        $userMenuIds = SysUserMenu::where('user_id', $this->id)
            ->where('tenant_id', $tenantId ?? 0)
            ->pluck('menu_id')
            ->toArray();

        // 3. 合并去重
        return array_unique(array_merge($roleMenuIds, $userMenuIds));
    }

    /**
     * 获取用户的菜单列表 (树形结构)
     *
     * @return array
     */
    public function getMenuTree(): array
    {
        $menuIds = $this->getMergedMenuIds();

        if (empty($menuIds)) {
            return [];
        }

        $menus = SysMenu::whereIn('id', $menuIds)
            ->where('status', SysMenu::STATUS_ENABLED)
            ->orderBy('sort')
            ->get()
            ->toArray();

        return $this->buildMenuTree($menus, 0);
    }

    /**
     * 构建菜单树
     *
     * @param array $menus    菜单列表
     * @param int   $parentId 父ID
     * @return array
     */
    protected function buildMenuTree(array $menus, int $parentId = 0): array
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ((int)$menu['parent_id'] === $parentId) {
                $children = $this->buildMenuTree($menus, (int)$menu['id']);
                if ($children) {
                    $menu['children'] = $children;
                }
                $tree[] = $menu;
            }
        }
        return $tree;
    }

    /**
     * 获取用户的所有权限标识
     *
     * @return array
     */
    public function getPermissions(): array
    {
        $menuIds = $this->getMergedMenuIds();

        if (empty($menuIds)) {
            return [];
        }

        return SysMenu::whereIn('id', $menuIds)
            ->where('status', SysMenu::STATUS_ENABLED)
            ->where('permission', '!=', '')
            ->pluck('permission')
            ->toArray();
    }

    /**
     * 更新最后登录信息
     *
     * @param string $ip 登录IP
     * @return void
     */
    public function updateLoginInfo(string $ip): void
    {
        $this->last_login_ip = $ip;
        $this->last_login_time = now();
        $this->save();
    }
}
