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

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

trait AutoFillable
{
    /**
     * Eloquent 会在构造函数中自动调用 initialize{TraitName} 方法
     * 这是设置 fillable 的最佳时机
     */
    public function initializeAutoFillable()
    {
        // 只有当 fillable 为空时才自动设置，允许你在模型中手动覆盖
        if (empty($this->fillable)) {
            $this->fillable($this->getCalculatedFillable());
        }
    }

    /**
     * 获取经过计算的 fillable 字段
     */
    protected function getCalculatedFillable(): array
    {
        $tableName = $this->getTable();
        // 缓存键名，区分不同表
        $cacheKey = 'table_columns_' . $tableName;
        
        // 缓存时间：例如 24 小时。生产环境 schema 不常变，建议设置久一点
        // 开发环境如果经常改表结构，可以运行 php artisan cache:clear
        $columns = Cache::remember($cacheKey, 86400, function () use ($tableName) {
            return Schema::getColumnListing($tableName);
        });

        // 全局不需要加入 fillable 的黑名单字段
        $blacklist = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'tenant_id',
            // 如果有其他想要全局排除的字段，加在这里
        ];

        // 可以在具体模型中定义 protected $guarded_fields = [] 来追加模型特有的黑名单
        if (property_exists($this, 'guarded_fields')) {
            $blacklist = array_merge($blacklist, $this->guarded_fields);
        }

        // 做差集：表所有字段 - 黑名单字段
        return array_values(array_diff($columns, $blacklist));
    }
}