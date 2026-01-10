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

use Framework\Utils\DateTime;
use Symfony\Component\HttpFoundation\Request;

trait CrudQueryTrait
{
    protected array $prefixes = [
        'IN_', 'LIKE_', 'EQ_', 'GT_', 'LT_', 'GTE_', 'LTE_', 'NE_', 'BETWEEN_',
    ];

    protected function selectInput(Request $request): array
    {
        $params = $request->query->all() + $request->request->all();

        $field  = $params['field'] ?? '*';
        $sort   = $params['order'] ?? 'id desc';
        $format = $params['format'] ?? 'normal';
        $limit  = max(1, (int) ($params['limit'] ?? 10));
        $page   = max(1, (int) ($params['page'] ?? 1));

        $model = $this->service->getModel();
        $allow = $model->getFields();

        // 排序处理
        $order        = '';
        [$col, $rank] = array_pad(explode(' ', $sort), 2, 'desc');
        if (in_array($col, $allow)) {
            $order = "$col $rank";
        }

        // 字段验证
        if ($field !== '*' && ! in_array($field, $allow)) {
            $field = '*';
        }

        $where = $this->buildWhereConditions($params, $allow);

        return [$where, $format, $limit, $field, $order, $page];
    }

    protected function buildWhereConditions(array $params, array $allowColumns): array
    {
        $where = [];

        foreach ($params as $column => $value) {
            $prefix       = '';
            $actualColumn = $column;

            if (preg_match('/^([A-Z_]+)_(.*)$/', $column, $m)) {
                $prefix       = rtrim($m[1], '_');
                $actualColumn = strtolower($m[2]);
            }

            if (! in_array($actualColumn, $allowColumns)) {
                continue;
            }

            switch ($prefix) {
                case 'IN':
                    $where[] = [$actualColumn, 'IN', (array) $value];
                    break;

                case 'LIKE':
                    $where[] = [$actualColumn, 'LIKE', "%{$value}%"];
                    break;

                case 'GT':
                    $where[] = [$actualColumn, '>', $value];
                    break;

                case 'LT':
                    $where[] = [$actualColumn, '<', $value];
                    break;

                case 'GTE':
                    $where[] = [$actualColumn, '>=', $value];
                    break;

                case 'LTE':
                    $where[] = [$actualColumn, '<=', $value];
                    break;

                case 'NE':
                    $where[] = [$actualColumn, '!=', $value];
                    break;

                case 'BETWEEN':
                    if (is_array($value) && count($value) === 2) {
                        $where[] = [$actualColumn, '>=', DateTime::dateTimeStringToTimestamp($value[0])];
                        $where[] = [$actualColumn, '<=', DateTime::dateTimeStringToTimestamp($value[1])];
                    }
                    break;

                default:
                    $where[] = [$actualColumn, '=', $value];
            }
        }

        return $where;
    }
}
