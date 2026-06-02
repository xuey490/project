<?php

declare(strict_types=1);

/**
 * @Filename: ArticleDao.php
 * @Date: 2026-06-02
 * @Developer: blue2004
 * @Email: xuey863toy@gmail.com
 */

namespace App\Dao;

use App\Models\Article;
use Framework\Basic\BaseDao;

/**
 * 文章数据访问层
 *
 * 继承 BaseDao 获得完整 CRUD 能力，通过 __call 代理到 ORM 适配器。
 * 此处只需声明模型，通用查询方法（selectList / count / get 等）由父类提供。
 *
 * 常用代理方法（父类 @method 已声明）：
 * - selectList(array $where, ...) : array   按条件分页查询
 * - count(array $where)           : int     按条件计数
 * - get($id)                      : array   按主键查询单条
 * - save(array $data)             : mixed   新增
 * - update($id, array $data)      : bool    更新
 * - destroy($id)                  : bool    软删除
 */
class ArticleDao extends BaseDao
{
    /**
     * 绑定 Article 模型
     */
    protected function setModel(): string
    {
        return Article::class;
    }

    /**
     * 按分类查询文章列表（自定义扩展示例）
     *
     * @param int    $categoryId 分类id
     * @param int    $page       页码
     * @param int    $limit      每页条数
     * @param string $order      排序字段，如 'sort asc'
     * @return array
     */
    public function getByCategory(int $categoryId, int $page = 1, int $limit = 10, string $order = 'sort asc'): array
    {
        return $this->selectList(
            ['EQ_category_id' => $categoryId, 'EQ_status' => 1],
            '*',
            $page,
            $limit,
            $order,
            [],
            true  // 启用 search 模式解析 EQ_ 等前缀
        );
    }

    /**
     * 按租户+状态查询（多条件组合示例）
     *
     * @param int $tenantId 租户id
     * @param int $status   状态（1正常）
     * @return array
     */
    public function getByTenant(int $tenantId, int $status = 1): array
    {
        return $this->selectList(
            ['EQ_tenant_id' => $tenantId, 'EQ_status' => $status],
            '*',
            0,
            0,
            '',
            [],
            true  // 启用 search 模式解析 EQ_ 等前缀
        );
    }
}
