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

namespace Framework\Session;

# use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface,

class FileSessionHandler implements \SessionHandlerInterface
{
    private string $savePath;

    private string $setPrefix;

    public function __construct()
    {
        // 初始路径可以为空，后续通过 setSavePath 设置
        $this->savePath = '';
    }

    public function setSavePath(string $savePath): void
    {
        $this->savePath = $savePath;
    }

    public function setPrefix(string $setPrefix): void
    {
        $this->setPrefix = $setPrefix;
    }

    public function open(string $savePath, string $sessionName): bool
    {
        // 如果通过 setSavePath 设置了路径，就用它；否则用传入的 $savePath
        $path = $this->savePath ?: $savePath;
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        $path = $this->getSessionFile($id);
        return is_file($path) ? file_get_contents($path) : '';
    }

    public function write(string $id, string $data): bool
    {
        $path = $this->getSessionFile($id);
        return file_put_contents($path, $data) !== false;
    }

    public function destroy(string $id): bool
    {
        $path = $this->getSessionFile($id);
        if (is_file($path)) {
            unlink($path);
        }
        return true;
    }

    public function gc(int $maxlifetime): int
    {
        $count = 0;
        $path  = $this->savePath ?: session_save_path();
        if (! is_dir($path)) {
            return 0;
        }

        foreach (new \DirectoryIterator($path) as $file) {
            if ($file->isFile() && $file->getMTime() + $maxlifetime < time()) {
                unlink($file->getPathname());
                ++$count;
            }
        }
        return $count;
    }

    private function getSessionFile(string $id): string
    {
        $path = $this->savePath ?: session_save_path();
        return rtrim($path, '/\\') . '/' . $this->setPrefix . '_' . $id;
    }
}
