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

use Framework\Utils\Json;
use Framework\Utils\Tree;

trait CrudFormatterTrait
{
    protected function formatSelect($list, int $total)
    {
        return Json::success([
            'total' => $total,
            'items' => $list,
        ], 'ok');
    }

    protected function formatNormal($list, int $total)
    {
        return Json::success([
            'total' => $total,
            'list'  => $list,
        ], 'ok');
    }

    protected function formatTree($items)
    {
        $nodes = [];
        foreach ($items as $item) {
            $nodes[] = [
                'name'  => $item->title ?? $item->name ?? $item->id,
                'value' => (string) $item->id,
                'id'    => $item->id,
                'pid'   => $item->pid ?? 0,
            ];
        }
        $tree = new Tree($nodes);
        return Json::success($tree->getTree(), 'ok');
    }
}
