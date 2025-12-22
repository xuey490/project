<?php

declare(strict_types=1);

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
