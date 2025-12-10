<?php

declare(strict_types=1);

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
        $model = $this->service->getModel();
        $columns = $model->getFields();

        foreach ($data as $col => $v) {
            if (!in_array($col, $columns) && !in_array($col, $skipKeys)) {
                unset($data[$col]);
            }
        }

        return $data;
    }
}
