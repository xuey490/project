<?php

declare(strict_types=1);

/**
 * 系统菜单服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysMenu;
use App\Models\SysRoleMenu;
use App\Models\SysUserMenu;
use App\Services\Casbin\CasbinService;
use App\Dao\SysMenuDao;
use Framework\Basic\BaseService;

/**
 * SysMenuService 菜单服务
 *
 * 处理菜单相关的业务逻辑
 */
class SysMenuService extends BaseService
{
    /**
     * DAO 实例
     * @var SysMenuDao
     */
    protected SysMenuDao $menuDao;

    /**
     * Casbin 服务
     * @var CasbinService
     */
    protected CasbinService $casbinService;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->menuDao = new SysMenuDao();
        $this->casbinService = new CasbinService();
    }

    /**
     * 获取菜单列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $menuName = $params['menu_name'] ?? '';
        $menuType = $params['menu_type'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysMenu::query()->whereNull('deleted_at');

        if ($menuName !== '') {
            $query->where('menu_name', 'like', "%{$menuName}%");
        }

        if ($menuType !== '') {
            $query->where('menu_type', (int)$menuType);
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $list = $query->orderBy('sort')->get()->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item = $this->formatMenu($item);
        }

        return $list;
    }

    /**
     * 获取菜单树
     *
     * @return array
     */
    public function getMenuTree(): array
    {
        return SysMenu::getMenuTree();
    }

    /**
     * 获取菜单详情
     *
     * @param int $menuId 菜单ID
     * @return array|null
     */
    public function getDetail(int $menuId): ?array
    {
        $menu = SysMenu::find($menuId);

        if (!$menu) {
            return null;
        }

        $data = $this->formatMenu($menu);
        $data['path'] = $menu->getPath();
        $data['menu_type_name'] = $menu->getMenuTypeName();

        return $data;
    }

    /**
     * 创建菜单
     *
     * @param array $data     菜单数据
     * @param int   $operator 操作人ID
     * @return SysMenu|null
     */
    public function create(array $data, int $operator = 0): ?SysMenu
    {
        // 设置审计字段
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        // 如果是外链，设置 is_frame = 1
        if (($data['menu_type'] ?? 0) === SysMenu::TYPE_LINK) {
            $data['is_frame'] = 1;
        }

        return SysMenu::create($data);
    }

    /**
     * 更新菜单
     *
     * @param int   $menuId   菜单ID
     * @param array $data     菜单数据
     * @param int   $operator 操作人ID
     * @return bool
     */
    public function update(int $menuId, array $data, int $operator = 0): bool
    {
        $menu = SysMenu::find($menuId);
        if (!$menu) {
            throw new \Exception('菜单不存在');
        }

        // 检查父菜单是否有效
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            if ($data['parent_id'] == $menuId) {
                throw new \Exception('父菜单不能是自己');
            }

            // 检查父菜单是否存在
            if (!SysMenu::where('id', $data['parent_id'])->exists()) {
                throw new \Exception('父菜单不存在');
            }
        }

        // 设置审计字段
        $data['updated_by'] = $operator;

        // 如果是外链，设置 is_frame = 1
        if (($data['menu_type'] ?? $menu->menu_type) === SysMenu::TYPE_LINK) {
            $data['is_frame'] = 1;
        }

        $menu->fill($data);
        $result = $menu->save();

        // 更新 Casbin 权限
        if ($result && !empty($menu->permission)) {
            $this->syncMenuPermissions($menuId);
        }

        return $result;
    }

    /**
     * 删除菜单
     *
     * @param int $menuId 菜单ID
     * @return bool
     */
    public function delete(int $menuId): bool
    {
        $menu = SysMenu::find($menuId);
        if (!$menu) {
            return false;
        }

        // 检查是否有子菜单
        if ($menu->hasChildren()) {
            throw new \Exception('该菜单下存在子菜单，无法删除');
        }

        // 软删除菜单
        $menu->delete();

        // 删除角色菜单关联
        SysRoleMenu::deleteByMenuId($menuId);

        // 删除用户菜单关联
        SysUserMenu::deleteByMenuId($menuId);

        return true;
    }

    /**
     * 更新菜单状态
     *
     * @param int $menuId 菜单ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $menuId, int $status): bool
    {
        return $this->menuDao->updateStatus($menuId, $status);
    }

    /**
     * 获取用户菜单树
     *
     * @param int $userId 用户ID
     * @return array
     */
    public function getUserMenuTree(int $userId): array
    {
        $user = \App\Models\SysUser::find($userId);
        if (!$user) {
            return [];
        }

        return $user->getMenuTree();
    }

    /**
     * 获取用户权限列表
     *
     * @param int $userId 用户ID
     * @return array
     */
    public function getUserPermissions(int $userId): array
    {
        $user = \App\Models\SysUser::find($userId);
        if (!$user) {
            return [];
        }

        return $user->getPermissions();
    }

    /**
     * 获取目录和菜单类型列表 (用于分配权限)
     *
     * @return array
     */
    public function getDirectoryAndMenuTree(): array
    {
        $menus = SysMenu::where('status', SysMenu::STATUS_ENABLED)
            ->whereIn('menu_type', [SysMenu::TYPE_DIRECTORY, SysMenu::TYPE_MENU])
            ->orderBy('sort')
            ->get()
            ->toArray();

        return $this->buildTree($menus, 0);
    }

    /**
     * 同步菜单权限到 Casbin
     *
     * @param int $menuId 菜单ID
     * @return void
     */
    protected function syncMenuPermissions(int $menuId): void
    {
        // 获取拥有该菜单的所有角色
        $roleIds = SysRoleMenu::where('menu_id', $menuId)->pluck('role_id')->toArray();

        foreach ($roleIds as $roleId) {
            $this->casbinService->syncRoleMenuPermissions($roleId);
        }
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化菜单数据
     *
     * @param SysMenu|array $menu 菜单
     * @return array
     */
    protected function formatMenu(SysMenu|array $menu): array
    {
        if ($menu instanceof SysMenu) {
            $data = $menu->toArray();
        } else {
            $data = $menu;
        }

        // 格式化时间
        if (isset($data['created_at'])) {
            $data['created_at'] = is_string($data['created_at'])
                ? $data['created_at']
                : $data['created_at']?->format('Y-m-d H:i:s');
        }

        if (isset($data['updated_at'])) {
            $data['updated_at'] = is_string($data['updated_at'])
                ? $data['updated_at']
                : $data['updated_at']?->format('Y-m-d H:i:s');
        }

        // 状态文本
        $data['status_text'] = $data['status'] === SysMenu::STATUS_ENABLED ? '启用' : '禁用';
        $data['visible_text'] = $data['visible'] === SysMenu::VISIBLE_SHOWN ? '显示' : '隐藏';
        $data['menu_type_name'] = $this->getMenuTypeName($data['menu_type'] ?? 1);

        return $data;
    }

    /**
     * 获取菜单类型名称
     *
     * @param int $menuType 菜单类型
     * @return string
     */
    protected function getMenuTypeName(int $menuType): string
    {
        return match ($menuType) {
            SysMenu::TYPE_DIRECTORY => '目录',
            SysMenu::TYPE_MENU => '菜单',
            SysMenu::TYPE_BUTTON => '按钮',
            SysMenu::TYPE_LINK => '外链',
            default => '未知',
        };
    }

    /**
     * 构建树形结构
     *
     * @param array $items    数据列表
     * @param int   $parentId 父ID
     * @return array
     */
    protected function buildTree(array $items, int $parentId = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ((int)$item['parent_id'] === $parentId) {
                $children = $this->buildTree($items, (int)$item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
