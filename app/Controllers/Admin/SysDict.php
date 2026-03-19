<?php

namespace App\Controllers\Admin;

use App\Services\SysDictService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class SysDict
{
    #[Autowire]
    protected SysDictService $dictService;

    // ================= Dict Type =================

    public function listType(Request $request)
    {
        $params = $request->query->all();
        $data = $this->dictService->getTypeList($params);
        return BaseJsonResponse::success($data);
    }

    public function getType(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->dictService->getTypeById($id);
        return BaseJsonResponse::success($data);
    }

    public function addType(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->dictService->createType($content);
        return BaseJsonResponse::success([], 'Created successfully');
    }

    public function editType(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($content['id'] ?? 0);
        $this->dictService->updateType($id, $content);
        return BaseJsonResponse::success([], 'Updated successfully');
    }

    public function deleteType(Request $request)
    {
        $id = $request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id');
        $ids = [];
        if ($id) {
            $ids = explode(',', (string)$id);
        } else {
             $content = json_decode($request->getContent(), true) ?? $request->request->all();
             $ids = (array) ($content['ids'] ?? []);
        }
        
        $this->dictService->deleteType($ids);
        return BaseJsonResponse::success([], 'Deleted successfully');
    }

    public function batchDeleteType(Request $request)
    {
        return $this->deleteType($request);
    }

    public function refreshCache(Request $request)
    {
        $this->dictService->refreshCache();
        return BaseJsonResponse::success([], 'Cache refreshed');
    }
    
    public function optionSelect(Request $request)
    {
        // Return dict type list for select options
        $data = $this->dictService->getTypeList(['limit' => 1000]); // Get all (simplified)
        return BaseJsonResponse::success($data);
    }

    // ================= Dict Data =================

    public function listData(Request $request)
    {
        $params = $request->query->all();
        $data = $this->dictService->getDataList($params);
        return BaseJsonResponse::success($data);
    }

    public function getData(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->dictService->getDataById($id);
        return BaseJsonResponse::success($data);
    }

    public function addData(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->dictService->createData($content);
        return BaseJsonResponse::success([], 'Created successfully');
    }

    public function editData(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($content['id'] ?? 0);
        $this->dictService->updateData($id, $content);
        return BaseJsonResponse::success([], 'Updated successfully');
    }

    public function deleteData(Request $request)
    {
        $id = $request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id');
        $ids = [];
        if ($id) {
            $ids = explode(',', (string)$id);
        } else {
             $content = json_decode($request->getContent(), true) ?? $request->request->all();
             $ids = (array) ($content['ids'] ?? []);
        }
        
        $this->dictService->deleteData($ids);
        return BaseJsonResponse::success([], 'Deleted successfully');
    }

    public function batchDeleteData(Request $request)
    {
        return $this->deleteData($request);
    }

    public function getDicts(Request $request)
    {
        $type = $request->attributes->get('dictType') ?? $request->query->get('dictType');
        if (empty($type)) {
            // Try to get from last part of url if using route param like /system/dict/data/type/{dictType}
             // Not implemented here, assuming query or attribute
             return BaseJsonResponse::success([]);
        }
        
        $data = $this->dictService->getDictDataByType($type);
        return BaseJsonResponse::success($data);
    }
}
