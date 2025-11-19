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

class MimeTypeChecker
{
    private array $extToMime;        // 扩展名 → MIME

    private array $mimeToExtensions; // MIME → 所有扩展名（支持多个）

    public function __construct(string $configPath)
    {
        $this->extToMime = include $configPath;

        // 反转数组：MIME → [ext1, ext2, ...]
        $this->mimeToExtensions = [];
        foreach ($this->extToMime as $ext => $mime) {
            $this->mimeToExtensions[$mime][] = $ext;
        }
    }

    public function getAllowedMimesByExtension(string $ext): array
    {
        return [$this->extToMime[strtolower($ext)] ?? 'application/octet-stream'];
    }

    // 原方法：扩展名 → MIME
    public function getMimeByExtension(string $ext): string
    {
        return $this->extToMime[strtolower($ext)] ?? 'application/octet-stream';
    }

    // 新方法：MIME → 所有可能的扩展名（数组）
    public function getExtensionsByMime(string $mime): array
    {
        $mime = trim(strtolower($mime));
        return $this->mimeToExtensions[$mime] ?? [];
    }

    // 获取第一个匹配的扩展名（用于命名）
    public function getExtensionByMime(string $mime): string
    {
        $extensions = $this->getExtensionsByMime($mime);
        return $extensions[0] ?? '';
    }

    // 获取所有允许的 MIME 类型（可用于白名单）
    public function getAllowedMimes(): array
    {
        return array_values($this->extToMime);
    }
}
