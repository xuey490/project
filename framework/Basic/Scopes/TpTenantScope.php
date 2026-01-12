<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2026-1-11
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic\Scopes;

use think\db\Query;
use think\Model;
use Framework\Tenant\TenantContext;

class TpTenantScope
{
    public function apply(Query $query, Model $model): void
    {
        // 超管模式忽略租户隔离
        if (TenantContext::isIgnoring()) {
            return;
        }

        $tenantId = TenantContext::getTenantId();
        if (!$tenantId) {
            return;
        }

        $fields = $model->getFields();
        if (!is_array($fields) || !array_key_exists('tenant_id', $fields)) {
            return;
        }

        $fieldKey = $model->getTable() . '.tenant_id';

        // 避免重复添加
        $options = $query->getOptions();
        $hasWhere = false;
        if (!empty($options['where'])) {
            foreach ($options['where'] as $where) {
                if (isset($where[0]) && $where[0] === $fieldKey) {
                    $hasWhere = true;
                    break;
                }
            }
        }

        if (!$hasWhere) {
            $query->where($fieldKey, '=', $tenantId);
        }
    }
}
