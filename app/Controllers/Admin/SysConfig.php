<?php

namespace App\Controllers\Admin;

use App\Services\SysConfigService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class SysConfig
{
    #[Autowire]
    protected SysConfigService $configService;

    public function index(Request $request)
    {
        $params = $request->query->all();
        $data = $this->configService->getList($params);
        return BaseJsonResponse::success($data);
    }

    public function show(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->configService->getById($id);
        return BaseJsonResponse::success($data);
    }

    public function store(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->configService->create($content);
        return BaseJsonResponse::success([], 'Created successfully');
    }

    public function update(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($content['id'] ?? 0);
        $this->configService->update($id, $content);
        return BaseJsonResponse::success([], 'Updated successfully');
    }

    public function destroy(Request $request)
    {
        $id = $request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id');
        $ids = [];
        if ($id) {
            $ids = explode(',', (string)$id);
        } else {
             $content = json_decode($request->getContent(), true) ?? $request->request->all();
             $ids = (array) ($content['ids'] ?? []);
        }
        
        $this->configService->delete($ids);
        return BaseJsonResponse::success([], 'Deleted successfully');
    }

    public function batchDestroy(Request $request)
    {
        return $this->destroy($request);
    }

    public function refreshCache(Request $request)
    {
        $this->configService->refreshCache();
        return BaseJsonResponse::success([], 'Cache refreshed');
    }

    public function getConfigKey(Request $request)
    {
        $key = $request->attributes->get('configKey') ?? $request->query->get('configKey');
        $data = $this->configService->getConfigValue($key);
        return BaseJsonResponse::success($data); // Note: Should check return format, sometimes string, sometimes obj
    }
}
