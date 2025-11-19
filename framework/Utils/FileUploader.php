<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-15
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Utils;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Redis;

/*
| 功能      | URL             | 方法   | 参数                                           |
| ------- | --------------- | ---- | -------------------------------------------- |
| 检查已上传分片 | `/upload/check` | GET  | `hash`                                       |
| 上传分片    | `/upload/chunk` | POST | `file`, `index`, `total`, `hash`, `filename` |
| 合并分片    | `/upload/merge` | POST | JSON `{hash, filename}`                      |
| 普通上传    | `/upload`       | POST | 表单上传文件                                       |
*/
class FileUploader
{
    private string $uploadDir;

    // 分片
    private string $chunkDir;

    // 合并
    private string $mergeDir;

    // redis存储分片
    private Redis $redis;

    private int $maxSize;

    private array $whitelist;

    private array $blacklist;

    private string $naming;

    private MimeTypeChecker $mimeChecker;

    public function __construct(
        array $uploadConfig,           // 上传配置
        MimeTypeChecker $mimeChecker  // MIME 检查器
    ) {
        $this->mimeChecker = $mimeChecker;

        $this->maxSize   = $uploadConfig['max_size'] ?? 5 * 1024 * 1024; // maxsize 5MB
        $this->whitelist = array_map('strtolower', $uploadConfig['whitelist_extensions'] ?? []);
        $this->blacklist = array_map('strtolower', $uploadConfig['blacklist_extensions'] ?? []);
        $this->naming    = $uploadConfig['naming'] ?? 'uuid';

        if (! in_array($this->naming, ['original', 'uuid', 'datetime', 'md5'])) {
            $this->raiseError("Invalid naming strategy: {$this->naming}");
            # throw new \InvalidArgumentException("Invalid naming strategy: {$this->naming}");
        }

        $uploadDir       = $uploadConfig['upload_dir'] ?? $this->raiseError('Missing upload_dir');
        $this->uploadDir = str_replace('%kernel.project_dir%', $this->getProjectDir(), $uploadDir);

        if (! is_dir($this->uploadDir) && ! mkdir($this->uploadDir, 0755, true)) {
            $this->raiseError("Failed to create upload directory: {$this->uploadDir}");
            # throw new \RuntimeException("Failed to create upload directory: {$this->uploadDir}");
        }

        if (! is_writable($this->uploadDir)) {
            $this->raiseError("Upload directory is not writable: {$this->uploadDir}");
            # throw new \RuntimeException("Upload directory is not writable: {$this->uploadDir}");
        }

        $this->chunkDir = $this->uploadDir . '/chunks';
        $this->mergeDir = $this->uploadDir . '/complete';

        if (! is_dir($this->chunkDir)) {
            mkdir($this->chunkDir, 0755, true);
        }
        if (! is_dir($this->mergeDir)) {
            mkdir($this->mergeDir, 0755, true);
        }

        // Redis 连接
        $this->redis = app('redis.client');
        /*
        $this->redis = new Redis();
        $this->redis->connect($config['redis']['host'] ?? '127.0.0.1', $config['redis']['port'] ?? 6379);
        $this->redis->select($config['redis']['db'] ?? 0);
        */
    }

    /**
     * 分片上传.
     */
    public function uploadChunk(Request $request): array
    {
        $file     = $request->files->get('file');
        $hash     = $request->request->get('hash');
        $index    = (int) $request->request->get('index');
        $total    = (int) $request->request->get('total');
        $filename = $request->request->get('filename');

        if (! $file || ! $hash || ! $filename) {
            $this->raiseError('缺少必要参数');
        }

        $chunkDir = "{$this->chunkDir}/{$hash}";
        if (! is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $chunkPath = "{$chunkDir}/{$index}.part";
        try {
            $file->move($chunkDir, "{$index}.part");
        } catch (\Exception $e) {
            $this->raiseError('保存分片失败: ' . $e->getMessage());
        }

        // Redis 记录分片
        $redisKey = "upload:{$hash}:chunks";
        $this->redis->sAdd($redisKey, (string) $index);
        $this->redis->expire($redisKey, 24 * 3600);

        return [
            'status'  => 'ok',
            'index'   => $index,
            'message' => '分片上传成功',
        ];
    }

    /**
     * 查询已上传分片（断点恢复）.
     */
    public function checkUploadedChunks(Request $request): array
    {
        $hash = $request->query->get('hash');
        if (! $hash) {
            $this->raiseError('缺少 hash 参数');
        }

        $redisKey = "upload:{$hash}:chunks";
        $uploaded = $this->redis->sMembers($redisKey);

        return [
            'status'   => 'ok',
            'uploaded' => array_map('intval', $uploaded),
        ];
    }

    /**
     * 合并分片.
     */
    public function mergeChunks(Request $request): array
    {
        $data     = json_decode($request->getContent(), true);
        $hash     = $data['hash']     ?? null;
        $filename = $data['filename'] ?? null;
        if (! $hash || ! $filename) {
            $this->raiseError('缺少 hash 或 filename');
        }

        $chunkDir = "{$this->chunkDir}/{$hash}";
        if (! is_dir($chunkDir)) {
            $this->raiseError('分片目录不存在');
        }

        $outputPath = "{$this->mergeDir}/" . basename($filename);
        $chunks     = glob("{$chunkDir}/*.part");
        if (! $chunks) {
            $this->raiseError('未找到分片文件');
        }

        natsort($chunks);
        $output = fopen($outputPath, 'wb');
        if (! $output) {
            $this->raiseError('无法创建合并文件');
        }

        foreach ($chunks as $chunk) {
            $in = fopen($chunk, 'rb');
            if ($in) {
                stream_copy_to_stream($in, $output);
                fclose($in);
            }
        }
        fclose($output);

        // 清理 Redis + 临时文件
        $this->redis->del("upload:{$hash}:chunks");
        foreach ($chunks as $chunk) {
            unlink($chunk);
        }
        @rmdir($chunkDir);

        return [
            'status'  => 'ok',
            'message' => "文件已合并: {$outputPath}",
            'path'    => str_replace($this->getProjectDir(), '', $outputPath),
        ];
    }

    /**
     * 普通上传.
     */
    public function upload(Request $request, string $formName = 'file'): array
    {
        $files = $request->files->get($formName);
        if (! $files) {
            $this->raiseError("No file found under key '{$formName}'");
            # throw new \InvalidArgumentException("No file found under key '{$formName}'");
        }

        $fileList = is_array($files) ? $files : [$files];
        $results  = [];

        foreach ($fileList as $file) {
            if (! $file instanceof UploadedFile) {
                $this->raiseError('Invalid uploaded file.');
                # throw new \InvalidArgumentException('Invalid uploaded file.');
            }
            $results[] = $this->handleFile($file);
        }

        return $results;
    }

    /**
     * 统一错误处理函数.
     */
    private function raiseError(string $message, int $code = 400): never
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode([
            'status'  => 'error',
            'code'    => $code,
            'message' => $message,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 文件基础校验 + 保存逻辑.
     */
    private function handleFile(UploadedFile $file): array
    {

        // 1. 检查上传错误
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $this->raiseError($this->getUploadErrorMessage($file->getError()));
            # throw new \RuntimeException($this->getUploadErrorMessage($file->getError()));
        }

        // 2. 检查文件大小
        if ($file->getSize() > $this->maxSize) {
            $this->raiseError("File size exceeds limit ({$this->maxSize} bytes).");
        }

        // 3. 获取扩展名（优先原始扩展名，其次从 MIME 推断）
        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
        if (! $extension) {
            $detectedMime       = $file->getMimeType();
            $inferredExtensions = $this->mimeChecker->getExtensionsByMime($detectedMime);
            $extension          = $inferredExtensions[0] ?? '';
        }
        if (! $extension) {
            $this->raiseError('Cannot determine file extension and no valid MIME mapping found.');
        }

        // 4. 黑名单检查
        if (in_array($extension, $this->blacklist)) {
            $this->raiseError("File extension '{$extension}' is blacklisted.");
        }

        // 5. 白名单检查
        if (! empty($this->whitelist) && ! in_array($extension, $this->whitelist)) {
            $this->raiseError("File extension '{$extension}' is not allowed.");
        }

        // 6. MIME 类型严格校验（使用 fileinfo）
        $expectedMime = $this->mimeChecker->getMimeByExtension($extension);
        if (! $expectedMime || $expectedMime === 'application/octet-stream') {
            $this->raiseError("No valid MIME type defined for extension: {$extension}");
        }

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realPath = $file->getRealPath();
        if (! $realPath || ! is_file($realPath)) {
            $this->raiseError('Temporary uploaded file not found.');
        }

        $detectedMime = $finfo->file($realPath);
        if (! $detectedMime) {
            $this->raiseError('Unable to detect MIME type of uploaded file.');
        }

        if ($detectedMime !== $expectedMime) {
            $this->raiseError("Suspicious MIME type detected: {$detectedMime}, expected: {$expectedMime}");
        }

        // 7. 生成日期子目录（格式：Y-m-d）
        $datePath     = date('Y-m-d'); // 如 2025-10-12
        $targetSubDir = $this->uploadDir . '/' . $datePath;

        if (! is_dir($targetSubDir) && ! mkdir($targetSubDir, 0755, true)) {
            $this->raiseError("Failed to create upload subdirectory: {$targetSubDir}");
        }

        // 8. 生成安全文件名并构建目标路径
        $safeFilename = $this->generateSafeFilename($file->getClientOriginalName(), $extension);
        $targetPath   = $targetSubDir . '/' . $safeFilename;
        $size         =$file->getSize();

        // 9. 移动文件（必须在 getRealPath() 之后、文件消失前完成所有读取）
        try {
            if (defined('WORKERMAN_VERSION')) {
                // Workerman 模式：手动移动文件
                $realPath = $file->getRealPath();
                if (! @rename($realPath, $targetPath)) {
                    if (! @copy($realPath, $targetPath)) {
                        $this->raiseError('Failed to move uploaded file manually (Workerman mode).');
                    }
                    @unlink($realPath);
                }
            } else {
                // PHP-FPM 模式：使用 Symfony 内置方法
                $file->move($targetSubDir, $safeFilename);
            }
        } catch (\Exception $e) {
            $this->raiseError('Error: Failed to move uploaded file: ' . $e->getMessage());
        }

        // 10. 计算 MD5 哈希（操作已保存的文件）
        if (! is_file($targetPath)) {
            $this->raiseError("Uploaded file not found after move: {$targetPath}");
        }
        $md5Hash = md5_file($targetPath);

        // 11. 构造 Web 路径（相对于 public/）
        $webPath = '/uploads/' . $datePath . '/' . $safeFilename;
        $baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
        $fullUrl = rtrim($baseUrl, '/') . $webPath;

        // 12. 返回结果
        return [
            'original_name' => $file->getClientOriginalName(),
            'size'          => $size,
            'extension'     => $extension,
            'mime'          => $detectedMime,
            'uploaded_at'   => date('Y-m-d H:i:s'),
            'saved_name'    => $safeFilename,
            'path'          => $webPath,      // ✅ 如 /uploads/2025-10-12/xxx.jpg
            'url'           => $fullUrl,
            'hash'          => $md5Hash,      // ✅ MD5 哈希
        ];
    }

    private function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => '文件超过 php.ini 限制',
            UPLOAD_ERR_FORM_SIZE  => '文件超过表单限制',
            UPLOAD_ERR_PARTIAL    => '文件仅部分上传',
            UPLOAD_ERR_NO_FILE    => '没有上传文件',
            UPLOAD_ERR_NO_TMP_DIR => '缺少临时目录',
            UPLOAD_ERR_CANT_WRITE => '磁盘写入失败',
            UPLOAD_ERR_EXTENSION  => '扩展中断上传',
            default               => '未知上传错误',
        };
    }

    private function generateSafeFilename(string $originalName, string $extension): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);

        return match ($this->naming) {
            'uuid'     => generateUuid() . '.' . $extension, // Uuid::v4() . '.' . $extension,
            'datetime' => (new \DateTime())->format('Ymd_His_u') . '.' . $extension,
            'md5'      => md5_file($file->getRealPath()) . '.' . $extension,
            'original' => $name . '.' . $extension,
            default    => $name . '.' . $extension,
        };
    }

    /* 遗弃 */
    private function getExtensionFromMime(?string $mime): string
    {
        return match ($mime) {
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/gif'       => 'gif',
            'application/pdf' => 'pdf',
            'text/plain'      => 'txt',
            default           => '',
        };
    }

    private function getProjectDir(): string
    {
		//echo dirname(__DIR__, 3);
        return \dirname(__DIR__, 3); // src -> src/../ -> project root
    }
}
