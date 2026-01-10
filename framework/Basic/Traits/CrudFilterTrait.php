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

use Symfony\Component\HttpFoundation\Request;

trait CrudFilterTrait
{
    protected function insertInput(Request $request): array
    {
        $data = $request->request->all();
        return $this->inputFilter($data);
    }

    protected function inputFilter(array $data, array $skipKeys = []): array
    {
        $model   = $this->service->getModel();
        $columns = $model->getFields();

        foreach ($data as $col => $v) {
            if (! in_array($col, $columns) && ! in_array($col, $skipKeys)) {
                unset($data[$col]);
            }
        }

        return $data;
    }
}
