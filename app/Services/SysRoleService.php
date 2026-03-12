<?php

declare(strict_types=1);

/**
 * 系统角色服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysRole;
use App\Models\SysRoleMenu;
use App\Models\SysUserRole;
use App\Dao\SysRoleDao;
use App\Services\Casbin\CasbinService;
use Framework\Basic\BaseService;

/**
 * SysRoleService 角色服务
 *
 * 处理角色相关的业务逻辑
 */
class SysRoleService extends BaseService
{
    /**
     * DAO 实例
     * @var SysRoleDao
     */
    protected SysRoleDao $roleDao;

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
        $this->roleDao = new SysRoleDao();
        $this->casbinService = new CasbinService();
    }

    /**
     * 获取角色列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $roleName = $params['role_name'] ?? '';
        $roleCode = $params['role_code'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysRole::query()->whereNull('deleted_at');

        if ($roleName !== '') {
            $query->where('role_name', 'like', "%{$roleName}%");
        }

        if ($roleCode !== '') {
            $query->where('role_code', 'like', "%{$roleCode}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $total = $query->count();
        $list = $query->orderBy('sort')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item = $this->formatRole($item);
        }

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取所有启用的角色
     *
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->roleDao->getAllEnabled();
    }

    /**
     * 获取角色树
     *
     * @return array
     */
    public function getRoleTree(): array
    {
        return SysRole::getRoleTree();
    }

    /**
     * 获取角色详情
     *
     * @param int $roleId 角色ID
     * @return array|null
     */
    public function getDetail(int $roleId): ?array
    {
        $role = SysRole::find($roleId);

        if (!$role) {
            return null;
        }

        $data = $this->formatRole($role);
        $data['menu_ids'] = SysRoleMenu::getMenuIdsByRoleId($roleId);

        return $data;
    }

    /**
     * 创建角色
     *
     * @param array $data     角色数据
     * @param int   $operator 操作人ID
     * @return SysRole|null
     */
    public function create(array $data, int $operator = 0): ?SysRole
    {
        return $this->transaction(function () use ($data, $operator) {
            // 检查角色编码是否存在
            if ($this->roleDao->isRoleCodeExists($data['role_code'])) {
                throw new \Exception('角色编码已存在');
            }

            // 设置审计字段
            $data['created_by'] = $operator;
            $data['updated_by'] = $operator;

            // 创建角色
            $role = SysRole::create($data);

            // 分配菜单
            if (!empty($data['menu_ids'])) {
                SysRoleMenu::syncRoleMenus($role->id, $data['menu_ids'], $operator);
            }

            return $role;
        });
    }

    /**
     * 更新角色
     *
     * @param int   $roleId   角色ID
     * @param array $data     角色数据
     * @param int   $operator 操作人ID
     * @return bool
     */
    public function update(int $roleId, array $data, int $operator = 0): bool
    {
        return $this->transaction(function () use ($roleId, $data, $operator) {
            $role = SysRole::find($roleId);
            if (!$role) {
                throw new \Exception('角色不存在');
            }

            // 检查角色编码是否重复
            if (isset($data['role_code']) && $data['role_code'] !== $role->role_code) {
                if ($this->roleDao->isRoleCodeExists($data['role_code'], $roleId)) {
                    throw new \Exception('角色编码已存在');
                }
            }

            // 设置审计字段
            $data['updated_by'] = $operator;

            // 更新角色
            $role->fill($data);
            $role->save();

            // 更新菜单
            if (isset($data['menu_ids'])) {
                SysRoleMenu::syncRoleMenus($roleId, $data['menu_ids'], $operator);
                // 同步 Casbin 权限
                $this->casbinService->syncRoleMenuPermissions($roleId);
            }

            return true;
        });
    }

    /**
     * 删除角色
     *
     * @param int $roleId 角色ID
     * @return bool
     */
    public function delete(int $roleId): bool
    {
        $role = SysRole::find($roleId);
        if (!$role) {
            return false;
        }

        // 检查是否有关联用户
        $userCount = SysUserRole::where('role_id', $roleId)->count();
        if ($userCount > 0) {
            throw new \Exception('该角色下存在用户，无法删除');
        }

        // 软删除角色
        $role->delete();

        // 删除角色菜单关联
        SysRoleMenu::deleteByRoleId($roleId);

        // 删除 Casbin 权限
        $this->casbinService->deletePermissionsForRole($role->role_code);

        return true;
    }

    /**
     * 更新角色状态
     *
     * @param int $roleId 角色ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $roleId, int $status): bool
    {
        return $this->roleDao->updateStatus($roleId, $status);
    }

    /**
     * 获取角色菜单ID列表
     *
     * @param int $roleId 角色ID
     * @return array
     */
    public function getMenuIds(int $roleId): array
    {
        return SysRoleMenu::getMenuIdsByRoleId($roleId);
    }

    /**
     * 分配菜单给角色
     *
     * @param int   $roleId  角色ID
     * @param array $menuIds 菜单ID数组
     * @param int   $operator 操作人ID
     * @return bool
     */
    public function assignMenus(int $roleId, array $menuIds, int $operator = 0): bool
    {
        SysRoleMenu::syncRoleMenus($roleId, $menuIds, $operator);

        // 同步 Casbin 权限
        $this->casbinService->syncRoleMenuPermissions($roleId);

        return true;
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化角色数据
     *
     * @param SysRole|array $role 角色
     * @return array
     */
    protected function formatRole(SysRole|array $role): array
    {
        if ($role instanceof SysRole) {
            $data = $role->toArray();
        } else {
            $data = $role;
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
        $data['status_text'] = $data['status'] === SysRole::STATUS_ENABLED ? '启用' : '禁用';

        return $data;
    }
}
