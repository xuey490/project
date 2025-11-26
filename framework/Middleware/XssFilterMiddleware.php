<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Middleware;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class XssFilterMiddleware
{
    private bool $enabled = true;
    private bool $enableSqlInjectionProtection = true; // 新增：是否启用SQL注入防护

    private ?\HTMLPurifier $purifier = null;
    private array $allowedHtml = [];

    /**
     * @param bool  $enabled                      是否启用XSS过滤
     * @param array $allowedHtml                  允许的 HTML 标签
     * @param bool  $enableSqlInjectionProtection 是否启用SQL注入防护
     */
    public function __construct(
        bool $enabled = true,
        array $allowedHtml = [],
        bool $enableSqlInjectionProtection = true
    ) {
        $this->enabled = $enabled;
        $this->allowedHtml = $allowedHtml;
        $this->enableSqlInjectionProtection = $enableSqlInjectionProtection;

        if ($enabled && !empty($allowedHtml)) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', sys_get_temp_dir());
            $config->set('HTML.Allowed', implode(',', array_map(
                fn($tag) => $tag . '[*]',
                $allowedHtml
            )));
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $this->purifier = new \HTMLPurifier($config);
        }
    }

    public function handle(Request $request, callable $next): Response
    {
        if (!$this->enabled) {
            return $next($request);
        }

        // 1. 过滤 GET 参数
        if ($request->query->count() > 0) {
            $filtered = $this->filterArray($request->query->all());
            $request->query = new InputBag($filtered);
        }

        // 2. 过滤 POST 参数
        if ($request->request->count() > 0) {
            $filtered = $this->filterArray($request->request->all());
            $request->request = new InputBag($filtered);
        }

        // 3. 过滤 JSON 请求体 (并替换原请求内容)
        $this->filterAndReplaceJsonBody($request);

        // 4. 过滤 FILES (文件名和临时文件名)
        if ($request->files->count() > 0) {
            $filteredFiles = $this->filterFiles($request->files->all());
            //$request->files = new InputBag($filteredFiles);
        }

        return $next($request);
    }

    /**
     * 过滤 JSON 请求体并替换
     */
    private function filterAndReplaceJsonBody(Request $request): void
    {
        $contentType = $request->headers->get('Content-Type');
        if ($contentType && strpos($contentType, 'application/json') !== false) {
            $content = $request->getContent();
            if ($content !== '') {
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $filteredData = $this->filterArray($data);
                    $request->request = new InputBag($filteredData);
                }
            }
        }
    }

    /**
     * 过滤文件数组
     */
    private function filterFiles(array $files): array
    {
        $filteredFiles = [];
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                // 处理多维文件数组 (如 input type="file" name="files[]")
                $filteredFiles[$key] = $this->filterFiles($file);
            } elseif ($file instanceof UploadedFile) {
                // 对上传文件对象进行处理
                $sanitizedName = $this->sanitizeFileName($file->getClientOriginalName());
                // 创建一个新的 UploadedFile 对象，使用清洗后的文件名
                $filteredFiles[$key] = new UploadedFile(
                    $file->getPathname(),
                    $sanitizedName,
                    $file->getMimeType(),
                    $file->getError(),
                    true // 标记为已测试，避免再次移动时的安全检查警告
                );
            }
        }
        return $filteredFiles;
    }

    /**
     * 清洗文件名
     */
    private function sanitizeFileName(string $fileName): string
    {
        // 移除路径信息，只保留文件名本身
        $fileName = basename($fileName);
        // 移除或替换掉文件名中的危险字符和HTML标签
        $fileName = strip_tags($fileName);
        // 可以添加更多规则，如只允许字母、数字和特定符号
        $fileName = preg_replace('/[^\w\.\-]/', '_', $fileName);
        return $fileName;
    }

    /**
     * 深度过滤数组
     */
    private function filterArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitize($value);
            }
        }
        return $data;
    }

    /**
     * 综合清洗字符串 (XSS + SQL)
     */
    private function sanitize(string $input): string
    {
        // 1. XSS 过滤
        if ($this->purifier) {
            $input = $this->purifier->purify($input);
        } else {
            $input = htmlspecialchars(strip_tags($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        // 2. SQL 注入过滤 (如果启用)
        if ($this->enableSqlInjectionProtection) {
            $input = $this->escapeSql($input);
        }

        // 3. 移除危险字符和控制字符
        $str = preg_replace('/[\x00-\x1F\x7F]/', '', $input); // 移除 ASCII 控制字符
        $str = preg_replace('/[<>{}()\/\\\]/', '', $input); // 移除危险符号


        return $input;
    }

    /**
     * SQL 注入基础防护
     */
    private function escapeSql(string $str): string
    {
        // 注意：这只是一个辅助防护手段，不能替代 PDO 预处理语句！
        // 它主要用于过滤掉一些明显的注入关键字和字符。
        $search = [
            // SQL 注释符
            '--',
            '/*',
            '*/',
            // 危险的关键字 (可根据需要扩展)
            'UNION',
            'SELECT',
            'INSERT',
            'UPDATE',
            'DELETE',
            'DROP',
            'ALTER',
            'EXEC',
            'EXECUTE',
            'XP_',
            'SP_',
        ];

        $replace = array_fill(0, count($search), '');

        return str_ireplace($search, $replace, $str);
    }
}
