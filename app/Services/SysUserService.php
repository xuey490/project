<?php

declare(strict_types=1);

/**
 * 系统用户服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysUser;
use App\Models\SysUserRole;
use App\Models\SysUserMenu;
use App\Dao\SysUserDao;
use App\Services\Casbin\CasbinService;
use Framework\Basic\BaseService;
use Illuminate\Support\Facades\Hash;

/**
 * SysUserService 用户服务
 *
 * 处理用户相关的业务逻辑
 */
class SysUserService extends BaseService
{
    /**
     * DAO 实例
     * @var SysUserDao
     */
    protected SysUserDao $userDao;

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
        $this->userDao = new SysUserDao();
        $this->casbinService = new CasbinService();
    }

    // ==================== 用户认证 ====================

    /**
     * 用户登录
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $ip       登录 IP
     * @return array|null 成功返回用户信息和 token，失败返回 null
     */
    public function login(string $username, string $password, string $ip = ''): ?array
    {
        // 查找用户
        $user = $this->userDao->findByUsername($username);

        if (!$user) {
            return null;
        }

        // 检查用户状态
        if ($user->status === SysUser::STATUS_DISABLED) {
            return null;
        }

        // 验证密码
        if (!Hash::check($password, $user->password)) {
            return null;
        }

        // 更新最后登录信息
        $this->userDao->updateLoginInfo($user->id, $ip);

        // 同步用户角色到 Casbin
        $this->casbinService->syncUserRolesFromDatabase($user->id);

        // 生成 JWT Token
        $token = $this->generateJwtToken($user);

        // 获取用户菜单
        $menus = $user->getMenuTree();
        $permissions = $user->getPermissions();

        return [
            'user' => $this->formatUser($user),
            'token' => $token,
            'menus' => $menus,
            'permissions' => $permissions,
        ];
    }

    /**
     * 生成 JWT Token
     *
     * @param SysUser $user 用户
     * @return string
     */
    protected function generateJwtToken(SysUser $user): string
    {
        $jwt = app('jwt');
        $roles = $user->getRoleCodes();
        $primaryRole = $roles[0] ?? 'user';

        $tokenData = $jwt->issue([
            'uid' => $user->id,
            'username' => $user->username,
            'role' => $primaryRole,
            'roles' => $roles,
        ]);

        return $tokenData['token'];
    }

    // ==================== 用户管理 ====================

    /**
     * 获取用户列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $username = $params['username'] ?? '';
        $status = $params['status'] ?? '';
        $deptId = $params['dept_id'] ?? '';

        $query = SysUser::query()->whereNull('deleted_at');

        if ($username !== '') {
            $query->where('username', 'like', "%{$username}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        if ($deptId !== '') {
            $query->where('dept_id', (int)$deptId);
        }

        $total = $query->count();
        // 优化：使用 Eloquent 标准的 skip/take 方法
        $list = $query->orderBy('id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get()
            ->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item = $this->formatUser($item);
        }

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取用户详情
     *
     * @param int $userId 用户 ID
     * @return array|null
     */
    public function getDetail(int $userId): ?array
    {
        $user = SysUser::find($userId);

        if (!$user) {
            return null;
        }

        $data = $this->formatUser($user);

        // 获取用户角色
        $data['roles'] = $user->roles->toArray();
        $data['role_ids'] = $user->getRoleIds();

        // 获取用户个人菜单
        $data['menu_ids'] = SysUserMenu::getMenuIdsByUserId($userId);

        return $data;
    }

    /**
     * 创建用户
     *
     * @param array $data     用户数据
     * @param int   $operator 操作人 ID
     * @return SysUser|null
     */
    public function create(array $data, int $operator = 0): ?SysUser
    {
        return $this->transaction(function () use ($data, $operator) {
            // 检查用户名是否存在
            if ($this->userDao->isUsernameExists($data['username'])) {
                throw new \Exception('用户名已存在');
            }

            // 检查手机号是否存在
            if (!empty($data['mobile']) && $this->userDao->isMobileExists($data['mobile'])) {
                throw new \Exception('手机号已存在');
            }

            // 设置审计字段
            $data['created_by'] = $operator;
            $data['updated_by'] = $operator;

            // 加密密码
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                $data['password'] = Hash::make('123456'); // 默认密码
            }

            // 创建用户
            $user = SysUser::create($data);

            // 分配角色
            if (!empty($data['role_ids'])) {
                SysUserRole::syncUserRoles($user->id, $data['role_ids'], $operator);
            }

            // 分配个人菜单
            if (!empty($data['menu_ids'])) {
                SysUserMenu::syncUserMenus($user->id, $data['menu_ids'], $operator);
            }

            return $user;
        });
    }

    /**
     * 更新用户
     *
     * @param int   $userId   用户 ID
     * @param array $data     用户数据
     * @param int   $operator 操作人 ID
     * @return bool
     */
    public function update(int $userId, array $data, int $operator = 0): bool
    {
        return $this->transaction(function () use ($userId, $data, $operator) {
            $user = SysUser::find($userId);
            if (!$user) {
                throw new \Exception('用户不存在');
            }

            // 检查用户名是否重复
            if (isset($data['username']) && $data['username'] !== $user->username) {
                if ($this->userDao->isUsernameExists($data['username'], $userId)) {
                    throw new \Exception('用户名已存在');
                }
            }

            // 检查手机号是否重复
            if (isset($data['mobile']) && $data['mobile'] !== $user->mobile) {
                if ($this->userDao->isMobileExists($data['mobile'], $userId)) {
                    throw new \Exception('手机号已存在');
                }
            }

            // 设置审计字段
            $data['updated_by'] = $operator;

            // 如果修改密码，需要加密
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // 更新用户
            $user->fill($data);
            $user->save();

            // 更新角色
            if (isset($data['role_ids'])) {
                SysUserRole::syncUserRoles($userId, $data['role_ids'], $operator);
                // 同步 Casbin
                $this->casbinService->syncUserRolesFromDatabase($userId);
            }

            // 更新个人菜单
            if (isset($data['menu_ids'])) {
                SysUserMenu::syncUserMenus($userId, $data['menu_ids'], $operator);
            }

            return true;
        });
    }

    /**
     * 删除用户
     *
     * @param int $userId 用户 ID
     * @return bool
     */
    public function delete(int $userId): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            return false;
        }

        // 软删除用户
        $user->delete();

        // 删除用户角色关联
        SysUserRole::deleteByUserId($userId);

        // 删除用户菜单关联
        SysUserMenu::deleteByUserId($userId);

        // 清除 Casbin 角色
        $this->casbinService->deleteRolesForUser($userId);

        return true;
    }

    /**
     * 更新用户状态
     *
     * @param int $userId 用户 ID
     * @param int $status 状态
     * @return bool
     */
    public function updateStatus(int $userId, int $status): bool
    {
        return $this->userDao->updateStatus($userId, $status);
    }

    /**
     * 重置密码
     *
     * @param int    $userId   用户 ID
     * @param string $password 新密码
     * @return bool
     */
    public function resetPassword(int $userId, string $password = '123456'): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            return false;
        }

        $user->password = Hash::make($password);
        return $user->save();
    }

    /**
     * 修改密码
     *
     * @param int    $userId      用户 ID
     * @param string $oldPassword 旧密码
     * @param string $newPassword 新密码
     * @return bool
     * @throws \Exception
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): bool
    {
        $user = SysUser::find($userId);
        if (!$user) {
            throw new \Exception('用户不存在');
        }

        // 验证旧密码
        if (!Hash::check($oldPassword, $user->password)) {
            throw new \Exception('旧密码错误');
        }

        $user->password = Hash::make($newPassword);
        return $user->save();
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化用户数据
     *
     * @param SysUser|array $user 用户
     * @return array
     */
    protected function formatUser(SysUser|array $user): array
    {
        if ($user instanceof SysUser) {
            $data = $user->toArray();
        } else {
            $data = $user;
        }

        // 移除敏感字段
        unset($data['password']);

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
        $data['status_text'] = $data['status'] === SysUser::STATUS_ENABLED ? '启用' : '禁用';

        return $data;
    }
}