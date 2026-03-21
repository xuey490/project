<?php

declare(strict_types=1);

/**
 * 文章服务层
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Services;

use App\Dao\ArticleDao;
use App\Models\Article;
use App\Models\SysRole;
use App\Models\SysRoleDept;
use App\Models\SysUserRole;
use Framework\Tenant\TenantContext;
use Framework\Basic\Traits\DataScopeTrait;

/**
 * ArticleService 文章服务层
 *
 * 处理文章相关的业务逻辑，包括：
 * - 文章的增删改查
 * - 数据权限控制
 * - 业务规则验证
 */
class ArticleService
{
    /**
     * 文章 DAO
     * @var ArticleDao
     */
    protected ArticleDao $articleDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->articleDao = new ArticleDao();
    }

    // ==================== 数据权限初始化 ====================

    /**
     * 初始化当前用户的数据权限
     *
     * 根据当前登录用户的角色，设置数据权限上下文
     *
     * @param int $userId 用户ID
     * @param int|null $deptId 部门ID
     * @param int|null $tenantId 租户ID
     * @return void
     */
    public function initDataScope(int $userId, ?int $deptId = null, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? TenantContext::getTenantId() ?? 0;

        // 获取用户在当前租户的角色
        $roleIds = SysUserRole::getRoleIdsByTenant($userId, $tenantId);

        if (empty($roleIds)) {
            // 没有角色，只能看自己的数据
            Article::setDataScopeContext($userId, DataScopeTrait::DATA_SCOPE_SELF, $deptId);
            return;
        }

        // 获取角色的数据权限范围（取最大权限）
        $dataScope = $this->getMaxDataScope($roleIds);

        // 如果是自定义权限，获取自定义部门ID
        $customDeptIds = [];
        if ($dataScope === DataScopeTrait::DATA_SCOPE_CUSTOM) {
            $customDeptIds = SysRoleDept::getDeptIdsByRoles($roleIds);
        }

        // 设置数据权限上下文
        Article::setDataScopeContext($userId, $dataScope, $deptId, $customDeptIds);
    }

    /**
     * 获取多个角色的最大数据权限
     *
     * 权限范围从小到大：本人 < 部门 < 部门及子部门 < 全部
     *
     * @param array $roleIds 角色ID数组
     * @return int 最大权限范围值
     */
    protected function getMaxDataScope(array $roleIds): int
    {
        // 获取所有角色的数据权限
        $scopes = \App\Models\SysRole::whereIn('id', $roleIds)
            ->pluck('data_scope')
            ->toArray();

        if (empty($scopes)) {
            return DataScopeTrait::DATA_SCOPE_SELF;
        }

        // 返回最大权限（数值越小权限越大）
        return min($scopes);
    }

    /**
     * 清除数据权限上下文
     *
     * @return void
     */
    public function clearDataScope(): void
    {
        Article::clearDataScopeContext();
    }

    // ==================== 文章 CRUD ====================

    /**
     * 获取文章列表
     *
     * @param array $params 查询参数
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @return array
     */
    public function getList(array $params = [], int $page = 1, int $pageSize = 10): array
    {
        return $this->articleDao->getList($params, $page, $pageSize);
    }

    /**
     * 获取文章详情
     *
     * @param int $id 文章ID
     * @return Article|null
     */
    public function getDetail(int $id): ?Article
    {
        return $this->articleDao->getById($id);
    }

    /**
     * 创建文章
     *
     * @param array $data 文章数据
     * @param int $userId 创建人ID
     * @param int $deptId 部门ID
     * @param int|null $tenantId 租户ID
     * @return Article
     * @throws \Exception
     */
    public function create(array $data, int $userId, int $deptId, ?int $tenantId = null): Article
    {
        // 验证数据
        $this->validateArticleData($data);

        // 补充默认字段
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $data['dept_id'] = $deptId;
        $data['tenant_id'] = $tenantId ?? TenantContext::getTenantId() ?? 0;

        // 如果状态为已发布，设置发布时间
        if (isset($data['status']) && $data['status'] == Article::STATUS_PUBLISHED) {
            $data['published_at'] = now();
        }

        return $this->articleDao->create($data);
    }

    /**
     * 更新文章
     *
     * @param int $id 文章ID
     * @param array $data 更新数据
     * @param int $userId 更新人ID
     * @return bool
     * @throws \Exception
     */
    public function update(int $id, array $data, int $userId): bool
    {
        // 检查文章是否存在
        $article = $this->articleDao->getById($id);
        if (!$article) {
            throw new \Exception('文章不存在');
        }

        // 验证数据
        $this->validateArticleData($data, $id);

        // 补充更新字段
        $data['updated_by'] = $userId;

        // 如果状态从草稿变为已发布，设置发布时间
        if (isset($data['status']) &&
            $data['status'] == Article::STATUS_PUBLISHED &&
            $article->isDraft()) {
            $data['published_at'] = now();
        }

        return $this->articleDao->update($id, $data);
    }

    /**
     * 删除文章
     *
     * @param int $id 文章ID
     * @return bool
     * @throws \Exception
     */
    public function delete(int|string $id): bool
    {
        $article = $this->articleDao->getById($id);
        if (!$article) {
            throw new \Exception('文章不存在');
        }

        return $this->articleDao->delete($id);
    }

    /**
     * 发布文章
     *
     * @param int $id 文章ID
     * @return bool
     * @throws \Exception
     */
    public function publish(int $id): bool
    {
        $article = $this->articleDao->getById($id);
        if (!$article) {
            throw new \Exception('文章不存在');
        }

        return $article->publish();
    }

    /**
     * 下架文章
     *
     * @param int $id 文章ID
     * @return bool
     * @throws \Exception
     */
    public function offline(int $id): bool
    {
        $article = $this->articleDao->getById($id);
        if (!$article) {
            throw new \Exception('文章不存在');
        }

        return $article->offline();
    }

    // ==================== 数据验证 ====================

    /**
     * 验证文章数据
     *
     * @param array $data 文章数据
     * @param int|null $excludeId 排除的文章ID（更新时使用）
     * @return void
     * @throws \Exception
     */
    protected function validateArticleData(array $data, ?int $excludeId = null): void
    {
        // 标题必填
        if (empty($data['title'])) {
            throw new \Exception('文章标题不能为空');
        }

        // 标题长度
        if (mb_strlen($data['title']) > 200) {
            throw new \Exception('文章标题不能超过200个字符');
        }

        // 内容长度检查
        if (!empty($data['content']) && mb_strlen($data['content']) > 100000) {
            throw new \Exception('文章内容过长');
        }

        // 摘要长度
        if (!empty($data['summary']) && mb_strlen($data['summary']) > 500) {
            throw new \Exception('文章摘要不能超过500个字符');
        }

        // 状态有效性
        if (isset($data['status']) && !in_array($data['status'], [
            Article::STATUS_DRAFT,
            Article::STATUS_PUBLISHED,
            Article::STATUS_OFFLINE,
        ])) {
            throw new \Exception('无效的文章状态');
        }
    }

    // ==================== 批量操作 ====================

    /**
     * 批量发布
     *
     * @param array $ids 文章ID数组
     * @return int
     */
    public function batchPublish(array $ids): int
    {
        return $this->articleDao->batchUpdateStatus($ids, Article::STATUS_PUBLISHED);
    }

    /**
     * 批量下架
     *
     * @param array $ids 文章ID数组
     * @return int
     */
    public function batchOffline(array $ids): int
    {
        return $this->articleDao->batchUpdateStatus($ids, Article::STATUS_OFFLINE);
    }

    /**
     * 批量删除
     *
     * @param array $ids 文章ID数组
     * @return int
     */
    public function batchDelete(array $ids): int
    {
        return $this->articleDao->batchDelete($ids);
    }

    // ==================== 统计功能 ====================

    /**
     * 获取文章统计
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return $this->articleDao->getStatistics();
    }

    /**
     * 获取仪表盘统计
     *
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        $stats = $this->articleDao->getStatistics();

        return [
            'overview' => [
                'total' => $stats['total'],
                'published' => $stats['published'],
                'draft' => $stats['draft'],
                'offline' => $stats['offline'],
            ],
            'today' => $stats['today_created'],
            'views' => $stats['total_views'],
            'by_status' => $this->articleDao->getStatusStatistics(),
            'by_dept' => $this->articleDao->getDeptStatistics(),
            'by_creator' => $this->articleDao->getCreatorStatistics(),
        ];
    }

    // ==================== 数据权限相关 ====================

    /**
     * 检查用户是否有权限操作文章
     *
     * @param int $articleId 文章ID
     * @param int $userId 用户ID
     * @param string $action 操作类型（view/edit/delete）
     * @return bool
     */
    public function checkPermission(int $articleId, int $userId, string $action = 'view'): bool
    {
        $article = $this->articleDao->getById($articleId);

        if (!$article) {
            return false;
        }

        // 超管有所有权限
        if ($this->isSuperAdmin($userId)) {
            return true;
        }

        // 创建人有编辑和删除权限
        if ($article->created_by === $userId) {
            return true;
        }

        // 查看权限根据数据权限判断
        if ($action === 'view') {
            // 使用数据权限 Trait 的检查逻辑
            return $this->canViewArticle($article, $userId);
        }

        return false;
    }

    /**
     * 检查用户是否可以查看文章
     *
     * @param Article $article 文章
     * @param int $userId 用户ID
     * @return bool
     */
    protected function canViewArticle(Article $article, int $userId): bool
    {
        // 获取当前用户的数据权限范围
        $tenantId = TenantContext::getTenantId() ?? 0;
        $roleIds = SysUserRole::getRoleIdsByTenant($userId, $tenantId);
        $dataScope = $this->getMaxDataScope($roleIds);

        switch ($dataScope) {
            case DataScopeTrait::DATA_SCOPE_ALL:
                return true;

            case DataScopeTrait::DATA_SCOPE_DEPT:
                // 需要获取当前用户的部门ID
                $userDeptId = $this->getUserDeptId($userId);
                return $article->dept_id === $userDeptId;

            case DataScopeTrait::DATA_SCOPE_DEPT_AND_CHILD:
            case DataScopeTrait::DATA_SCOPE_DEPT_AND_SELF:
                $userDeptId = $this->getUserDeptId($userId);
                if ($article->created_by === $userId) {
                    return true;
                }
                $childDeptIds = \App\Models\SysDept::getAllChildIds($userDeptId);
                return in_array($article->dept_id, $childDeptIds) || $article->dept_id === $userDeptId;

            case DataScopeTrait::DATA_SCOPE_SELF:
                return $article->created_by === $userId;

            case DataScopeTrait::DATA_SCOPE_CUSTOM:
                $customDeptIds = SysRoleDept::getDeptIdsByRoles($roleIds);
                return in_array($article->dept_id, $customDeptIds);

            default:
                return false;
        }
    }

    /**
     * 获取用户部门ID
     *
     * @param int $userId 用户ID
     * @return int|null
     */
    protected function getUserDeptId(int $userId): ?int
    {
        $user = \App\Models\SysUser::find($userId);
        return $user?->dept_id;
    }

    /**
     * 检查是否为超级管理员
     *
     * @param int $userId 用户ID
     * @return bool
     */
    protected function isSuperAdmin(int $userId): bool
    {
        $user = \App\Models\SysUser::find($userId);
        return $user?->isSuperAdmin() ?? false;
    }

    /**
     * 获取数据权限选项（用于前端）
     *
     * @return array
     */
    public function getDataScopeOptions(): array
    {
        return Article::getDataScopeOptions();
    }
}
