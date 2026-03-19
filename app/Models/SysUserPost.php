<?php

declare(strict_types=1);

/**
 * 用户岗位关联模型
 *
 * @package App\Models
 * @author  Genie
 * @date    2026-03-19
 */

namespace App\Models;

use Framework\Basic\BaseLaORMModel;

/**
 * SysUserPost 用户岗位关联模型
 *
 * 用户与岗位的多对多中间表模型
 *
 * @property int $user_id 用户ID
 * @property int $post_id 岗位ID
 */
class SysUserPost extends BaseLaORMModel
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'sys_user_post';

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = null;

    /**
     * 是否自增主键
     * @var bool
     */
    public $incrementing = false;

    /**
     * 是否包含时间戳
     * @var bool
     */
    public $timestamps = false;

    /**
     * 可填充字段
     * @var array
     */
    protected $fillable = [
        'user_id',
        'post_id',
    ];

    /**
     * 类型转换
     * @var array
     */
    protected $casts = [
        'user_id' => 'integer',
        'post_id' => 'integer',
    ];

    // ==================== 业务方法 ====================

    /**
     * 根据用户ID获取岗位ID列表
     *
     * @param int $userId 用户ID
     * @return array
     */
    public static function getPostIdsByUser(int $userId): array
    {
        return self::where('user_id', $userId)->pluck('post_id')->toArray();
    }

    /**
     * 根据岗位ID获取用户ID列表
     *
     * @param int $postId 岗位ID
     * @return array
     */
    public static function getUserIdsByPost(int $postId): array
    {
        return self::where('post_id', $postId)->pluck('user_id')->toArray();
    }

    /**
     * 批量保存用户岗位关联
     *
     * @param int   $userId  用户ID
     * @param array $postIds 岗位ID列表
     * @return void
     */
    public static function saveUserPosts(int $userId, array $postIds): void
    {
        // 先删除原有关联
        self::where('user_id', $userId)->delete();

        // 批量插入新关联
        if (!empty($postIds)) {
            $data = array_map(function ($postId) use ($userId) {
                return [
                    'user_id' => $userId,
                    'post_id' => $postId,
                ];
            }, $postIds);

            self::insert($data);
        }
    }

    /**
     * 批量保存岗位用户关联
     *
     * @param int   $postId  岗位ID
     * @param array $userIds 用户ID列表
     * @return void
     */
    public static function savePostUsers(int $postId, array $userIds): void
    {
        // 先删除原有关联
        self::where('post_id', $postId)->delete();

        // 批量插入新关联
        if (!empty($userIds)) {
            $data = array_map(function ($userId) use ($postId) {
                return [
                    'user_id' => $userId,
                    'post_id' => $postId,
                ];
            }, $userIds);

            self::insert($data);
        }
    }

    /**
     * 检查用户是否拥有指定岗位
     *
     * @param int $userId 用户ID
     * @param int $postId 岗位ID
     * @return bool
     */
    public static function hasPost(int $userId, int $postId): bool
    {
        return self::where('user_id', $userId)
            ->where('post_id', $postId)
            ->exists();
    }
}
