<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ExcelExportService;
use Framework\Utils\Json;
use Framework\Utils\DateTime;
use Framework\Utils\Tree;
use Framework\Basic\BaseController;
use Framework\Basic\BaseService;

class Crud extends BaseController
{
	
    public static array $prefixes = [
        'IN_', 'LIKE_', 'PREFIX_', 'EQ_', 'GT_', 'LT', 'GTE_', 'LTE_', 'NE_', 'BETWEEN_'
    ];
	
    public function __construct(
        ?BaseService $service = null,
        ?object $validate = null
    ) {
		
        parent::__construct($service, $validate);
    }
	
	
    /**
     * 设置 Request
     *
     * ⚠️ 必须在中间件处理后调用，保证获取的是最新过滤的 Request
     */	
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

	/**
     * 获取当前 Request
     *
     * 权威来源，永远返回中间件过滤后的最新 Request
     */
    public function getRequest(): Request
    {
        if ($this->request === null) {
            throw new \RuntimeException('Request 尚未设置，请先调用 setRequest()');
        }

        return $this->request;
    }

    /**
     * 获取当前 Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * 示例通用 GET 方法
     * ⚠️ 控制器动作中应使用 $this->getRequest() 获取 Request
     */
    protected function getParam(string $key, $default = null): mixed
    {
        return $this->getRequest()->query->get($key, $default);
    }

    protected function postParam(string $key, $default = null): mixed
    {
        return $this->getRequest()->request->get($key, $default);
    }
	
    /**
     * 列表
     */
    public function index(Request $request): Response
    {
        try {
            [$where, $format, $limit, $field, $order, $page] = $this->selectInput($request);

            $methods = [
                'select'     => 'formatSelect',
                'tree'       => 'formatTree',
                'table_tree' => 'formatTableTree',
                'normal'     => 'formatNormal',
            ];
            $formatFunction = $methods[$format] ?? 'formatNormal';

            $total = $this->service->getCount($where);
            $list  = $this->service->selectList($where, $field, $page, $limit, $order, [], false);

            return $this->$formatFunction($list, $total);
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    public function select(Request $request): Response
    {
        try {
            [$where, $format, $limit, $field, $order] = $this->selectInput($request);
            $data = $this->service->selectList($where, $field, 0, 99999, $order, [], true);
            return Json::success('ok', $data);
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 创建表单
     */
    public function create(Request $request): Response
    {
        try {
            throw new \Exception('表单不存在');
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 插入
     */
    public function store(Request $request): Response
    {
        try {
            $data = $this->insertInput($request);

            if ($this->validate && !$this->validate->scene('store')->check($data)) {
                throw new \Exception($this->validate->getError());
            }

            $model = $this->service->save($data);
            if (empty($model)) {
                throw new \Exception('插入失败');
            }

            $pk = $model->getPk();
            return Json::success('ok', [$pk => $model->getAttribute($pk)]);
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 详情
     */
    public function show(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $data = $this->service->get($id);

            if (empty($data)) {
                throw new \Exception('数据未找到');
            }

            return Json::success('ok', $data->toArray());
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    public function edit(Request $request): Response
    {
        try {
            throw new \Exception('表单不存在');
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 更新
     */
    public function update(Request $request): Response
    {
        try {
            $id   = $request->attributes->get('id');
            $data = $this->insertInput($request);

            if ($this->validate && !$this->validate->scene('update')->check($data)) {
                throw new \Exception($this->validate->getError());
            }

            if (empty($id)) {
                $model = $this->service->getModel();
                $primaryKey = $model->getKeyName();
                if (!isset($data[$primaryKey])) {
                    throw new \Exception("缺少参数: $primaryKey");
                }
                $id = $data[$primaryKey];
            }

            $this->service->update($id, $data);
            return Json::success('ok');
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 修改状态
     */
    public function changeStatus(Request $request): Response
    {
        try {
            $data = $this->insertInput($request);
            $model = $this->service->getModel();
            $pk = $model->getKeyName();

            if (!isset($data[$pk])) {
                throw new \Exception("缺少主键");
            }

            $target = $model->findOrFail($data[$pk]);
            $target->fill($data);

            if (!$target->save()) {
                throw new \RuntimeException("更新失败");
            }

            return Json::success('ok');
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * Excel导出
     */
    public function export(Request $request): Response
    {
        try {
            $params = $request->query->all() + $request->request->all();
            $args   = $params['query'] ?? [];

            $result = ExcelExportService::export($params, function ($chunkHandler) use ($args) {

                $model = $this->service->getModel();
                $allow = $model->getFields();
                $query = $model->query();

                $where = $this->buildWhereConditions($args, $allow);

                foreach ($where as $cond) {
                    if (is_array($cond) && $cond[1] === 'IN') {
                        $query->whereIn($cond[0], $cond[2]);
                    } else {
                        $query->where($cond);
                    }
                }

                $query->chunk(1000, $chunkHandler);
            });

            return Json::success("导出成功", $result);
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 删除
     */
    public function destroy(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $data = $request->request->get('data', []);

            $data = $id ?: $data;

            if (empty($data)) {
                throw new \Exception('参数错误');
            }

            $result = $this->service->transaction(function () use ($data) {
                $ids = is_array($data) ? $data : explode(',', $data);

                $deleted = [];
                foreach ($ids as $id) {
                    $item = $this->service->get($id);
                    if (!$item) {
                        continue;
                    }
                    $item->delete();
                    $deleted[] = $item->getPkValue();
                }
                return $deleted;
            });

            return Json::success("ok", $result);
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 恢复删除
     */
    public function recovery(Request $request): Response
    {
        try {
            $id = $request->attributes->get('id');
            $data = $request->request->get('data', []);
            $data = $id ?: $data;

            if (empty($data)) {
                throw new \Exception("参数错误");
            }

            $this->service->transaction(function () use ($data) {
                $ids = is_array($data) ? $data : explode(',', $data);
                foreach ($ids as $id) {
                    $this->service->update($id, ['delete' => null]);
                }
            });

            return Json::success("操作成功");
        } catch (\Throwable $e) {
            return Json::fail($e->getMessage());
        }
    }

    /**
     * 过滤插入数据
     */
    protected function insertInput(Request $request): array
    {
        $data = $this->inputFilter($request->request->all());
        if (isset($data['password'])) {
            //$data['password'] = JwtAuth::passwordHash($data['password']);
        }
        return $data;
    }

    /**
     * WHERE 拼装
     */
    protected function buildWhereConditions(array $params, array $allowColumns): array
    {
        $where = [];

        foreach ($params as $column => $value) {

            $prefix = '';
            $actualColumn = $column;

            if (preg_match('/^([A-Z_]+)_(.*)$/', $column, $m)) {
                $prefix = $m[1];
                $actualColumn = strtolower($m[2]);
            }

            if (!in_array($actualColumn, $allowColumns, true)) {
                continue;
            }

            switch ($prefix) {
                case 'IN':
                    $where[] = [$actualColumn, 'IN', (array)$value];
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

    protected function selectInput(Request $request): array
    {
        $params = $request->query->all() + $request->request->all();

        $field  = $params['field'] ?? '*';
        $sort   = $params['order'] ?? 'create_time';
        $format = $params['format'] ?? 'normal';
        $limit  = max(1, (int)($params['limit'] ?? 10));
        $page   = max(1, (int)($params['page'] ?? 1));

        $model = $this->service->getModel();
        $allow = $model->getFields();

        $order = '';
        [$col, $rank] = array_pad(explode(' ', $sort), 2, 'desc');
        if (in_array($col, $allow)) {
            $order = "$col $rank";
        }

        if (!in_array($field, $allow)) {
            $field = '*';
        }

        $where = $this->buildWhereConditions($params, $allow);

        return [$where, $format, $limit, $field, $order, $page];
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

    /**
     * 格式化树
     */
    protected function formatTree($items): Response
    {
        $nodes = [];
        foreach ($items as $item) {
            $nodes[] = [
                'name'  => $item->title ?? $item->name ?? $item->id,
                'value' => (string)$item->id,
                'id'    => $item->id,
                'pid'   => $item->pid,
            ];
        }
        $tree = new Tree($nodes);
        return Json::success('ok', $tree->getTree());
    }

}
