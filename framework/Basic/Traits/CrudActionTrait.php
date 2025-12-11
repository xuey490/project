<?php

declare(strict_types=1);

namespace Framework\Basic\Traits;

use Symfony\Component\HttpFoundation\Request;
use Framework\Utils\Json;

trait CrudActionTrait
{
    public function index(Request $request)
    {
        try {
            [$where, $format, $limit, $field, $order, $page] = $this->selectInput($request);

            $total = $this->service->getCount($where);
            $list  = $this->service->selectList($where, $field, $page, $limit, $order);

            $methodMap = [
                'select'     => 'formatSelect',
                'tree'       => 'formatTree',
                'table_tree' => 'formatTree',
                'normal'     => 'formatNormal',
            ];

            $method = $methodMap[$format] ?? 'formatNormal';
            return $this->$method($list, $total);

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function show(Request $request)
    {
        try {
            $id = $request->get('id');
            $data = $this->service->get($id);
			
            if (!$data) {
                return $this->fail('数据不存在');
            }

            return Json::success('ok', $data->toArray());

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $this->insertInput($request);

            if ($this->validator && !$this->validator->scene('store')->check($data)) {
                return $this->fail($this->validator->getError());
            }

            $model = $this->service->save($data);
            return Json::success('ok', $model->toArray());

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $id = $request->attributes->get('id');
            $data = $this->insertInput($request);

            if ($this->validator && !$this->validator->scene('update')->check($data)) {
                return $this->fail($this->validator->getError());
            }

            $this->service->update($id, $data);
            return $this->success();

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $id = $request->attributes->get('id');

            $this->service->delete($id);
            return $this->success();

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}
