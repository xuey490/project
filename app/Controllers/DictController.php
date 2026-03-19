<?php

declare(strict_types=1);

/**
 * 数据字典控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysDictService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * DictController 数据字典控制器
 */
class DictController extends BaseController
{
    /**
     * 字典服务
     * @var SysDictService
     */
    protected SysDictService $dictService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->dictService = new SysDictService();
    }

    // ==================== 字典类型 ====================

    /**
     * 获取字典类型列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/list', methods: ['GET'], name: 'dict.type.list')]
    #[Auth(required: true)]
    public function typeList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->dictService->getTypeList($params);

        return $this->success($result);
    }

    /**
     * 获取字典类型详情
     *
     * @param Request $request 请求对象
     * @param int     $id      字典类型ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/detail/{id}', methods: ['GET'], name: 'dict.type.detail')]
    #[Auth(required: true)]
    public function typeDetail(Request $request, int $id): BaseJsonResponse
    {
        $result = $this->dictService->getTypeDetail($id);

        if (!$result) {
            return $this->fail('字典类型不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建字典类型
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/create', methods: ['POST'], name: 'dict.type.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function typeCreate(Request $request): BaseJsonResponse
    {
        $data = [
            'dict_name' => $this->input('dict_name', ''),
            'dict_code' => $this->input('dict_code', ''),
            'status' => (int)$this->input('status', 1),
            'remark' => $this->input('remark', ''),
        ];

        if (empty($data['dict_name']) || empty($data['dict_code'])) {
            return $this->fail('字典名称和编码不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $dictType = $this->dictService->createType($data, $operator);
            return $this->success(['id' => $dictType->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典类型
     *
     * @param Request $request 请求对象
     * @param int     $id      字典类型ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/update/{id}', methods: ['PUT'], name: 'dict.type.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function typeUpdate(Request $request, int $id): BaseJsonResponse
    {
        $data = [
            'dict_name' => $this->input('dict_name', ''),
            'dict_code' => $this->input('dict_code', ''),
            'status' => $this->input('status') !== '' ? (int)$this->input('status') : null,
            'remark' => $this->input('remark', ''),
        ];

        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        $operator = $this->getOperatorId($request);

        try {
            $this->dictService->updateType($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除字典类型
     *
     * @param Request $request 请求对象
     * @param int     $id      字典类型ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/delete/{id}', methods: ['DELETE'], name: 'dict.type.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function typeDelete(Request $request, int $id): BaseJsonResponse
    {
        try {
            $this->dictService->deleteType($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典类型状态
     *
     * @param Request $request 请求对象
     * @param int     $id      字典类型ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/type/status/{id}', methods: ['PUT'], name: 'dict.type.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function typeUpdateStatus(Request $request, int $id): BaseJsonResponse
    {
        $status = (int)$this->input('status', 1);

        $result = $this->dictService->updateTypeStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

    // ==================== 字典数据 ====================

    /**
     * 获取字典数据列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/list', methods: ['GET'], name: 'dict.data.list')]
    #[Auth(required: true)]
    public function dataList(Request $request): BaseJsonResponse
    {
        $params = $request->query->all();
        $result = $this->dictService->getDataList($params);

        return $this->success($result);
    }

    /**
     * 根据字典编码获取字典数据
     *
     * @param Request $request 请求对象
     * @param string  $dictCode 字典编码
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/code/{dictCode}', methods: ['GET'], name: 'dict.data.byCode')]
    public function dataByCode(Request $request, string $dictCode): BaseJsonResponse
    {
        $data = $this->dictService->getDictDataByCode($dictCode);

        return $this->success($data);
    }

    /**
     * 获取字典数据详情
     *
     * @param Request $request 请求对象
     * @param int     $id      字典数据ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/detail/{id}', methods: ['GET'], name: 'dict.data.detail')]
    #[Auth(required: true)]
    public function dataDetail(Request $request, int $id): BaseJsonResponse
    {
        $result = $this->dictService->getDataDetail($id);

        if (!$result) {
            return $this->fail('字典数据不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 创建字典数据
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/create', methods: ['POST'], name: 'dict.data.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function dataCreate(Request $request): BaseJsonResponse
    {
        $data = [
            'dict_type_id' => (int)$this->input('dict_type_id', 0),
            'dict_label' => $this->input('dict_label', ''),
            'dict_value' => $this->input('dict_value', ''),
            'dict_sort' => (int)$this->input('dict_sort', 0),
            'color' => $this->input('color', ''),
            'status' => (int)$this->input('status', 1),
            'remark' => $this->input('remark', ''),
        ];

        if (empty($data['dict_label']) || empty($data['dict_value'])) {
            return $this->fail('字典标签和字典值不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $dictData = $this->dictService->createData($data, $operator);
            return $this->success(['id' => $dictData->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典数据
     *
     * @param Request $request 请求对象
     * @param int     $id      字典数据ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/update/{id}', methods: ['PUT'], name: 'dict.data.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function dataUpdate(Request $request, int $id): BaseJsonResponse
    {
        $data = [
            'dict_label' => $this->input('dict_label', ''),
            'dict_value' => $this->input('dict_value', ''),
            'dict_sort' => $this->input('dict_sort') !== '' ? (int)$this->input('dict_sort') : null,
            'color' => $this->input('color', ''),
            'status' => $this->input('status') !== '' ? (int)$this->input('status') : null,
            'remark' => $this->input('remark', ''),
        ];

        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        $operator = $this->getOperatorId($request);

        try {
            $this->dictService->updateData($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除字典数据
     *
     * @param Request $request 请求对象
     * @param int     $id      字典数据ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/delete/{id}', methods: ['DELETE'], name: 'dict.data.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function dataDelete(Request $request, int $id): BaseJsonResponse
    {
        try {
            $this->dictService->deleteData($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新字典数据状态
     *
     * @param Request $request 请求对象
     * @param int     $id      字典数据ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/dict/data/status/{id}', methods: ['PUT'], name: 'dict.data.status')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function dataUpdateStatus(Request $request, int $id): BaseJsonResponse
    {
        $status = (int)$this->input('status', 1);

        $result = $this->dictService->updateDataStatus($id, $status);

        return $result
            ? $this->success([], '状态更新成功')
            : $this->fail('状态更新失败');
    }

}
