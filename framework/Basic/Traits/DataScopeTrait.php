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

trait DataScopeTrait
{
    /**
     * 当前登录用户ID
     */
    protected static ?int $currentAdminId = null;

    /**
     * 当前用户数据权限配置 [scope, dept_ids]
     */
    protected static array $dataScope = [];

    /**
     * 初始化数据权限
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
     */
    public static function clearDataScope(): void
    {
        self::$currentAdminId = null;
        self::$dataScope = [];
    }

    /**
     * 数据权限查询作用域
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
