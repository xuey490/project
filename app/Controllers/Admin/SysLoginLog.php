<?php

namespace App\Controllers\Admin;

use App\Services\SysLoginLogService;
use Framework\Basic\BaseJsonResponse;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class SysLoginLog
{
    #[Autowire]
    protected SysLoginLogService $loginLogService;

    public function index(Request $request)
    {
        $params = $request->query->all();
        $data = $this->loginLogService->getList($params);
        return BaseJsonResponse::success($data);
    }

    public function destroy(Request $request)
    {
        $id = $request->query->get('id') ?? $request->request->get('id');
        $ids = [];
        
        if ($id) {
            $ids = explode(',', (string)$id);
        } else {
             $content = json_decode($request->getContent(), true) ?? $request->request->all();
             $ids = (array) ($content['ids'] ?? []);
        }
        
        if (empty($ids)) {
             return BaseJsonResponse::fail('Invalid params', [], 400);
        }
        
        $this->loginLogService->delete($ids);
        return BaseJsonResponse::success([], 'Deleted successfully');
    }

    public function batchDestroy(Request $request)
    {
        return $this->destroy($request);
    }

    public function clean(Request $request)
    {
        $this->loginLogService->clean();
        return BaseJsonResponse::success([], 'Cleaned successfully');
    }
}
