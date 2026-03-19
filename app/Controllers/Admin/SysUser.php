<?php

namespace App\Controllers\Admin;

use App\Services\SysUserService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class SysUser
{
    #[Autowire]
    protected SysUserService $userService;
    
    public function index(Request $request)
    {
        $params = $request->query->all();
        $data = $this->userService->getList($params);
        return BaseJsonResponse::success($data);
    }
    
    public function show(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->userService->getById($id);
        return BaseJsonResponse::success($data);
    }
    
    public function store(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->userService->create($content);
        return BaseJsonResponse::success([], 'Created successfully');
    }
    
    public function update(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
		
		
		
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
		
		return BaseJsonResponse::success($content);
		
		
        $this->userService->update($id, $content);
        return BaseJsonResponse::success([], 'Updated successfully');
    }
    
    public function destroy(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $this->userService->delete([$id]);
        return BaseJsonResponse::success([], 'Deleted successfully');
    }

    public function batchDestroy(Request $request)
    {
        // Try to get IDs from body (JSON) or query params
        $payload = json_decode($request->getContent(), true);
        $ids = $payload['ids'] ?? $payload ?? $request->query->get('ids') ?? [];
        
        if (!is_array($ids)) {
            $ids = explode(',', (string)$ids);
        }
        $ids = array_filter($ids, fn($id) => is_numeric($id));
        
        if (empty($ids)) {
            return BaseJsonResponse::fail('No IDs provided');
        }
        
        $this->userService->delete($ids);
        return BaseJsonResponse::success([], 'Batch deleted successfully');
    }
    
    public function changeStatus(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? ($payload['id'] ?? null) ?? $request->attributes->get('id') ?? 0);
        $status = (int) ($payload['enabled'] ?? $request->query->get('enabled') ?? 1);
        $this->userService->changeStatus($id, $status);
        return BaseJsonResponse::success([], 'Status updated');
    }

    public function resetPassword(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $id = (int) ($payload['userId'] ?? $payload['id'] ?? 0);
        $password = (string) ($payload['password'] ?? '');
        
        if ($id <= 0 || empty($password)) {
            return BaseJsonResponse::fail('Invalid parameters');
        }
        
        $this->userService->resetPassword($id, $password);
        return BaseJsonResponse::success([], 'Password reset successfully');
    }

    public function grantRole(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $id = (int) ($payload['userId'] ?? $payload['id'] ?? 0);
        $roleIds = $payload['roleIds'] ?? $payload['role_ids'] ?? [];
        
        if ($id <= 0) {
            return BaseJsonResponse::fail('Invalid parameters');
        }
        
        $this->userService->grantRole($id, $roleIds);
        return BaseJsonResponse::success([], 'Roles granted successfully');
    }
}
