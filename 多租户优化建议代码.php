<?php

declare(strict_types=1);

/**
 * 多租户功能优化建议代码
 * 
 * 本文件包含针对现有代码的优化建议实现
 * 可以直接复制到项目中使用
 */

// ==================== 1. Workerman 安全的 TenantContext ====================

namespace Framework\Tenant;

use Workerman\Protocols\Http\Request;

/**
 * Workerman 安全的租户上下文管理器
 * 
 * 优化点：
 * 1. 使用 Request 对象存储租户状态，避免静态变量污染
 * 2. 每个请求独立，请求结束后自动清理
 * 3. 支持中间件注入 Request 对象
 */
final class SafeTenantContext
{
    /**
     * 请求属性键名
     */
    private const TENANT_ID_KEY = '_tenant_id';
    private const IGNORE_TENANT_KEY = '_ignore_tenant';
    
    /**
     * 当前请求实例
     */
    private static ?Request $currentRequest = null;
    
    /**
     * 设置当前请求实例（由中间件调用）
     * 
     * @param Request $request 当前 HTTP 请求
     */
    public static function setRequest(Request $request): void
    {
        self::$currentRequest = $request;
    }
    
    /**
     * 设置租户ID（存储在 Request 中）
     * 
     * @param int|null $tenantId 租户ID
     */
    public static function setTenantId(?int $tenantId): void
    {
        if (self::$currentRequest !== null) {
            self::$currentRequest->{self::TENANT_ID_KEY} = $tenantId;
        }
    }
    
    /**
     * 获取租户ID（从 Request 中读取）
     * 
     * @return int|null 租户ID
     */
    public static function getTenantId(): ?int
    {
        if (self::$currentRequest === null) {
            return null;
        }
        return self::$currentRequest->{self::TENANT_ID_KEY} ?? null;
    }
    
    /**
     * 忽略租户隔离（超管模式）
     */
    public static function ignore(): void
    {
        if (self::$currentRequest !== null) {
            self::$currentRequest->{self::IGNORE_TENANT_KEY} = true;
        }
    }
    
    /**
     * 恢复租户隔离
     */
    public static function restore(): void
    {
        if (self::$currentRequest !== null) {
            self::$currentRequest->{self::IGNORE_TENANT_KEY} = false;
        }
    }
    
    /**
     * 是否应用租户隔离
     * 
     * @return bool 需要应用隔离返回 true
     */
    public static function shouldApplyTenant(): bool
    {
        if (self::$currentRequest === null) {
            return false;
        }
        
        $ignore = self::$currentRequest->{self::IGNORE_TENANT_KEY} ?? false;
        $tenantId = self::$currentRequest->{self::TENANT_ID_KEY} ?? null;
        
        return !$ignore && $tenantId !== null;
    }
    
    /**
     * 检查当前是否处于忽略租户隔离状态
     * 
     * @return bool 正在忽略返回 true
     */
    public static function isIgnoring(): bool
    {
        if (self::$currentRequest === null) {
            return false;
        }
        return self::$currentRequest->{self::IGNORE_TENANT_KEY} ?? false;
    }
    
    /**
     * 在忽略租户隔离的作用域内安全执行回调函数
     * 
     * @param callable $fn 要执行的回调函数
     * @return mixed 回调函数的返回值
     */
    public static function withIgnore(callable $fn)
    {
        if (self::$currentRequest === null) {
            return $fn();
        }
        
        $prev = self::$currentRequest->{self::IGNORE_TENANT_KEY} ?? false;
        self::$currentRequest->{self::IGNORE_TENANT_KEY} = true;
        
        try {
            return $fn();
        } finally {
            self::$currentRequest->{self::IGNORE_TENANT_KEY} = $prev;
        }
    }
    
    /**
     * 清理上下文（请求结束时调用）
     */
    public static function clear(): void
    {
        self::$currentRequest = null;
    }
    
    /**
     * 获取最大影响行数限制
     * 
     * @return int 最大影响行数
     */
    public static function maxAffectRows(): int
    {
        return 100; // 默认限制
    }
}

// ==================== 2. JWT Token 方案（推荐） ====================

namespace Framework\Tenant;

/**
 * JWT Token 租户上下文管理器
 * 
 * 优化点：
 * 1. 租户ID存储在 JWT Token 中，无状态
 * 2. 支持 Token 刷新时切换租户
 * 3. 不依赖 Request 对象，适合 API 场景
 */
final class JwtTenantContext
{
    /**
     * 从 JWT Token 解析租户ID
     * 
     * @param string $token JWT Token
     * @return int|null 租户ID
     */
    public static function getTenantIdFromToken(string $token): ?int
    {
        try {
            $payload = self::decodeToken($token);
            return $payload['tenant_id'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * 生成包含租户ID的 JWT Token
     * 
     * @param array $userData 用户数据
     * @param int $tenantId 租户ID
     * @param string $secret 密钥
     * @return string JWT Token
     */
    public static function generateToken(array $userData, int $tenantId, string $secret): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        $payload = json_encode(array_merge($userData, [
            'tenant_id' => $tenantId,
            'iat' => time(),
            'exp' => time() + 7200, // 2小时过期
        ]));
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * 解码 JWT Token
     * 
     * @param string $token JWT Token
     * @return array Payload 数据
     * @throws \Exception 解码失败时抛出
     */
    private static function decodeToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }
        
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        if (!$payload) {
            throw new \Exception('Invalid payload');
        }
        
        // 检查过期时间
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('Token expired');
        }
        
        return $payload;
    }
}

// ==================== 3. 优化的 SysUserRole 模型 ====================

namespace App\Models;

use Framework\Basic\BaseLaORMModel;

/**
 * 用户角色关联模型（优化版）
 * 
 * 优化点：
 * 1. 继承 BaseLaORMModel 获得完整功能
 * 2. 添加 tenant_id 字段支持多租户
 * 3. 提供按租户查询的方法
 */
class SysUserRoleOptimized extends BaseLaORMModel
{
    /**
     * 表名
     */
    protected $table = 'sys_user_role';
    
    /**
     * 主键
     */
    protected $primaryKey = 'id';
    
    /**
     * 可填充字段
     */
    protected $fillable = [
        'user_id',
        'role_id',
        'tenant_id',  // 新增：支持多租户
        'created_by',
        'updated_by',
    ];
    
    /**
     * 类型转换
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'role_id' => 'integer',
        'tenant_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * 是否自动维护时间戳
     */
    public $timestamps = true;
    
    // ==================== 业务方法 ====================
    
    /**
     * 获取用户在指定租户的角色ID列表
     * 
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @return array 角色ID列表
     */
    public static function getRoleIdsByTenant(int $userId, int $tenantId): array
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->pluck('role_id')
            ->toArray();
    }
    
    /**
     * 获取指定租户下的所有用户角色关联
     * 
     * @param int $tenantId 租户ID
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByTenant(int $tenantId)
    {
        return self::where('tenant_id', $tenantId)
            ->with(['user', 'role'])
            ->get();
    }
    
    /**
     * 批量插入用户角色关联（带租户ID）
     * 
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @param array $roleIds 角色ID数组
     * @param int $createdBy 创建人ID
     * @return bool
     */
    public static function batchInsertByTenant(
        int $userId, 
        int $tenantId, 
        array $roleIds, 
        int $createdBy = 0
    ): bool {
        if (empty($roleIds)) {
            return false;
        }
        
        $data = [];
        $now = now();
        
        foreach ($roleIds as $roleId) {
            $data[] = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'tenant_id' => $tenantId,
                'created_by' => $createdBy,
                'updated_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        return self::insert($data);
    }
    
    /**
     * 同步用户在指定租户的角色
     * 
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @param array $roleIds 角色ID数组
     * @param int $createdBy 创建人ID
     */
    public static function syncUserRolesByTenant(
        int $userId, 
        int $tenantId, 
        array $roleIds, 
        int $createdBy = 0
    ): void {
        // 只删除该租户下的角色关联
        self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->delete();
        
        // 插入新的角色关联
        if (!empty($roleIds)) {
            self::batchInsertByTenant($userId, $tenantId, $roleIds, $createdBy);
        }
    }
    
    /**
     * 删除用户的所有角色关联（跨租户）
     * 
     * @param int $userId 用户ID
     * @return bool
     */
    public static function deleteByUserId(int $userId): bool
    {
        return self::where('user_id', $userId)->delete() !== false;
    }
    
    /**
     * 删除用户的所有角色关联（指定租户）
     * 
     * @param int $userId 用户ID
     * @param int $tenantId 租户ID
     * @return bool
     */
    public static function deleteByUserIdAndTenant(int $userId, int $tenantId): bool
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->delete() !== false;
    }
    
    /**
     * 删除角色的所有用户关联
     * 
     * @param int $roleId 角色ID
     * @return bool
     */
    public static function deleteByRoleId(int $roleId): bool
    {
        return self::where('role_id', $roleId)->delete() !== false;
    }
    
    /**
     * 检查用户是否拥有指定角色
     * 
     * @param int $userId 用户ID
     * @param int $roleId 角色ID
     * @param int|null $tenantId 租户ID（为null则检查所有租户）
     * @return bool
     */
    public static function hasRole(int $userId, int $roleId, ?int $tenantId = null): bool
    {
        $query = self::where('user_id', $userId)
            ->where('role_id', $roleId);
        
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }
        
        return $query->exists();
    }
    
    // ==================== 关联关系 ====================
    
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(SysUser::class, 'user_id');
    }
    
    /**
     * 关联角色
     */
    public function role()
    {
        return $this->belongsTo(SysRole::class, 'role_id');
    }
    
    /**
     * 关联租户
     */
    public function tenant()
    {
        return $this->belongsTo(SysTenant::class, 'tenant_id');
    }
}

// ==================== 4. 租户中间件 ====================

namespace Framework\Middleware;

use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Framework\Tenant\SafeTenantContext;

/**
 * 租户上下文中间件
 * 
 * 功能：
 * 1. 从 JWT Token 或 Header 中解析租户ID
 * 2. 设置租户上下文
 * 3. 请求结束后清理上下文
 */
class TenantMiddleware
{
    /**
     * 处理请求
     * 
     * @param Request $request
     * @param callable $next
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        // 设置当前请求
        SafeTenantContext::setRequest($request);
        
        // 从 Token 中解析租户ID
        $tenantId = $this->resolveTenantId($request);
        
        if ($tenantId) {
            SafeTenantContext::setTenantId($tenantId);
        }
        
        // 执行后续中间件
        $response = $next($request);
        
        // 清理上下文
        SafeTenantContext::clear();
        
        return $response;
    }
    
    /**
     * 解析租户ID
     * 
     * @param Request $request
     * @return int|null
     */
    protected function resolveTenantId(Request $request): ?int
    {
        // 方式1：从 JWT Token 解析
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $tenantId = JwtTenantContext::getTenantIdFromToken($token);
            if ($tenantId) {
                return $tenantId;
            }
        }
        
        // 方式2：从自定义 Header 获取
        $tenantHeader = $request->header('X-Tenant-ID');
        if ($tenantHeader && is_numeric($tenantHeader)) {
            return (int) $tenantHeader;
        }
        
        // 方式3：从 Query 参数获取（仅用于开发调试）
        $tenantParam = $request->get('tenant_id');
        if ($tenantParam && is_numeric($tenantParam)) {
            return (int) $tenantParam;
        }
        
        return null;
    }
}

// ==================== 5. 登录控制器示例 ====================

namespace App\Controllers;

use App\Models\SysUser;
use App\Models\SysTenant;
use App\Models\SysUserTenant;
use Framework\Tenant\JwtTenantContext;

/**
 * 多租户登录控制器
 */
class TenantAuthController
{
    /**
     * 登录（支持租户选择）
     * 
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $tenantId = $request->post('tenant_id'); // 租户选择
        
        // 1. 验证用户
        $user = SysUser::where('username', $username)->first();
        if (!$user || !$user->verifyPassword($password)) {
            return json(['code' => 401, 'message' => '用户名或密码错误']);
        }
        
        if ($user->isDisabled()) {
            return json(['code' => 403, 'message' => '账号已被禁用']);
        }
        
        // 2. 验证租户
        if ($tenantId) {
            // 检查用户是否属于该租户
            $hasTenant = SysUserTenant::where('user_id', $user->id)
                ->where('tenant_id', $tenantId)
                ->exists();
            
            if (!$hasTenant && !$user->isSuperAdmin()) {
                return json(['code' => 403, 'message' => '您不属于该租户']);
            }
            
            // 验证租户有效性
            $tenant = SysTenant::find($tenantId);
            if (!$tenant || !$tenant->isValid()) {
                return json(['code' => 403, 'message' => '租户无效或已过期']);
            }
        } else {
            // 使用默认租户
            $tenantId = SysUserTenant::getDefaultTenantId($user->id);
            
            if (!$tenantId && !$user->isSuperAdmin()) {
                return json(['code' => 403, 'message' => '请先选择租户']);
            }
        }
        
        // 3. 生成 Token
        $token = JwtTenantContext::generateToken(
            [
                'user_id' => $user->id,
                'username' => $user->username,
            ],
            $tenantId ?? 0, // 超管可能 tenant_id = 0
            config('app.jwt_secret')
        );
        
        // 4. 更新登录信息
        $user->updateLoginInfo($request->getRemoteIp());
        
        // 5. 返回用户信息
        return json([
            'code' => 200,
            'message' => '登录成功',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'is_admin' => $user->isSuperAdmin(),
                ],
                'tenant_id' => $tenantId,
            ],
        ]);
    }
    
    /**
     * 获取用户可访问的租户列表
     * 
     * @param Request $request
     * @return Response
     */
    public function getUserTenants(Request $request): Response
    {
        $userId = $request->get('user_id'); // 从 Token 中解析
        
        $tenants = SysUserTenant::where('user_id', $userId)
            ->with('tenant')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->tenant->id,
                    'name' => $item->tenant->tenant_name,
                    'code' => $item->tenant->tenant_code,
                    'is_default' => $item->is_default,
                ];
            });
        
        return json([
            'code' => 200,
            'data' => $tenants,
        ]);
    }
    
    /**
     * 切换租户
     * 
     * @param Request $request
     * @return Response
     */
    public function switchTenant(Request $request): Response
    {
        $userId = $request->get('user_id'); // 从 Token 中解析
        $newTenantId = $request->post('tenant_id');
        
        // 验证用户是否属于该租户
        $hasTenant = SysUserTenant::where('user_id', $userId)
            ->where('tenant_id', $newTenantId)
            ->exists();
        
        if (!$hasTenant) {
            return json(['code' => 403, 'message' => '您不属于该租户']);
        }
        
        // 设置新的默认租户
        SysUserTenant::setDefaultTenant($userId, $newTenantId);
        
        // 生成新的 Token
        $token = JwtTenantContext::generateToken(
            [
                'user_id' => $userId,
                'username' => $request->get('username'),
            ],
            $newTenantId,
            config('app.jwt_secret')
        );
        
        return json([
            'code' => 200,
            'message' => '切换成功',
            'data' => [
                'token' => $token,
                'tenant_id' => $newTenantId,
            ],
        ]);
    }
}

// ==================== 6. 超管控制器示例 ====================

namespace App\Controllers\Admin;

use App\Models\SysUser;
use App\Models\SysRole;
use App\Models\SysTenant;
use Framework\Tenant\SafeTenantContext;

/**
 * 超级管理员控制器
 * 
 * 用于跨租户数据管理
 */
class SuperAdminController
{
    /**
     * 获取所有租户列表（超管）
     */
    public function getAllTenants()
    {
        // 使用超管模式
        $tenants = SysTenant::withIgnoreTenant(function () {
            return SysTenant::all();
        });
        
        return json([
            'code' => 200,
            'data' => $tenants,
        ]);
    }
    
    /**
     * 获取指定租户的用户列表（超管）
     */
    public function getTenantUsers(Request $request)
    {
        $tenantId = $request->get('tenant_id');
        
        $users = SysUser::withIgnoreTenant(function () use ($tenantId) {
            return SysUser::whereHas('tenants', function ($query) use ($tenantId) {
                $query->where('sys_tenant.id', $tenantId);
            })->get();
        });
        
        return json([
            'code' => 200,
            'data' => $users,
        ]);
    }
    
    /**
     * 跨租户查询用户（超管）
     */
    public function searchUsersAcrossTenants(Request $request)
    {
        $keyword = $request->get('keyword');
        
        $users = SysUser::withIgnoreTenant(function () use ($keyword) {
            return SysUser::where('username', 'like', "%{$keyword}%")
                ->orWhere('nickname', 'like', "%{$keyword}%")
                ->with('tenants.tenant')
                ->get();
        });
        
        return json([
            'code' => 200,
            'data' => $users,
        ]);
    }
    
    /**
     * 临时切换到指定租户视角
     */
    public function switchToTenantView(Request $request)
    {
        $tenantId = $request->post('tenant_id');
        
        // 验证租户存在
        $tenant = SysTenant::withIgnoreTenant(function () use ($tenantId) {
            return SysTenant::find($tenantId);
        });
        
        if (!$tenant) {
            return json(['code' => 404, 'message' => '租户不存在']);
        }
        
        // 生成临时 Token（超管视角，但指定租户）
        $token = JwtTenantContext::generateToken(
            [
                'user_id' => $request->get('user_id'),
                'username' => $request->get('username'),
                'is_super_admin' => true,
            ],
            $tenantId,
            config('app.jwt_secret')
        );
        
        return json([
            'code' => 200,
            'message' => '已切换到租户视角',
            'data' => [
                'token' => $token,
                'tenant_id' => $tenantId,
                'tenant_name' => $tenant->tenant_name,
            ],
        ]);
    }
}

// ==================== 7. 前端登录页示例（Vue3） ====================

/*
<!-- TenantLogin.vue -->
<template>
  <div class="login-container">
    <el-form :model="form" :rules="rules" ref="loginForm">
      <!-- 租户选择 -->
      <el-form-item label="租户" prop="tenant_id" v-if="tenants.length > 1">
        <el-select v-model="form.tenant_id" placeholder="请选择租户">
          <el-option
            v-for="tenant in tenants"
            :key="tenant.id"
            :label="tenant.name"
            :value="tenant.id"
          />
        </el-select>
      </el-form-item>
      
      <!-- 用户名 -->
      <el-form-item label="用户名" prop="username">
        <el-input v-model="form.username" placeholder="请输入用户名" />
      </el-form-item>
      
      <!-- 密码 -->
      <el-form-item label="密码" prop="password">
        <el-input 
          v-model="form.password" 
          type="password" 
          placeholder="请输入密码"
          @keyup.enter="handleLogin"
        />
      </el-form-item>
      
      <!-- 登录按钮 -->
      <el-form-item>
        <el-button type="primary" @click="handleLogin" :loading="loading">
          登录
        </el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'

const router = useRouter()
const loading = ref(false)
const tenants = ref([])

const form = reactive({
  tenant_id: null,
  username: '',
  password: ''
})

const rules = {
  tenant_id: [{ required: true, message: '请选择租户', trigger: 'change' }],
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [{ required: true, message: '请输入密码', trigger: 'blur' }]
}

// 获取租户列表（输入用户名后）
const fetchTenants = async (username) => {
  try {
    const res = await fetch(`/api/auth/tenants?username=${username}`)
    const data = await res.json()
    if (data.code === 200) {
      tenants.value = data.data
      // 如果有默认租户，自动选择
      const defaultTenant = data.data.find(t => t.is_default)
      if (defaultTenant) {
        form.tenant_id = defaultTenant.id
      }
    }
  } catch (error) {
    console.error('获取租户列表失败:', error)
  }
}

// 登录
const handleLogin = async () => {
  loading.value = true
  try {
    const res = await fetch('/api/auth/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    })
    const data = await res.json()
    
    if (data.code === 200) {
      // 保存 Token
      localStorage.setItem('token', data.data.token)
      localStorage.setItem('tenant_id', data.data.tenant_id)
      
      ElMessage.success('登录成功')
      router.push('/dashboard')
    } else {
      ElMessage.error(data.message)
    }
  } catch (error) {
    ElMessage.error('登录失败')
  } finally {
    loading.value = false
  }
}
</script>
*/

// ==================== 8. 请求拦截器示例（Axios） ====================

/*
// http.js
import axios from 'axios'

const http = axios.create({
  baseURL: '/api',
  timeout: 10000
})

// 请求拦截器
http.interceptors.request.use(
  config => {
    // 添加 Token
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // 添加租户ID（备用）
    const tenantId = localStorage.getItem('tenant_id')
    if (tenantId) {
      config.headers['X-Tenant-ID'] = tenantId
    }
    
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
http.interceptors.response.use(
  response => {
    return response.data
  },
  error => {
    if (error.response?.status === 401) {
      // Token 过期，跳转到登录页
      localStorage.removeItem('token')
      localStorage.removeItem('tenant_id')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default http
*/
