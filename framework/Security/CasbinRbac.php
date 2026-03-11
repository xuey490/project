<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Security;

use Casbin\Enforcer;
use Casbin\Model\Model;
use Casbin\Adapter\MySQL\Adapter;

/**
 * 基于 Casbin 的 RBAC（基于角色的访问控制）服务类.
 *
 * 该类封装了 Casbin 权限管理功能，支持用户-角色绑定、角色权限策略管理以及权限校验。
 * 复用框架的数据库连接和缓存服务，支持多租户场景下的权限隔离。
 *
 * 主要功能：
 * - 用户与角色的绑定/解绑
 * - 角色权限策略的添加
 * - 权限校验（基于用户、资源、操作）
 * - 获取用户角色列表
 * - 获取角色权限策略列表
 *
 * @package Framework\Security
 */
class CasbinRbac
{
    /**
     * Casbin 权限执行器实例.
     *
     * @var Enforcer
     */
    protected $enforcer;

    /**
     * 构造函数，初始化 Casbin RBAC 服务.
     *
     * 根据配置初始化 Casbin 模型、MySQL 适配器和缓存机制。
     * 通过框架容器复用数据库连接和缓存服务。
     *
     * @param array $config Casbin 配置数组，包含以下键：
     *                      - model.config_text: RBAC 模型规则文本
     *                      - adapter.table_name: 策略存储表名
     *                      - cache.enable: 是否启用缓存
     *                      - cache.key_prefix: 缓存键前缀
     *                      - cache.ttl: 缓存过期时间（秒）
     */
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
     * 绑定用户与角色的关联关系.
     *
     * 将指定用户分配到特定角色，支持多租户场景下的角色隔离。
     *
     * @param string|int $userId   用户ID，可以是字符串或整数类型
     * @param string|int $roleId   角色ID，可以是字符串或整数类型
     * @param string|int $tenantId 租户ID，用于多租户场景，默认为 'default'
     *
     * @return bool 绑定成功返回 true，失败返回 false
     */
    public function bindUserRole($userId, $roleId, $tenantId = 'default'): bool
    {
        return $this->enforcer->addGroupingPolicy($userId, $roleId, $tenantId);
    }

    /**
     * 解绑用户与角色的关联关系.
     *
     * 移除指定用户与角色的绑定关系，支持多租户场景。
     *
     * @param string|int $userId   用户ID
     * @param string|int $roleId   角色ID
     * @param string|int $tenantId 租户ID，默认为 'default'
     *
     * @return bool 解绑成功返回 true，失败返回 false
     */
    public function unbindUserRole($userId, $roleId, $tenantId = 'default'): bool
    {
        return $this->enforcer->removeGroupingPolicy($userId, $roleId, $tenantId);
    }

    /**
     * 添加角色权限策略.
     *
     * 为指定角色添加对特定资源的操作权限。
     *
     * @param string|int $roleId   角色ID
     * @param string     $resource 资源路径，如 '/api/user' 或 'user'
     * @param string     $action   操作方法，如 'GET'、'POST'、'PUT'、'DELETE'
     * @param string|int $tenantId 租户ID，默认为 'default'
     *
     * @return bool 添加成功返回 true，失败返回 false
     */
    public function addRolePolicy($roleId, string $resource, string $action, $tenantId = 'default'): bool
    {
        return $this->enforcer->addPolicy($roleId, $resource, $action, $tenantId);
    }

    /**
     * 权限校验核心方法.
     *
     * 检查指定用户是否有权限对特定资源执行指定操作。
     * 基于 Casbin 的 RBAC 模型进行权限判断。
     *
     * @param string|int $userId   用户ID
     * @param string     $resource 资源路径
     * @param string     $action   操作方法（如 GET、POST 等）
     * @param string|int $tenantId 租户ID，默认为 'default'
     *
     * @return bool 有权限返回 true，无权限返回 false
     */
    public function checkPermission($userId, string $resource, string $action, $tenantId = 'default'): bool
    {
        return $this->enforcer->enforce($userId, $resource, $action, $tenantId);
    }

    /**
     * 获取用户的所有角色列表.
     *
     * 查询指定用户在特定租户下绑定的所有角色。
     *
     * @param string|int $userId   用户ID
     * @param string|int $tenantId 租户ID，默认为 'default'
     *
     * @return array 角色ID数组，包含用户所拥有的所有角色
     */
    public function getUserRoles($userId, $tenantId = 'default'): array
    {
        return $this->enforcer->getRolesForUser($userId, $tenantId);
    }

    /**
     * 获取角色的所有权限策略.
     *
     * 查询指定角色在特定租户下的所有权限策略配置。
     *
     * @param string|int $roleId   角色ID
     * @param string|int $tenantId 租户ID，默认为 'default'
     *
     * @return array 权限策略数组，每个元素包含资源路径和操作方法
     */
    public function getRolePolicies($roleId, $tenantId = 'default'): array
    {
        return $this->enforcer->getFilteredPolicy(0, $roleId, '', '', $tenantId);
    }
}
