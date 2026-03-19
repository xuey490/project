<?php

namespace App\Controllers\Admin;

use App\Services\SysRoleService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class SysRole
{
    #[Autowire]
    protected SysRoleService $roleService;

    public function index(Request $request)
    {
        $params = $request->query->all();
        $data = $this->roleService->getList($params);
        return BaseJsonResponse::success($data);
    }

    public function show(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->roleService->getById($id);
        return BaseJsonResponse::success($data);
    }
    
    public function store(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->roleService->create($content);
        return BaseJsonResponse::success([], 'Created successfully');
    }
    
    public function update(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->roleService->update($id, $content);
        return BaseJsonResponse::success([], 'Updated successfully');
    }

    public function destroy(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $this->roleService->delete([$id]);
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
        
        $this->roleService->delete($ids);
        return BaseJsonResponse::success([], 'Batch deleted successfully');
    }

    public function dataScope(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($content['id'] ?? 0);
        $this->roleService->update($id, $content);
        return BaseJsonResponse::success([], 'Data scope updated');
    }

    public function roleMenuIds(Request $request)
    {
        $roleId = (int) ($request->query->get('roleId') ?? $request->request->get('roleId') ?? 0);
        $ids = $this->roleService->getRoleMenuIds($roleId);
        return BaseJsonResponse::success($ids);
    }

    public function roleScopeIds(Request $request)
    {
        $roleId = (int) ($request->query->get('roleId') ?? $request->request->get('roleId') ?? 0);
        $ids = $this->roleService->getRoleScopeIds($roleId);
        return BaseJsonResponse::success($ids);
    }

    public function changeStatus(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? ($payload['id'] ?? null) ?? $request->attributes->get('id') ?? 0);
        $status = (int) ($payload['enabled'] ?? $request->query->get('enabled') ?? 1);
        $this->roleService->changeStatus($id, $status);
        return BaseJsonResponse::success([], 'Status updated');
    }

    public function allocatedUserList(Request $request)
    {
        $roleId = (int) ($request->query->get('roleId') ?? $request->request->get('roleId') ?? 0);
        $params = $request->query->all();
        $data = $this->roleService->allocatedUserList($roleId, $params);
        return BaseJsonResponse::success($data);
    }

    public function unallocatedUserList(Request $request)
    {
        $roleId = (int) ($request->query->get('roleId') ?? $request->request->get('roleId') ?? 0);
        $params = $request->query->all();
        $data = $this->roleService->unallocatedUserList($roleId, $params);
        return BaseJsonResponse::success($data);
    }

    public function authUser(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $roleId = (int) ($content['roleId'] ?? 0);
        // Support userIds (array/string) or userId (single)
        $userIds = $content['userIds'] ?? $content['userId'] ?? [];
        
        if (!is_array($userIds)) {
            $userIds = explode(',', (string)$userIds);
        }
        $userIds = array_filter($userIds, fn($id) => is_numeric($id));
        
        if ($roleId <= 0 || empty($userIds)) {
            return BaseJsonResponse::fail('Invalid params', [], 400);
        }
        
        $this->roleService->authUser($roleId, $userIds);
        return BaseJsonResponse::success([], 'Authorized successfully');
    }

    public function cancelAuthUser(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $roleId = (int) ($content['roleId'] ?? 0);
        // Support userIds or userId
        $userIds = $content['userIds'] ?? $content['userId'] ?? [];
        
        if (!is_array($userIds)) {
            $userIds = explode(',', (string)$userIds);
        }
        $userIds = array_filter($userIds, fn($id) => is_numeric($id));
        
        if ($roleId <= 0 || empty($userIds)) {
            return BaseJsonResponse::fail('Invalid params', [], 400);
        }
        
        $this->roleService->cancelAuthUser($roleId, $userIds);
        return BaseJsonResponse::success([], 'Canceled authorization successfully');
    }
}
