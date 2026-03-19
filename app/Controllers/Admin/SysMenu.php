<?php

namespace App\Controllers\Admin;

use App\Services\SysMenuService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class SysMenu
{
    #[Autowire]
    protected SysMenuService $menuService;

    public function index(Request $request)
    {
        $params = $request->query->all();
        $data = $this->menuService->getList($params);
        return BaseJsonResponse::success($data);
    }

    public function show(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->menuService->getById($id);
        return BaseJsonResponse::success($data);
    }
    
    public function store(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->menuService->create($content);
        return BaseJsonResponse::success([], 'Created successfully');
    }
    
    public function update(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->menuService->update($id, $content);
        return BaseJsonResponse::success([], 'Updated successfully');
    }

    public function destroy(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $this->menuService->delete([$id]);
        return BaseJsonResponse::success([], 'Deleted successfully');
    }

    public function batchDestroy(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $ids = $payload['ids'] ?? $payload ?? $request->query->get('ids') ?? [];
        
        if (!is_array($ids)) {
            $ids = explode(',', (string)$ids);
        }
        $ids = array_filter($ids, fn($id) => is_numeric($id));
        
        if (empty($ids)) {
            return BaseJsonResponse::fail('No IDs provided');
        }
        
        $this->menuService->delete($ids);
        return BaseJsonResponse::success([], 'Batch deleted successfully');
    }

    public function batchStore(Request $request)
    {
        // Placeholder for batch store
        return BaseJsonResponse::success([], 'Batch stored successfully');
    }
    
    public function changeStatus(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? ($payload['id'] ?? null) ?? $request->attributes->get('id') ?? 0);
        $status = (int) ($payload['enabled'] ?? $request->query->get('enabled') ?? 1);
        $this->menuService->changeStatus($id, $status);
        return BaseJsonResponse::success([], 'Status updated');
    }
}
