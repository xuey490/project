<?php

declare(strict_types=1);

/**
 * Casbin 服务
 *
 * @package App\Services\Casbin
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services\Casbin;

use Casbin\Enforcer;
use Casbin\Model\Model;
use App\Models\SysUser;
use App\Models\SysRole;
use App\Models\SysMenu;
use App\Models\SysUserRole;
use App\Models\SysRoleMenu;
use App\Models\SysUserMenu;
use Illuminate\Support\Facades\Cache;

/**
 * CasbinService Casbin 权限服务
 *
 * 提供权限验证、策略管理等功能
 */
class CasbinService
{
    /**
     * Enforcer 实例
     * @var Enforcer|null
     */
    protected ?Enforcer $enforcer = null;

    /**
     * 配置
     * @var array
     */
    protected array $config;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->config = config('casbin', []);
    }

    /**
     * 获取 Enforcer 实例
     *
     * @return Enforcer
     */
    public function getEnforcer(): Enforcer
    {
        if ($this->enforcer === null) {
            $this->enforcer = $this->createEnforcer();
        }

        return $this->enforcer;
    }

    /**
     * 创建 Enforcer 实例
     *
     * @return Enforcer
     */
    protected function createEnforcer(): Enforcer
    {
        // 创建模型
        $model = new Model();

        // 加载模型配置
        $modelPath = $this->config['model']['path'] ?? config_path('casbin_rbac_model.conf');

        if (file_exists($modelPath)) {
            $model->loadModel($modelPath);
        } elseif (!empty($this->config['model']['content'])) {
            $model->loadModelFromText($this->config['model']['content']);
        } else {
            // 使用默认 RBAC 模型
            $model->loadModelFromText($this->getDefaultModelText());
        }

        // 创建适配器
        $tableName = $this->config['adapter']['table_name'] ?? 'casbin_rule';
        $connection = $this->config['adapter']['connection'] ?? null;
        $adapter = new DatabaseAdapter($tableName, $connection);

        // 创建 Enforcer
        return new Enforcer($model, $adapter);
    }

    /**
     * 获取默认 RBAC 模型文本
     *
     * @return string
     */
    protected function getDefaultModelText(): string
    {
        return <<<'EOT'
[request_definition]
r = sub, obj, act

[policy_definition]
p = sub, obj, act

[role_definition]
g = _, _
g2 = _, _

[policy_effect]
e = some(where (p.eft == allow))

[matchers]
m = g(r.sub, p.sub) && (keyMatch2(r.obj, p.obj) || keyMatch(r.obj, p.obj)) && (r.act == p.act || p.act == "*")
EOT;
    }

    // ==================== 权限验证 ====================

    /**
     * 检查用户是否有权限
     *
     * @param int|string $user    用户ID或角色编码
     * @param string     $resource 资源 (如: /api/user/list)
     * @param string     $action   操作 (如: GET, POST, *)
     * @return bool
     */
    public function checkPermission(int|string $user, string $resource, string $action = '*'): bool
    {
        // 检查缓存
        $cacheKey = $this->getCacheKey('permission', $user, $resource, $action);

        if ($this->isCacheEnabled()) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        // 执行权限检查
        $result = $this->getEnforcer()->enforce((string)$user, $resource, $action);

        // 缓存结果
        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $result, $this->getCacheTtl());
        }

        return $result;
    }

    /**
     * 检查用户是否超级管理员
     *
     * @param int $userId 用户ID
     * @return bool
     */
    public function isSuperAdmin(int $userId): bool
    {
        $user = SysUser::find($userId);
        return $user && $user->isSuperAdmin();
    }

    /**
     * 获取用户的所有角色
     *
     * @param int $userId 用户ID
     * @return array
     */
    public function getRolesForUser(int $userId): array
    {
        return $this->getEnforcer()->getRolesForUser((string)$userId);
    }

    /**
     * 获取角色的所有权限
     *
     * @param string $role 角色编码
     * @return array
     */
    public function getPermissionsForRole(string $role): array
    {
        return $this->getEnforcer()->getPermissionsForUser($role);
    }

    // ==================== 角色管理 ====================

    /**
     * 添加角色给用户
     *
     * @param int    $userId   用户ID
     * @param string $roleCode 角色编码
     * @return bool
     */
    public function addRoleForUser(int $userId, string $roleCode): bool
    {
        $result = $this->getEnforcer()->addRoleForUser((string)$userId, $roleCode);
        $this->clearUserCache($userId);

        return $result;
    }

    /**
     * 删除用户的角色
     *
     * @param int    $userId   用户ID
     * @param string $roleCode 角色编码
     * @return bool
     */
    public function deleteRoleForUser(int $userId, string $roleCode): bool
    {
        $result = $this->getEnforcer()->deleteRoleForUser((string)$userId, $roleCode);
        $this->clearUserCache($userId);

        return $result;
    }

    /**
     * 删除用户的所有角色
     *
     * @param int $userId 用户ID
     * @return bool
     */
    public function deleteRolesForUser(int $userId): bool
    {
        $result = $this->getEnforcer()->deleteRolesForUser((string)$userId);
        $this->clearUserCache($userId);

        return $result;
    }

    /**
     * 同步用户角色 (从数据库)
     *
     * @param int $userId 用户ID
     * @return void
     */
    public function syncUserRolesFromDatabase(int $userId): void
    {
        // 先清除 Casbin 中的用户角色
        $this->deleteRolesForUser($userId);

        // 从数据库获取用户角色
        $roles = SysUserRole::where('user_id', $userId)
            ->join('sys_role', 'sys_user_role.role_id', '=', 'sys_role.id')
            ->where('sys_role.status', SysRole::STATUS_ENABLED)
            ->pluck('sys_role.role_code')
            ->toArray();

        // 添加到 Casbin
        foreach ($roles as $roleCode) {
            $this->addRoleForUser($userId, $roleCode);
        }

        $this->clearUserCache($userId);
    }

    // ==================== 权限策略管理 ====================

    /**
     * 添加权限策略
     *
     * @param string $role     角色编码
     * @param string $resource 资源
     * @param string $action   操作
     * @return bool
     */
    public function addPermission(string $role, string $resource, string $action = '*'): bool
    {
        return $this->getEnforcer()->addPolicy($role, $resource, $action);
    }

    /**
     * 删除权限策略
     *
     * @param string $role     角色编码
     * @param string $resource 资源
     * @param string $action   操作
     * @return bool
     */
    public function deletePermission(string $role, string $resource, string $action = '*'): bool
    {
        return $this->getEnforcer()->deletePolicy($role, $resource, $action);
    }

    /**
     * 删除角色的所有权限
     *
     * @param string $role 角色编码
     * @return bool
     */
    public function deletePermissionsForRole(string $role): bool
    {
        return $this->getEnforcer()->deletePermissionsForUser($role);
    }

    /**
     * 同步角色菜单权限 (从数据库)
     *
     * @param int $roleId 角色ID
     * @return void
     */
    public function syncRoleMenuPermissions(int $roleId): void
    {
        $role = SysRole::find($roleId);
        if (!$role) {
            return;
        }

        // 先清除该角色的所有权限
        $this->deletePermissionsForRole($role->role_code);

        // 获取角色菜单
        $menuIds = SysRoleMenu::getMenuIdsByRoleId($roleId);

        if (empty($menuIds)) {
            return;
        }

        // 获取菜单的权限标识
        $menus = SysMenu::whereIn('id', $menuIds)
            ->where('status', SysMenu::STATUS_ENABLED)
            ->where('permission', '!=', '')
            ->get();

        // 添加权限策略
        foreach ($menus as $menu) {
            // 解析权限标识，格式如: system:user:list -> /api/system/user, GET
            $this->addPermissionFromMenu($role->role_code, $menu);
        }

        // 清除该角色下所有用户的缓存
        $userIds = SysUserRole::where('role_id', $roleId)->pluck('user_id')->toArray();
        foreach ($userIds as $userId) {
            $this->clearUserCache($userId);
        }
    }

    /**
     * 从菜单添加权限策略
     *
     * @param string  $roleCode 角色编码
     * @param SysMenu $menu     菜单
     * @return void
     */
    protected function addPermissionFromMenu(string $roleCode, SysMenu $menu): void
    {
        // 根据菜单类型生成权限策略
        $permission = $menu->permission;
        $path = $menu->path;

        // 如果有自定义权限标识，解析使用
        if ($permission) {
            // 简单处理：将权限标识转换为 API 路径
            // system:user:list -> /api/system/user/list
            // system:user:add -> /api/system/user, POST
            $parts = explode(':', $permission);

            if (count($parts) >= 3) {
                $module = $parts[0];
                $controller = $parts[1];
                $action = $parts[2];

                // 根据动作确定 HTTP 方法
                $httpMethod = $this->getHttpMethod($action);
                $apiPath = "/api/{$module}/{$controller}";

                // 添加权限策略
                $this->addPermission($roleCode, $apiPath, $httpMethod);

                // 对于查询接口，同时添加列表路径
                if (in_array($action, ['list', 'query'])) {
                    $this->addPermission($roleCode, $apiPath . '/list', 'GET');
                    $this->addPermission($roleCode, $apiPath . '/*', 'GET');
                }
            }
        }

        // 如果有路由路径，直接添加
        if ($path && str_starts_with($path, '/')) {
            $this->addPermission($roleCode, $path, '*');

            // 对于菜单，添加其子路径权限
            if ($menu->isMenu()) {
                $this->addPermission($roleCode, $path . '/*', '*');
            }
        }
    }

    /**
     * 根据动作获取 HTTP 方法
     *
     * @param string $action 动作
     * @return string
     */
    protected function getHttpMethod(string $action): string
    {
        return match ($action) {
            'list', 'query', 'get', 'detail', 'info' => 'GET',
            'add', 'create', 'insert' => 'POST',
            'edit', 'update', 'modify' => 'PUT',
            'delete', 'remove', 'destroy' => 'DELETE',
            default => '*',
        };
    }

    // ==================== 缓存管理 ====================

    /**
     * 清除用户权限缓存
     *
     * @param int $userId 用户ID
     * @return void
     */
    public function clearUserCache(int $userId): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $prefix = $this->getCachePrefix();
        $pattern = "{$prefix}permission:{$userId}:*";

        // 清除 Redis 缓存
        if ($this->config['cache']['driver'] === 'redis') {
            $redis = Cache::getRedis();
            $keys = $redis->keys($pattern);
            foreach ($keys as $key) {
                $redis->del($key);
            }
        }
    }

    /**
     * 清除所有权限缓存
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        if (!$this->isCacheEnabled()) {
            return;
        }

        $prefix = $this->getCachePrefix();

        if ($this->config['cache']['driver'] === 'redis') {
            $redis = Cache::getRedis();
            $keys = $redis->keys("{$prefix}*");
            foreach ($keys as $key) {
                $redis->del($key);
            }
        } else {
            Cache::flush();
        }
    }

    /**
     * 重新加载策略
     *
     * @return void
     */
    public function reloadPolicy(): void
    {
        $this->getEnforcer()->loadPolicy();
        $this->clearAllCache();
    }

    // ==================== 辅助方法 ====================

    /**
     * 检查缓存是否启用
     *
     * @return bool
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    /**
     * 获取缓存前缀
     *
     * @return string
     */
    protected function getCachePrefix(): string
    {
        return $this->config['cache']['prefix'] ?? 'casbin:';
    }

    /**
     * 获取缓存过期时间
     *
     * @return int
     */
    protected function getCacheTtl(): int
    {
        return $this->config['cache']['ttl'] ?? 3600;
    }

    /**
     * 生成缓存键
     *
     * @param mixed ...$args 参数
     * @return string
     */
    protected function getCacheKey(...$args): string
    {
        return $this->getCachePrefix() . implode(':', $args);
    }
}
