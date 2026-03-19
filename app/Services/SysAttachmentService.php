<?php

declare(strict_types=1);

/**
 * 附件管理服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use App\Models\SysAttachment;
use App\Models\SysAttachmentCategory;
use App\Dao\SysAttachmentDao;
use App\Dao\SysAttachmentCategoryDao;
use Framework\Basic\BaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SysAttachmentService 附件管理服务
 */
class SysAttachmentService extends BaseService
{
    /**
     * 附件DAO
     * @var SysAttachmentDao
     */
    protected SysAttachmentDao $attachmentDao;

    /**
     * 分类DAO
     * @var SysAttachmentCategoryDao
     */
    protected SysAttachmentCategoryDao $categoryDao;

    /**
     * 上传路径
     * @var string
     */
    protected string $uploadPath = 'uploads';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->attachmentDao = new SysAttachmentDao();
        $this->categoryDao = new SysAttachmentCategoryDao();
    }

    // ==================== 分类管理 ====================

    /**
     * 获取分类列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getCategoryList(array $params): array
    {
        $categoryName = $params['category_name'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysAttachmentCategory::query()->whereNull('deleted_at');

        if ($categoryName !== '') {
            $query->where('category_name', 'like', "%{$categoryName}%");
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $list = $query->orderBy('sort')->get()->toArray();

        return $list;
    }

    /**
     * 获取分类树
     *
     * @return array
     */
    public function getCategoryTree(): array
    {
        return SysAttachmentCategory::getCategoryTree();
    }

    /**
     * 获取分类详情
     *
     * @param int $id 分类ID
     * @return array|null
     */
    public function getCategoryDetail(int $id): ?array
    {
        $category = SysAttachmentCategory::find($id);
        return $category ? $category->toArray() : null;
    }

    /**
     * 创建分类
     *
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return SysAttachmentCategory|null
     */
    public function createCategory(array $data, int $operator = 0): ?SysAttachmentCategory
    {
        $data['created_by'] = $operator;
        $data['updated_by'] = $operator;

        return SysAttachmentCategory::create($data);
    }

    /**
     * 更新分类
     *
     * @param int   $id       分类ID
     * @param array $data     数据
     * @param int   $operator 操作人
     * @return bool
     */
    public function updateCategory(int $id, array $data, int $operator = 0): bool
    {
        $category = SysAttachmentCategory::find($id);
        if (!$category) {
            throw new \Exception('分类不存在');
        }

        $data['updated_by'] = $operator;
        $category->fill($data);
        return $category->save();
    }

    /**
     * 删除分类
     *
     * @param int $id 分类ID
     * @return bool
     */
    public function deleteCategory(int $id): bool
    {
        $category = SysAttachmentCategory::find($id);
        if (!$category) {
            return false;
        }

        // 检查是否有子分类
        if ($category->hasChildren()) {
            throw new \Exception('该分类下存在子分类，无法删除');
        }

        // 检查是否有附件
        if ($category->hasAttachments()) {
            throw new \Exception('该分类下存在附件，无法删除');
        }

        return $category->delete();
    }

    // ==================== 附件管理 ====================

    /**
     * 获取附件列表
     *
     * @param array $params 查询参数
     * @return array
     */
    public function getList(array $params): array
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $categoryId = $params['category_id'] ?? '';
        $fileName = $params['file_name'] ?? '';
        $fileExt = $params['file_ext'] ?? '';
        $status = $params['status'] ?? '';

        $query = SysAttachment::query()->whereNull('deleted_at');

        if ($categoryId !== '') {
            $query->where('category_id', (int)$categoryId);
        }

        if ($fileName !== '') {
            $query->where('file_name', 'like', "%{$fileName}%");
        }

        if ($fileExt !== '') {
            $query->where('file_ext', $fileExt);
        }

        if ($status !== '') {
            $query->where('status', (int)$status);
        }

        $total = $query->count();
        $list = $query->orderBy('id', 'desc')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get()
            ->toArray();

        // 格式化数据
        foreach ($list as &$item) {
            $item['formatted_size'] = $this->formatFileSize($item['file_size']);
            $item['file_icon'] = $this->getFileIcon($item['file_ext']);
        }

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取附件详情
     *
     * @param int $id 附件ID
     * @return array|null
     */
    public function getDetail(int $id): ?array
    {
        $attachment = SysAttachment::find($id);
        if (!$attachment) {
            return null;
        }

        $data = $attachment->toArray();
        $data['formatted_size'] = $attachment->getFormattedSize();
        $data['file_icon'] = $attachment->getFileIcon();

        return $data;
    }

    /**
     * 上传附件
     *
     * @param array  $file     文件信息 ($_FILES)
     * @param int    $categoryId 分类ID
     * @param int    $operator  操作人
     * @return SysAttachment|null
     */
    public function upload(array $file, int $categoryId = 0, int $operator = 0): ?SysAttachment
    {
        // 验证文件
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('无效的上传文件');
        }

        // 获取文件信息
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $md5 = md5_file($file['tmp_name']);

        // 检查是否已存在相同文件
        $existing = $this->attachmentDao->findByMd5($md5);
        if ($existing) {
            // 返回已存在的文件信息
            return $existing;
        }

        // 生成存储路径
        $datePath = date('Y/m/d');
        $newFileName = Str::random(40) . '.' . $fileExt;
        $relativePath = "{$this->uploadPath}/{$datePath}/{$newFileName}";
        $absolutePath = public_path($relativePath);

        // 确保目录存在
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 移动文件
        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            throw new \Exception('文件保存失败');
        }

        // 保存到数据库
        $attachment = SysAttachment::create([
            'category_id' => $categoryId,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'file_url' => asset($relativePath),
            'file_size' => $fileSize,
            'file_ext' => $fileExt,
            'file_type' => $fileType,
            'md5' => $md5,
            'storage_driver' => SysAttachment::STORAGE_LOCAL,
            'status' => SysAttachment::STATUS_ENABLED,
            'created_by' => $operator,
            'updated_by' => $operator,
        ]);

        return $attachment;
    }

    /**
     * 更新附件名称
     *
     * @param int    $id       附件ID
     * @param string $fileName 文件名
     * @param int    $operator 操作人
     * @return bool
     */
    public function updateFileName(int $id, string $fileName, int $operator = 0): bool
    {
        $attachment = SysAttachment::find($id);
        if (!$attachment) {
            return false;
        }

        $attachment->file_name = $fileName;
        $attachment->updated_by = $operator;
        return $attachment->save();
    }

    /**
     * 移动附件到分类
     *
     * @param int $attachmentId 附件ID
     * @param int $categoryId   分类ID
     * @param int $operator     操作人
     * @return bool
     */
    public function moveToCategory(int $attachmentId, int $categoryId, int $operator = 0): bool
    {
        $attachment = SysAttachment::find($attachmentId);
        if (!$attachment) {
            return false;
        }

        $attachment->category_id = $categoryId;
        $attachment->updated_by = $operator;
        return $attachment->save();
    }

    /**
     * 删除附件
     *
     * @param int $id 附件ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        $attachment = SysAttachment::find($id);
        if (!$attachment) {
            return false;
        }

        // 删除物理文件
        $filePath = public_path($attachment->file_path);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // 删除数据库记录
        return $attachment->delete();
    }

    /**
     * 批量删除附件
     *
     * @param array $ids 附件ID数组
     * @return int 删除数量
     */
    public function batchDelete(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            if ($this->delete($id)) {
                $count++;
            }
        }
        return $count;
    }

    // ==================== 辅助方法 ====================

    /**
     * 格式化文件大小
     *
     * @param int $size 文件大小(字节)
     * @return string
     */
    protected function formatFileSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * 获取文件图标
     *
     * @param string $ext 文件扩展名
     * @return string
     */
    protected function getFileIcon(string $ext): string
    {
        $ext = strtolower($ext);

        return match ($ext) {
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg' => 'image',
            'pdf' => 'pdf',
            'doc', 'docx' => 'word',
            'xls', 'xlsx' => 'excel',
            'ppt', 'pptx' => 'ppt',
            'zip', 'rar', '7z', 'tar', 'gz' => 'zip',
            'mp3', 'wav', 'flac', 'aac' => 'audio',
            'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv' => 'video',
            default => 'file',
        };
    }

    /**
     * 获取存储统计
     *
     * @return array
     */
    public function getStorageStats(): array
    {
        $totalSize = SysAttachment::sum('file_size');
        $totalCount = SysAttachment::count();

        // 按文件类型统计
        $typeStats = SysAttachment::selectRaw('file_ext, count(*) as count, sum(file_size) as size')
            ->groupBy('file_ext')
            ->orderBy('size', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total_size' => $totalSize,
            'total_count' => $totalCount,
            'formatted_size' => $this->formatFileSize($totalSize),
            'type_stats' => $typeStats,
        ];
    }
}
