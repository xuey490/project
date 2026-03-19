<?php

declare(strict_types=1);

/**
 * 系统岗位服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Services;

use App\Models\SysPost;
use App\Models\SysUserPost;
use App\Dao\SysPostDao;
use Framework\Basic\BaseService;

/**
 * SysPostService 岗位服务
 *
 * 处理岗位相关的业务逻辑
 */
class SysPostService extends BaseService
{
    /**
     * DAO 实例
     * @var SysPostDao
     */
    protected SysPostDao $postDao;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->postDao = new SysPostDao();
    }

    /**
     * 获取岗位列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $postCode = $params['post_code'] ?? '';
        $postName = $params['post_name'] ?? '';
        $enabled = $params['enabled'] ?? '';

        $query = SysPost::query()->whereNull('deleted_at');

        if ($postCode !== '') {
            $query->where('post_code', 'like', "%{$postCode}%");
        }

        if ($postName !== '') {
            $query->where('post_name', 'like', "%{$postName}%");
        }

        if ($enabled !== '') {
            $query->where('enabled', (int)$enabled);
        }

        $list = $query->orderBy('post_sort')->get()->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item = $this->formatPost($item);
        }

        return $list;
    }

    /**
     * 获取岗位详情
     *
     * @param int $postId 岗位ID
     * @return array|null
     */
    public function getDetail(int $postId): ?array
    {
        $post = SysPost::find($postId);

        if (!$post) {
            return null;
        }

        $data = $this->formatPost($post);

        // 获取岗位下的用户数量
        $data['user_count'] = SysUserPost::where('post_id', $postId)->count();

        return $data;
    }

    /**
     * 创建岗位
     *
     * @param array $data     岗位数据
     * @param int   $operator 操作人ID
     * @return SysPost|null
     */
    public function create(array $data, int $operator = 0): ?SysPost
    {
        // 检查岗位编码是否存在
        if ($this->postDao->isPostCodeExists($data['post_code'])) {
            throw new \Exception('岗位编码已存在');
        }

        // 设置审计字段
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        return SysPost::create($data);
    }

    /**
     * 更新岗位
     *
     * @param int   $postId   岗位ID
     * @param array $data     岗位数据
     * @param int   $operator 操作人ID
     * @return bool
     */
    public function update(int $postId, array $data, int $operator = 0): bool
    {
        $post = SysPost::find($postId);
        if (!$post) {
            throw new \Exception('岗位不存在');
        }

        // 检查岗位编码是否重复
        if (isset($data['post_code']) && $data['post_code'] !== $post->post_code) {
            if ($this->postDao->isPostCodeExists($data['post_code'], $postId)) {
                throw new \Exception('岗位编码已存在');
            }
        }

        // 设置审计字段
        $data['updated_by'] = $operator;

        $post->fill($data);
        return $post->save();
    }

    /**
     * 删除岗位
     *
     * @param int $postId 岗位ID
     * @return bool
     */
    public function delete(int $postId): bool
    {
        $post = SysPost::find($postId);
        if (!$post) {
            return false;
        }

        // 检查是否有用户关联
        if ($post->hasUsers()) {
            throw new \Exception('该岗位下存在用户，无法删除');
        }

        // 软删除岗位
        return $post->delete();
    }

    /**
     * 更新岗位状态
     *
     * @param int $postId  岗位ID
     * @param int $enabled 状态
     * @return bool
     */
    public function updateEnabled(int $postId, int $enabled): bool
    {
        return $this->postDao->updateEnabled($postId, $enabled);
    }

    /**
     * 获取所有启用的岗位
     *
     * @return array
     */
    public function getAllEnabled(): array
    {
        return $this->postDao->getAllEnabled();
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化岗位数据
     *
     * @param SysPost|array $post 岗位
     * @return array
     */
    protected function formatPost(SysPost|array $post): array
    {
        if ($post instanceof SysPost) {
            $data = $post->toArray();
        } else {
            $data = $post;
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
        $data['enabled_text'] = $data['enabled'] === SysPost::ENABLED_ENABLED ? '启用' : '禁用';

        return $data;
    }
}
