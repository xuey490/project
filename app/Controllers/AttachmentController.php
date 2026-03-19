<?php

declare(strict_types=1);

/**
 * 附件管理控制器
 *
 * @package App\Controllers
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Controllers;

use App\Services\SysAttachmentService;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Attributes\Auth;

/**
 * AttachmentController 附件管理控制器
 */
class AttachmentController extends BaseController
{
    /**
     * 附件服务
     * @var SysAttachmentService
     */
    protected SysAttachmentService $attachmentService;

    /**
     * 初始化
     */
    protected function initialize(): void
    {
        $this->attachmentService = new SysAttachmentService();
    }

    /**
     * 获取附件列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment/list', methods: ['GET'], name: 'attachment.list')]
    #[Auth(required: true)]
    public function list(Request $request): BaseJsonResponse
    {
        $params = [
            'page' => (int)$this->input('page', 1),
            'limit' => (int)$this->input('limit', 20),
            'category_id' => $this->input('category_id', ''),
            'file_name' => $this->input('file_name', ''),
            'file_ext' => $this->input('file_ext', ''),
        ];

        $result = $this->attachmentService->getList($params);

        return $this->success($result);
    }

    /**
     * 上传附件
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment/upload', methods: ['POST'], name: 'attachment.upload')]
    #[Auth(required: true)]
    public function upload(Request $request): BaseJsonResponse
    {
        $file = $request->files->get('file');
        $categoryId = (int)$this->input('category_id', 0);

        if (!$file) {
            return $this->fail('请选择要上传的文件');
        }

        $operator = $this->getOperatorId($request);

        try {
            $result = $this->attachmentService->upload($file, $categoryId, $operator);
            return $this->success($result, '上传成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取附件详情
     *
     * @param Request $request 请求对象
     * @param int     $id      附件ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment/detail/{id}', methods: ['GET'], name: 'attachment.detail')]
    #[Auth(required: true)]
    public function detail(Request $request, int $id): BaseJsonResponse
    {
        $result = $this->attachmentService->getDetail($id);

        if (!$result) {
            return $this->fail('附件不存在', 404);
        }

        return $this->success($result);
    }

    /**
     * 更新附件名称
     *
     * @param Request $request 请求对象
     * @param int     $id      附件ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment/update/{id}', methods: ['PUT'], name: 'attachment.update')]
    #[Auth(required: true)]
    public function update(Request $request, int $id): BaseJsonResponse
    {
        $fileName = $this->input('file_name', '');

        if (empty($fileName)) {
            return $this->fail('文件名不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $this->attachmentService->updateName($id, $fileName, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除附件
     *
     * @param Request $request 请求对象
     * @param int     $id      附件ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment/delete/{id}', methods: ['DELETE'], name: 'attachment.delete')]
    #[Auth(required: true)]
    public function delete(Request $request, int $id): BaseJsonResponse
    {
        try {
            $this->attachmentService->delete($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 下载附件
     *
     * @param Request $request 请求对象
     * @param int     $id      附件ID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    #[Route(path: '/api/system/attachment/download/{id}', methods: ['GET'], name: 'attachment.download')]
    #[Auth(required: true)]
    public function download(Request $request, int $id)
    {
        $attachment = \App\Models\SysAttachment::find($id);

        if (!$attachment) {
            return $this->fail('附件不存在', 404);
        }

        return response()->file(
            $attachment->file_path,
            $attachment->file_name,
            \Symfony\Component\HttpFoundation\BinaryFileResponse
        );
    }

    /**
     * 获取存储统计
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment/stats', methods: ['GET'], name: 'attachment.stats')]
    #[Auth(required: true)]
    public function stats(Request $request): BaseJsonResponse
    {
        $result = $this->attachmentService->getStorageStats();

        return $this->success($result);
    }

    // ==================== 分类管理 ====================

    /**
     * 获取分类列表
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment-category/list', methods: ['GET'], name: 'attachmentCategory.list')]
    #[Auth(required: true)]
    public function categoryList(Request $request): BaseJsonResponse
    {
        $params = [
            'category_name' => $this->input('category_name', ''),
            'status' => $this->input('status', ''),
        ];

        $result = $this->attachmentService->getCategoryList($params);

        return $this->success($result);
    }

    /**
     * 获取分类树
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment-category/tree', methods: ['GET'], name: 'attachmentCategory.tree')]
    #[Auth(required: true)]
    public function categoryTree(Request $request): BaseJsonResponse
    {
        $result = $this->attachmentService->getCategoryTree();

        return $this->success($result);
    }

    /**
     * 创建分类
     *
     * @param Request $request 请求对象
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment-category/create', methods: ['POST'], name: 'attachmentCategory.create')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function categoryCreate(Request $request): BaseJsonResponse
    {
        $data = [
            'parent_id' => (int)$this->input('parent_id', 0),
            'category_name' => $this->input('category_name', ''),
            'category_code' => $this->input('category_code', ''),
            'sort' => (int)$this->input('sort', 0),
            'status' => (int)$this->input('status', 1),
            'remark' => $this->input('remark', ''),
        ];

        if (empty($data['category_name'])) {
            return $this->fail('分类名称不能为空');
        }

        $operator = $this->getOperatorId($request);

        try {
            $category = $this->attachmentService->createCategory($data, $operator);
            return $this->success(['id' => $category->id], '创建成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 更新分类
     *
     * @param Request $request 请求对象
     * @param int     $id      分类ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment-category/update/{id}', methods: ['PUT'], name: 'attachmentCategory.update')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function categoryUpdate(Request $request, int $id): BaseJsonResponse
    {
        $data = [
            'parent_id' => $this->input('parent_id') !== '' ? (int)$this->input('parent_id') : null,
            'category_name' => $this->input('category_name', ''),
            'category_code' => $this->input('category_code', ''),
            'sort' => $this->input('sort') !== '' ? (int)$this->input('sort') : null,
            'status' => $this->input('status') !== '' ? (int)$this->input('status') : null,
            'remark' => $this->input('remark', ''),
        ];

        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        $operator = $this->getOperatorId($request);

        try {
            $this->attachmentService->updateCategory($id, $data, $operator);
            return $this->success([], '更新成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 删除分类
     *
     * @param Request $request 请求对象
     * @param int     $id      分类ID
     * @return BaseJsonResponse
     */
    #[Route(path: '/api/system/attachment-category/delete/{id}', methods: ['DELETE'], name: 'attachmentCategory.delete')]
    #[Auth(required: true, roles: ['admin', 'super_admin'])]
    public function categoryDelete(Request $request, int $id): BaseJsonResponse
    {
        try {
            $this->attachmentService->deleteCategory($id);
            return $this->success([], '删除成功');
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 获取操作人ID
     *
     * @param Request $request 请求对象
     * @return int
     */
    protected function getOperatorId(Request $request): int
    {
        $user = $request->attributes->get('user');
        return $user['id'] ?? 0;
    }
}
