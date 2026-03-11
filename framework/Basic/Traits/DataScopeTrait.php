<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-10
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * 数据权限隔离 Trait
 * 
 * 该 Trait 提供数据权限隔离功能，用于实现细粒度的数据访问控制。
 * 根据用户的权限范围（scope）自动过滤查询结果，确保用户只能访问其权限范围内的数据。
 * 
 * 支持的权限范围：
 * - 1: 全部数据权限（不限制）
 * - 2: 本部门数据
 * - 3: 本部门及子部门数据
 * - 4: 仅本人数据
 * - 5: 本部门及子部门 + 本人数据
 * - 6: 自定义部门数据
 * - 其他: 无权限
 * 
 * @package Framework\Basic\Traits
 */
trait DataScopeTrait
{
    /**
     * 当前登录用户ID
     * @var int|null
     */
    protected static ?int $currentAdminId = null;

    /**
     * 当前用户数据权限配置 [scope, dept_ids]
     * @var array{scope: int, dept_ids: array<int>}
     */
    protected static array $dataScope = [];

    /**
     * 初始化数据权限
     * 
     * 在请求开始时调用，设置当前用户的数据权限信息。
     * 应在用户认证完成后立即调用此方法。
     * 
     * @param int $adminId 当前登录用户ID
     * @param int $scope 数据权限范围（1-6）
     * @param array<int> $deptIds 部门ID列表
     * @return void
     */
    public static function initDataScope(int $adminId, int $scope, array $deptIds): void
    {
        self::$currentAdminId = $adminId;
        self::$dataScope = [
            'scope'     => $scope,
            'dept_ids'  => $deptIds
        ];
    }

    /**
     * 清空数据权限（避免请求污染）
     * 
     * 在请求结束时调用，清理静态变量，防止跨请求数据污染。
     * 建议在中间件的 terminate 阶段调用。
     * 
     * @return void
     */
    public static function clearDataScope(): void
    {
        self::$currentAdminId = null;
        self::$dataScope = [];
    }

    /**
     * 数据权限查询作用域
     * 
     * 根据用户的数据权限范围自动添加查询条件。
     * 支持多种权限范围的自动过滤。
     * 
     * @param Builder $query Eloquent 查询构建器
     * @return Builder 添加权限条件后的查询构建器
     */
    public function scopeWithDataScope(Builder $query): Builder
    {
        // 超级管理员或未初始化权限，直接返回
        if (isset($this::$isSuperAdmin) && $this::$isSuperAdmin || empty(self::$dataScope)) {
            return $query;
        }

        $scope = self::$dataScope['scope'];
        $deptIds = self::$dataScope['dept_ids'];
        $adminId = self::$currentAdminId;

        switch ($scope) {
            case 1:
                break;
            case 2:
                $query->where('dept_id', $deptIds[0] ?? 0);
                break;
            case 3:
                $query->whereIn('dept_id', $deptIds);
                break;
            case 4:
                $query->where('create_by', $adminId);
                break;
            case 5:
                $query->where(function ($q) use ($deptIds, $adminId) {
                    $q->whereIn('dept_id', $deptIds)->orWhere('create_by', $adminId);
                });
                break;
            case 6:
                $query->whereIn('dept_id', $deptIds);
                break;
            default:
                $query->whereRaw('1=0');
                break;
        }

        return $query;
    }
}
