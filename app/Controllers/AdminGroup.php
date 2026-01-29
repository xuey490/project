<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AdminGroupService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminGroup
{
    public function __construct(
        private AdminGroupService $dao
    ) {}

    public function index(Request $request): Response
    {
        $page = (int) $request->get('page', 1);
        $size = (int) $request->get('size', 10);
        $list = $this->dao->selectList([], '*', $page, $size);
        
        return new JsonResponse([
            'code' => 200,
            'data' => $list,
            'message' => 'success'
        ]);
    }

    public function show(int $id): Response
    {
        $item = $this->dao->find($id);
        if (!$item) {
            return new JsonResponse(['code' => 404, 'message' => 'Not Found'], 404);
        }
        return new JsonResponse(['code' => 200, 'data' => $item]);
    }

    public function store(Request $request): Response
    {
        $data = $request->request->all();
        // TODO: Add validation based on table fields
        $id = $this->dao->create($data);
        return new JsonResponse(['code' => 201, 'data' => ['id' => $id], 'message' => 'Created'], 201);
    }

    public function update(int $id, Request $request): Response
    {
        $data = $request->request->all();
        $this->dao->update($id, $data);
        return new JsonResponse(['code' => 200, 'message' => 'Updated']);
    }

    public function destroy(int $id): Response
    {
        $this->dao->delete($id);
        return new JsonResponse(['code' => 200, 'message' => 'Deleted']);
    }
}
