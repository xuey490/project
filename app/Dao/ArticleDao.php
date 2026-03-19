<?php

declare(strict_types=1);

/**
 * 文章数据访问对象（DAO）
 *
 * @package App\Dao
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Dao;

use App\Models\Article;
use Framework\Basic\BaseDao;

/**
 * ArticleDao 文章数据访问对象
 *
 * 负责文章相关的数据库操作，包括：
 * - 文章的增删改查
 * - 数据权限过滤
 * - 分页查询
 * - 统计功能
 */
class ArticleDao extends BaseDao
{
    /**
     * 模型类名
     * @var string
     */
    protected string $modelClass = Article::class;

    // ==================== 基础 CRUD ====================

    /**
     * 根据ID获取文章
     *
     * @param int $id 文章ID
     * @return Article|null
     */
    public function getById(int $id): ?Article
    {
        return Article::find($id);
    }

    /**
     * 创建文章
     *
     * @param array $data 文章数据
     * @return Article
     */
    public function create(array $data): Article
    {
        return Article::create($data);
    }

    /**
     * 更新文章
     *
     * @param int $id 文章ID
     * @param array $data 更新数据
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $article = Article::find($id);
        if (!$article) {
            return false;
        }
        return $article->update($data);
    }

    /**
     * 删除文章（软删除）
     *
     * @param int $id 文章ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        $article = Article::find($id);
        if (!$article) {
            return false;
        }
        return $article->delete();
    }

    /**
     * 强制删除文章
     *
     * @param int $id 文章ID
     * @return bool
     */
    public function forceDelete(int $id): bool
    {
        $article = Article::withTrashed()->find($id);
        if (!$article) {
            return false;
        }
        return $article->forceDelete();
    }

    /**
     * 恢复软删除的文章
     *
     * @param int $id 文章ID
     * @return bool
     */
    public function restore(int $id): bool
    {
        $article = Article::withTrashed()->find($id);
        if (!$article) {
            return false;
        }
        return $article->restore();
    }

    // ==================== 列表查询 ====================

    /**
     * 获取文章列表（自动应用数据权限）
     *
     * @param array $params 查询参数
     * @param int $page 页码
     * @param int $pageSize 每页数量
     * @return array
     */
    public function getList(array $params = [], int $page = 1, int $pageSize = 10): array
    {
        $query = Article::query();

        // 应用查询条件
        $this->applyFilters($query, $params);

        // 排序
        $orderBy = $params['order_by'] ?? 'created_at';
        $order = $params['order'] ?? 'desc';
        $query->orderBy($orderBy, $order);

        // 分页
        $paginator = $query->paginate($pageSize, ['*'], 'page', $page);

        return [
            'list' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'page_size' => $paginator->perPage(),
            'total_pages' => $paginator->lastPage(),
        ];
    }

    /**
     * 获取所有文章（不分页）
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getAll(array $params = []): array
    {
        $query = Article::query();

        $this->applyFilters($query, $params);

        $orderBy = $params['order_by'] ?? 'sort';
        $order = $params['order'] ?? 'desc';

        return $query->orderBy($orderBy, $order)->get()->toArray();
    }

    /**
     * 应用查询过滤器
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $params
     * @return void
     */
    protected function applyFilters($query, array $params): void
    {
        // 关键词搜索
        if (!empty($params['keyword'])) {
            $keyword = $params['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        // 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (int)$params['status']);
        }

        // 分类筛选
        if (!empty($params['category_id'])) {
            $query->where('category_id', (int)$params['category_id']);
        }

        // 部门筛选
        if (!empty($params['dept_id'])) {
            $query->where('dept_id', (int)$params['dept_id']);
        }

        // 创建人筛选
        if (!empty($params['created_by'])) {
            $query->where('created_by', (int)$params['created_by']);
        }

        // 日期范围
        if (!empty($params['start_date'])) {
            $query->whereDate('created_at', '>=', $params['start_date']);
        }
        if (!empty($params['end_date'])) {
            $query->whereDate('created_at', '<=', $params['end_date']);
        }
    }

    // ==================== 数据权限相关 ====================

    /**
     * 忽略数据权限获取文章
     *
     * @param int $id 文章ID
     * @return Article|null
     */
    public function getByIdWithoutDataScope(int $id): ?Article
    {
        return Article::withoutDataScope(function () use ($id) {
            return Article::find($id);
        });
    }

    /**
     * 获取所有文章（忽略数据权限）
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getAllWithoutDataScope(array $params = []): array
    {
        return Article::withoutDataScope(function () use ($params) {
            $query = Article::query();
            $this->applyFilters($query, $params);
            return $query->get()->toArray();
        });
    }

    // ==================== 统计方法 ====================

    /**
     * 获取文章统计
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total' => Article::count(),
            'published' => Article::where('status', Article::STATUS_PUBLISHED)->count(),
            'draft' => Article::where('status', Article::STATUS_DRAFT)->count(),
            'offline' => Article::where('status', Article::STATUS_OFFLINE)->count(),
            'today_created' => Article::whereDate('created_at', today())->count(),
            'total_views' => Article::sum('view_count'),
        ];
    }

    /**
     * 按状态统计
     *
     * @return array
     */
    public function getStatusStatistics(): array
    {
        return Article::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * 按部门统计
     *
     * @return array
     */
    public function getDeptStatistics(): array
    {
        return Article::selectRaw('dept_id, COUNT(*) as count')
            ->groupBy('dept_id')
            ->with('dept:id,dept_name')
            ->get()
            ->map(function ($item) {
                return [
                    'dept_id' => $item->dept_id,
                    'dept_name' => $item->dept?->dept_name ?? '未知部门',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * 按创建人统计
     *
     * @return array
     */
    public function getCreatorStatistics(): array
    {
        return Article::selectRaw('created_by, COUNT(*) as count')
            ->groupBy('created_by')
            ->with('creator:id,username,nickname')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->created_by,
                    'username' => $item->creator?->username ?? '未知用户',
                    'nickname' => $item->creator?->nickname ?? '',
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    // ==================== 批量操作 ====================

    /**
     * 批量更新状态
     *
     * @param array $ids 文章ID数组
     * @param int $status 新状态
     * @return int 更新的记录数
     */
    public function batchUpdateStatus(array $ids, int $status): int
    {
        if (empty($ids)) {
            return 0;
        }

        return Article::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * 批量删除
     *
     * @param array $ids 文章ID数组
     * @return int 删除的记录数
     */
    public function batchDelete(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return Article::whereIn('id', $ids)->delete();
    }

    /**
     * 批量更新排序
     *
     * @param array $sorts 排序数据 [id => sort]
     * @return bool
     */
    public function batchUpdateSort(array $sorts): bool
    {
        foreach ($sorts as $id => $sort) {
            Article::where('id', $id)->update(['sort' => $sort]);
        }
        return true;
    }
}
