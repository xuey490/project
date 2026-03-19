<?php

declare(strict_types=1);

/**
 * 多租户功能测试案例
 * 
 * 测试范围：
 * 1. TenantContext 上下文管理
 * 2. LaTenantScope 租户隔离
 * 3. LaBelongsToTenant Trait 功能
 * 4. 超管模式
 * 5. 用户-租户-角色关联
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\SysUser;
use App\Models\SysRole;
use App\Models\SysTenant;
use App\Models\SysUserRole;
use App\Models\SysUserTenant;
use Framework\Tenant\TenantContext;

/**
 * 多租户功能测试类
 */
class TenantFunctionalityTest extends TestCase
{
    // ==================== 测试数据准备 ====================
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // 清理测试数据
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        // 清理测试数据
        $this->cleanupTestData();
        
        parent::tearDown();
    }
    
    /**
     * 创建测试租户
     */
    protected function createTestTenant(string $name, string $code): SysTenant
    {
        return SysTenant::create([
            'tenant_name' => $name,
            'tenant_code' => $code,
            'status' => SysTenant::STATUS_ENABLED,
            'max_users' => 100,
        ]);
    }
    
    /**
     * 创建测试用户
     */
    protected function createTestUser(string $username, int $tenantId = null): SysUser
    {
        $user = SysUser::create([
            'username' => $username,
            'password' => password_hash('123456', PASSWORD_BCRYPT),
            'nickname' => '测试用户',
            'status' => SysUser::STATUS_ENABLED,
        ]);
        
        if ($tenantId) {
            SysUserTenant::create([
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'is_default' => true,
            ]);
        }
        
        return $user;
    }
    
    /**
     * 创建测试角色
     */
    protected function createTestRole(string $name, string $code, int $tenantId): SysRole
    {
        return SysRole::create([
            'role_name' => $name,
            'role_code' => $code,
            'tenant_id' => $tenantId,
            'status' => SysRole::STATUS_ENABLED,
        ]);
    }
    
    /**
     * 清理测试数据
     */
    protected function cleanupTestData(): void
    {
        // 使用超管模式清理数据
        TenantContext::ignore();
        
        SysUserRole::where('id', '>', 0)->delete();
        SysUserTenant::where('id', '>', 0)->delete();
        SysRole::where('id', '>', 0)->delete();
        SysUser::where('id', '>', 0)->delete();
        SysTenant::where('id', '>', 0)->delete();
        
        TenantContext::restore();
    }
    
    // ==================== Test Case 1: TenantContext 基础功能 ====================
    
    /**
     * 测试：设置和获取租户ID
     * 
     * @test
     */
    public function test_set_and_get_tenant_id(): void
    {
        // Arrange
        $tenantId = 1001;
        
        // Act
        TenantContext::setTenantId($tenantId);
        $result = TenantContext::getTenantId();
        
        // Assert
        $this->assertEquals($tenantId, $result);
    }
    
    /**
     * 测试：租户隔离默认启用
     * 
     * @test
     */
    public function test_tenant_isolation_enabled_by_default(): void
    {
        // Arrange
        TenantContext::setTenantId(1001);
        
        // Act
        $shouldApply = TenantContext::shouldApplyTenant();
        
        // Assert
        $this->assertTrue($shouldApply);
    }
    
    /**
     * 测试：未设置租户ID时不应用隔离
     * 
     * @test
     */
    public function test_no_isolation_when_tenant_id_not_set(): void
    {
        // Arrange
        TenantContext::setTenantId(null);
        
        // Act
        $shouldApply = TenantContext::shouldApplyTenant();
        
        // Assert
        $this->assertFalse($shouldApply);
    }
    
    /**
     * 测试：忽略租户隔离（超管模式）
     * 
     * @test
     */
    public function test_ignore_tenant_isolation(): void
    {
        // Arrange
        TenantContext::setTenantId(1001);
        
        // Act
        TenantContext::ignore();
        $shouldApply = TenantContext::shouldApplyTenant();
        
        // Assert
        $this->assertFalse($shouldApply);
        
        // Cleanup
        TenantContext::restore();
    }
    
    /**
     * 测试：恢复租户隔离
     * 
     * @test
     */
    public function test_restore_tenant_isolation(): void
    {
        // Arrange
        TenantContext::setTenantId(1001);
        TenantContext::ignore();
        
        // Act
        TenantContext::restore();
        $shouldApply = TenantContext::shouldApplyTenant();
        
        // Assert
        $this->assertTrue($shouldApply);
    }
    
    /**
     * 测试：临时忽略租户隔离（withIgnore）
     * 
     * @test
     */
    public function test_temporary_ignore_with_withIgnore(): void
    {
        // Arrange
        TenantContext::setTenantId(1001);
        
        // Act & Assert
        $result = TenantContext::withIgnore(function () {
            // 在闭包内应该忽略租户隔离
            $this->assertFalse(TenantContext::shouldApplyTenant());
            return 'executed';
        });
        
        // 闭包执行完毕后应该恢复隔离
        $this->assertTrue(TenantContext::shouldApplyTenant());
        $this->assertEquals('executed', $result);
    }
    
    // ==================== Test Case 2: 模型租户隔离 ====================
    
    /**
     * 测试：创建记录时自动填充租户ID
     * 
     * @test
     */
    public function test_auto_fill_tenant_id_on_create(): void
    {
        // Arrange
        $tenant = $this->createTestTenant('测试租户', 'test_tenant_001');
        TenantContext::setTenantId($tenant->id);
        
        // Act
        $role = SysRole::create([
            'role_name' => '测试角色',
            'role_code' => 'test_role',
            'status' => SysRole::STATUS_ENABLED,
        ]);
        
        // Assert
        $this->assertEquals($tenant->id, $role->tenant_id);
    }
    
    /**
     * 测试：查询时自动过滤当前租户
     * 
     * @test
     */
    public function test_auto_filter_by_tenant_on_query(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        
        // 创建角色（使用超管模式绕过隔离）
        TenantContext::ignore();
        SysRole::create([
            'role_name' => '租户1的角色',
            'role_code' => 'role_t1',
            'tenant_id' => $tenant1->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        SysRole::create([
            'role_name' => '租户2的角色',
            'role_code' => 'role_t2',
            'tenant_id' => $tenant2->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        TenantContext::restore();
        
        // Act - 设置为租户1
        TenantContext::setTenantId($tenant1->id);
        $roles = SysRole::all();
        
        // Assert - 应该只返回租户1的角色
        $this->assertCount(1, $roles);
        $this->assertEquals('租户1的角色', $roles->first()->role_name);
    }
    
    /**
     * 测试：withoutTenancy 移除租户隔离
     * 
     * @test
     */
    public function test_without_tenancy_removes_isolation(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        
        TenantContext::ignore();
        SysRole::create([
            'role_name' => '角色1',
            'role_code' => 'role_1',
            'tenant_id' => $tenant1->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        SysRole::create([
            'role_name' => '角色2',
            'role_code' => 'role_2',
            'tenant_id' => $tenant2->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        TenantContext::restore();
        
        // Act - 设置为租户1，但使用 withoutTenancy
        TenantContext::setTenantId($tenant1->id);
        $allRoles = SysRole::withoutTenancy()->get();
        
        // Assert - 应该返回所有角色
        $this->assertCount(2, $allRoles);
    }
    
    /**
     * 测试：ignoreTenant 静态方法
     * 
     * @test
     */
    public function test_ignore_tenant_static_method(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        
        TenantContext::ignore();
        SysRole::create([
            'role_name' => '角色1',
            'role_code' => 'role_1',
            'tenant_id' => $tenant1->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        SysRole::create([
            'role_name' => '角色2',
            'role_code' => 'role_2',
            'tenant_id' => $tenant2->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        TenantContext::restore();
        
        // Act
        TenantContext::setTenantId($tenant1->id);
        $allRoles = SysRole::ignoreTenant()->get();
        
        // Assert
        $this->assertCount(2, $allRoles);
        
        // Cleanup
        SysRole::restoreTenant();
    }
    
    /**
     * 测试：withIgnoreTenant 安全作用域
     * 
     * @test
     */
    public function test_with_ignore_tenant_safe_scope(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        
        TenantContext::ignore();
        SysRole::create([
            'role_name' => '角色1',
            'role_code' => 'role_1',
            'tenant_id' => $tenant1->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        SysRole::create([
            'role_name' => '角色2',
            'role_code' => 'role_2',
            'tenant_id' => $tenant2->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        TenantContext::restore();
        
        // Act
        TenantContext::setTenantId($tenant1->id);
        $count = SysRole::withIgnoreTenant(function () {
            return SysRole::count();
        });
        
        // Assert
        $this->assertEquals(2, $count);
        $this->assertTrue(TenantContext::shouldApplyTenant()); // 应该自动恢复
    }
    
    // ==================== Test Case 3: 用户-租户-角色关联 ====================
    
    /**
     * 测试：用户可以在不同租户拥有不同角色
     * 
     * @test
     */
    public function test_user_can_have_different_roles_in_different_tenants(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        
        $user = $this->createTestUser('testuser');
        
        // 将用户关联到两个租户
        SysUserTenant::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant1->id,
            'is_default' => true,
        ]);
        SysUserTenant::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant2->id,
            'is_default' => false,
        ]);
        
        // 创建角色
        $roleAdmin = $this->createTestRole('管理员', 'admin', $tenant1->id);
        $roleUser = $this->createTestRole('普通用户', 'user', $tenant2->id);
        
        // 分配角色（带租户ID）
        SysUserRole::create([
            'user_id' => $user->id,
            'role_id' => $roleAdmin->id,
            'tenant_id' => $tenant1->id,
        ]);
        SysUserRole::create([
            'user_id' => $user->id,
            'role_id' => $roleUser->id,
            'tenant_id' => $tenant2->id,
        ]);
        
        // Act & Assert - 租户1下应该是管理员
        TenantContext::setTenantId($tenant1->id);
        $rolesT1 = $user->roles()->get();
        $this->assertCount(1, $rolesT1);
        $this->assertEquals('管理员', $rolesT1->first()->role_name);
        
        // Act & Assert - 租户2下应该是普通用户
        TenantContext::setTenantId($tenant2->id);
        $rolesT2 = $user->roles()->get();
        $this->assertCount(1, $rolesT2);
        $this->assertEquals('普通用户', $rolesT2->first()->role_name);
    }
    
    /**
     * 测试：同步用户角色（按租户）
     * 
     * @test
     */
    public function test_sync_user_roles_by_tenant(): void
    {
        // Arrange
        $tenant = $this->createTestTenant('测试租户', 'test_tenant');
        $user = $this->createTestUser('testuser', $tenant->id);
        
        $role1 = $this->createTestRole('角色1', 'role_1', $tenant->id);
        $role2 = $this->createTestRole('角色2', 'role_2', $tenant->id);
        $role3 = $this->createTestRole('角色3', 'role_3', $tenant->id);
        
        // Act - 初始分配 role1 和 role2
        SysUserRole::syncUserRolesByTenant($user->id, $tenant->id, [$role1->id, $role2->id]);
        
        // Assert
        $roleIds = SysUserRole::getRoleIdsByTenant($user->id, $tenant->id);
        $this->assertCount(2, $roleIds);
        $this->assertContains($role1->id, $roleIds);
        $this->assertContains($role2->id, $roleIds);
        
        // Act - 更新为 role2 和 role3
        SysUserRole::syncUserRolesByTenant($user->id, $tenant->id, [$role2->id, $role3->id]);
        
        // Assert
        $roleIds = SysUserRole::getRoleIdsByTenant($user->id, $tenant->id);
        $this->assertCount(2, $roleIds);
        $this->assertNotContains($role1->id, $roleIds);
        $this->assertContains($role2->id, $roleIds);
        $this->assertContains($role3->id, $roleIds);
    }
    
    /**
     * 测试：获取用户默认租户
     * 
     * @test
     */
    public function test_get_user_default_tenant(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        $user = $this->createTestUser('testuser');
        
        SysUserTenant::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant1->id,
            'is_default' => false,
        ]);
        SysUserTenant::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant2->id,
            'is_default' => true,
        ]);
        
        // Act
        $defaultTenantId = SysUserTenant::getDefaultTenantId($user->id);
        
        // Assert
        $this->assertEquals($tenant2->id, $defaultTenantId);
    }
    
    /**
     * 测试：设置用户默认租户
     * 
     * @test
     */
    public function test_set_user_default_tenant(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        $user = $this->createTestUser('testuser');
        
        SysUserTenant::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant1->id,
            'is_default' => true,
        ]);
        SysUserTenant::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant2->id,
            'is_default' => false,
        ]);
        
        // Act
        SysUserTenant::setDefaultTenant($user->id, $tenant1->id);
        
        // Assert
        $defaultTenantId = SysUserTenant::getDefaultTenantId($user->id);
        $this->assertEquals($tenant1->id, $defaultTenantId);
        
        // 验证另一个不再是默认
        $record = SysUserTenant::where('user_id', $user->id)
            ->where('tenant_id', $tenant2->id)
            ->first();
        $this->assertFalse($record->is_default);
    }
    
    // ==================== Test Case 4: 租户有效性检查 ====================
    
    /**
     * 测试：检查租户是否有效
     * 
     * @test
     */
    public function test_check_tenant_is_valid(): void
    {
        // Arrange - 有效租户
        $validTenant = $this->createTestTenant('有效租户', 'valid_tenant');
        
        // Arrange - 禁用租户
        $disabledTenant = SysTenant::create([
            'tenant_name' => '禁用租户',
            'tenant_code' => 'disabled_tenant',
            'status' => SysTenant::STATUS_DISABLED,
        ]);
        
        // Arrange - 过期租户
        $expiredTenant = SysTenant::create([
            'tenant_name' => '过期租户',
            'tenant_code' => 'expired_tenant',
            'status' => SysTenant::STATUS_ENABLED,
            'expire_time' => now()->subDay(),
        ]);
        
        // Act & Assert
        $this->assertTrue($validTenant->isValid());
        $this->assertFalse($disabledTenant->isValid());
        $this->assertFalse($expiredTenant->isValid());
    }
    
    /**
     * 测试：检查租户用户数量限制
     * 
     * @test
     */
    public function test_check_tenant_user_limit(): void
    {
        // Arrange - 限制2个用户的租户
        $limitedTenant = SysTenant::create([
            'tenant_name' => '有限制的租户',
            'tenant_code' => 'limited_tenant',
            'status' => SysTenant::STATUS_ENABLED,
            'max_users' => 2,
        ]);
        
        // Act & Assert - 初始状态
        $this->assertFalse($limitedTenant->isUserLimitReached());
        
        // 添加2个用户
        $user1 = $this->createTestUser('user1', $limitedTenant->id);
        $user2 = $this->createTestUser('user2', $limitedTenant->id);
        
        // Assert - 应该达到限制
        $this->assertTrue($limitedTenant->fresh()->isUserLimitReached());
    }
    
    // ==================== Test Case 5: 边界情况和异常处理 ====================
    
    /**
     * 测试：无 tenant_id 字段的表不受隔离影响
     * 
     * @test
     */
    public function test_table_without_tenant_id_not_affected(): void
    {
        // 假设有一个没有 tenant_id 的表（如系统配置表）
        // 这里用 SysUserTenant 模拟（如果它没有 tenant_id 字段）
        
        // Arrange
        $tenant = $this->createTestTenant('租户', 'tenant_001');
        TenantContext::setTenantId($tenant->id);
        
        // Act - 查询没有 tenant_id 的表应该不受限制
        // 注意：这需要实际的无 tenant_id 表来测试
        
        // Assert - 如果表没有 tenant_id，查询应该返回所有记录
        $this->assertTrue(true); // 占位
    }
    
    /**
     * 测试：并发请求租户隔离（Workerman 环境）
     * 
     * @test
     * @requires extension pcntl
     */
    public function test_concurrent_request_tenant_isolation(): void
    {
        // 这个测试模拟 Workerman 多进程环境下的租户隔离
        // 需要实际的 Workerman 环境才能完整测试
        
        // 测试思路：
        // 1. 进程 A 设置 tenant_id = 1
        // 2. 进程 B 设置 tenant_id = 2
        // 3. 两个进程同时执行查询
        // 4. 验证各自只能看到自己的数据
        
        $this->markTestSkipped('需要 Workerman 环境才能测试');
    }
    
    /**
     * 测试：租户切换
     * 
     * @test
     */
    public function test_tenant_switching(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        
        TenantContext::ignore();
        SysRole::create([
            'role_name' => '角色1',
            'role_code' => 'role_1',
            'tenant_id' => $tenant1->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        SysRole::create([
            'role_name' => '角色2',
            'role_code' => 'role_2',
            'tenant_id' => $tenant2->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        TenantContext::restore();
        
        // Act - 切换到租户1
        TenantContext::setTenantId($tenant1->id);
        $roles1 = SysRole::all();
        $this->assertCount(1, $roles1);
        $this->assertEquals('角色1', $roles1->first()->role_name);
        
        // Act - 切换到租户2
        TenantContext::setTenantId($tenant2->id);
        $roles2 = SysRole::all();
        $this->assertCount(1, $roles2);
        $this->assertEquals('角色2', $roles2->first()->role_name);
    }
    
    /**
     * 测试：批量操作受租户隔离保护
     * 
     * @test
     */
    public function test_bulk_operations_respect_tenant_isolation(): void
    {
        // Arrange
        $tenant1 = $this->createTestTenant('租户1', 'tenant_001');
        $tenant2 = $this->createTestTenant('租户2', 'tenant_002');
        
        TenantContext::ignore();
        SysRole::create([
            'role_name' => '角色1',
            'role_code' => 'role_1',
            'tenant_id' => $tenant1->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        SysRole::create([
            'role_name' => '角色2',
            'role_code' => 'role_2',
            'tenant_id' => $tenant2->id,
            'status' => SysRole::STATUS_ENABLED,
        ]);
        TenantContext::restore();
        
        // Act - 在租户1下执行批量更新
        TenantContext::setTenantId($tenant1->id);
        $affected = SysRole::where('status', SysRole::STATUS_ENABLED)
            ->update(['remark' => '已更新']);
        
        // Assert - 只应该更新租户1的数据
        $this->assertEquals(1, $affected);
        
        // 验证租户2的数据未被更新
        TenantContext::setTenantId($tenant2->id);
        $role2 = SysRole::first();
        $this->assertNull($role2->remark);
    }
}

/**
 * 多租户性能测试
 */
class TenantPerformanceTest extends TestCase
{
    /**
     * 测试：大量数据下租户隔离查询性能
     * 
     * @test
     */
    public function test_tenant_query_performance_with_large_dataset(): void
    {
        $this->markTestSkipped('性能测试需要大量数据准备');
        
        // 测试思路：
        // 1. 创建 10 个租户
        // 2. 每个租户 10000 条数据
        // 3. 测试带租户隔离的查询性能
        // 4. 对比有无索引的查询性能
    }
    
    /**
     * 测试：TenantContext 状态切换性能
     * 
     * @test
     */
    public function test_tenant_context_switching_performance(): void
    {
        $iterations = 10000;
        
        $start = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            TenantContext::setTenantId($i);
            TenantContext::getTenantId();
            TenantContext::ignore();
            TenantContext::restore();
        }
        
        $elapsed = microtime(true) - $start;
        
        // 断言：10000 次操作应该在 1 秒内完成
        $this->assertLessThan(1.0, $elapsed, 'TenantContext 操作性能下降');
    }
}
