<?php

namespace App\Controllers\Admin;

use App\Services\SysArticleService;
use Framework\DI\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SysArticle
{
    #[Autowire]
    protected SysArticleService $articleService;

    public function index(Request $request)
    {
        $params = $request->query->all();
        // 如果中间件已注入 currentUser，Service 可以使用它进行数据权限过滤
        // 如果没有注入，Service 会跳过过滤（或抛出异常，视业务需求定）
        if ($request->attributes->has('currentUser')) {
            $params['currentUser'] = $request->attributes->get('currentUser');
        }
        
        $data = $this->articleService->getList($params);
        return new JsonResponse($data);
    }

    public function show(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $data = $this->articleService->getById($id);
        return new JsonResponse(['code' => 200, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        
        // 如果需要自动注入作者ID
        if ($request->attributes->has('currentUser')) {
            $user = $request->attributes->get('currentUser');
            $content['author_id'] = $user->user_id;
            $content['dept_id'] = $user->dept_id; // 假设文章默认归属作者部门
        }
        
        $this->articleService->create($content);
        return new JsonResponse(['code' => 200, 'msg' => 'Created successfully']);
    }

    public function update(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $content = json_decode($request->getContent(), true) ?? $request->request->all();
        $this->articleService->update($id, $content);
        return new JsonResponse(['code' => 200, 'msg' => 'Updated successfully']);
    }

    public function destroy(Request $request)
    {
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? $request->attributes->get('id') ?? 0);
        $this->articleService->delete($id);
        return new JsonResponse(['code' => 200, 'msg' => 'Deleted successfully']);
    }

    public function changeStatus(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?? $request->request->all();
        $id = (int) ($request->query->get('id') ?? $request->request->get('id') ?? ($payload['id'] ?? null) ?? $request->attributes->get('id') ?? 0);
        $status = (string) ($payload['status'] ?? $request->query->get('status') ?? '');
        $this->articleService->changeStatus($id, $status);
        return new JsonResponse(['code' => 200, 'msg' => 'Status updated']);
    }
}
