<?php

namespace App\Controllers\Admin;

use App\Services\SysPostService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class SysPost
{
    #[Autowire]
    protected SysPostService $postService;
    
    public function index(Request $request)
    {
        $params = $request->query->all();
        $data = $this->postService->getList($params);
        return BaseJsonResponse::success($data);
    }
    
    public function show(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->postService->getById($id);
        return BaseJsonResponse::success($data);
    }
    
    public function store(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->postService->create($content);
        return BaseJsonResponse::success([], 'Created successfully');
    }
    
    public function update(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->postService->update($id, $content);
        return BaseJsonResponse::success([], 'Updated successfully');
    }
    
    public function destroy(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $this->postService->delete([$id]);
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
        
        $this->postService->delete($ids);
        return BaseJsonResponse::success([], 'Batch deleted successfully');
    }
    
    public function changeStatus(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? ($payload['id'] ?? null) ?? $request->attributes->get('id') ?? 0);
        $status = (int) ($payload['enabled'] ?? $request->query->get('enabled') ?? 1);
        $this->postService->changeStatus($id, $status);
        return BaseJsonResponse::success([], 'Status updated');
    }
}
