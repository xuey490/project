<?php

namespace App\Core\Auth;

use Casbin\Enforcer;
use Casbin\Model\Model;
use Casbin\Adapter\MySQL\Adapter;

/**
 * 基于自研框架容器的 Casbin RBAC 服务
 * 复用 App('db') 数据库连接 和 App('cache') 缓存
 */
class CasbinRbac
{
    /** @var Enforcer */
    protected $enforcer;

    public function __construct(array $config)
    {
        // 1. 加载 RBAC 模型规则
        $model = new Model();
        $model->loadModelText($config['model']['config_text']);

        // 2. 从 App('db') 获取 PDO 连接，初始化 Casbin MySQL 适配器
        $db = App('db');
        // 假设 App('db') 提供 getPdo() 方法获取 PDO 实例（需根据自研框架调整）
        $pdo = $db->getPdo();
        // 直接传入 PDO 实例 + 策略表名，复用框架数据库连接
        $adapter = new Adapter($pdo, $config['adapter']['table_name']);

        // 3. 创建 Casbin 执行器
        $this->enforcer = new Enforcer($model, $adapter);

        // 4. 开启缓存，使用自定义缓存适配器（复用 App('cache')）
        if ($config['cache']['enable']) {
            $this->enforcer->enableCache();
            $cacheAdapter = new CasbinCacheAdapter(
                $config['cache']['key_prefix'],
                $config['cache']['ttl']
            );
            $this->enforcer->setCacheAdapter($cacheAdapter);
            // 开启自动刷新缓存（权限变更时自动清除缓存）
            $this->enforcer->setAutoRefreshCache(true);
        }
    }

    /**
     * 绑定用户与角色
     * @param string|int $userId 用户ID
     * @param string|int $roleId 角色ID
     * @param string|int $tenantId 租户ID（多租户必填）
     * @return bool
     */
    public function bindUserRole($userId, $roleId, $tenantId = 'default'): bool
    {
        return $this->enforcer->addGroupingPolicy($userId, $roleId, $tenantId);
    }

    /**
     * 解绑用户与角色
     * @param string|int $userId 用户ID
     * @param string|int $roleId 角色ID
     * @param string|int $tenantId 租户ID
     * @return bool
     */
    public function unbindUserRole($userId, $roleId, $tenantId = 'default'): bool
    {
        return $this->enforcer->removeGroupingPolicy($userId, $roleId, $tenantId);
    }

    /**
     * 添加角色权限策略
     * @param string|int $roleId 角色ID
     * @param string $resource 资源路径（如/api/user）
     * @param string $action 请求方法（如GET/POST）
     * @param string|int $tenantId 租户ID
     * @return bool
     */
    public function addRolePolicy($roleId, string $resource, string $action, $tenantId = 'default'): bool
    {
        return $this->enforcer->addPolicy($roleId, $resource, $action, $tenantId);
    }

    /**
     * 权限校验核心方法
     * @param string|int $userId 用户ID
     * @param string $resource 资源路径
     * @param string $action 请求方法
     * @param string|int $tenantId 租户ID
     * @return bool
     */
    public function checkPermission($userId, string $resource, string $action, $tenantId = 'default'): bool
    {
        return $this->enforcer->enforce($userId, $resource, $action, $tenantId);
    }

    /**
     * 获取用户的所有角色
     * @param string|int $userId 用户ID
     * @param string|int $tenantId 租户ID
     * @return array
     */
    public function getUserRoles($userId, $tenantId = 'default'): array
    {
        return $this->enforcer->getRolesForUser($userId, $tenantId);
    }

    /**
     * 获取角色的所有权限策略
     * @param string|int $roleId 角色ID
     * @param string|int $tenantId 租户ID
     * @return array
     */
    public function getRolePolicies($roleId, $tenantId = 'default'): array
    {
        return $this->enforcer->getFilteredPolicy(0, $roleId, '', '', $tenantId);
    }
}
