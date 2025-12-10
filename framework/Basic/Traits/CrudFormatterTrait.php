<?php

declare(strict_types=1);

namespace Framework\Basic\Traits;

use Framework\Utils\Json;
use Framework\Utils\Tree;

trait CrudFormatterTrait
{
    protected function formatSelect($list, int $total)
    {
        return Json::success('ok', [
            'total' => $total,
            'items' => $list,
        ]);
    }

    protected function formatNormal($list, int $total)
    {
        return Json::success('ok', [
            'total' => $total,
            'list'  => $list,
        ]);
    }

    protected function formatTree($items)
    {
        $nodes = [];
        foreach ($items as $item) {
            $nodes[] = [
                'name'  => $item->title ?? $item->name ?? $item->id,
                'value' => (string)$item->id,
                'id'    => $item->id,
                'pid'   => $item->pid ?? 0,
            ];
        }
        $tree = new Tree($nodes);
        return Json::success('ok', $tree->getTree());
    }
}
