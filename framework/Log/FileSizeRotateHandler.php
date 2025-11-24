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

namespace Framework\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\LogRecord;

class FileSizeRotateHandler extends StreamHandler
{
    private int $maxSize;

    private int $keepDays;

    public function __construct(
        string $filename,
        int $maxSize,
        int $keepDays = 30,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->maxSize  = $maxSize;
        $this->keepDays = $keepDays;
        parent::__construct($filename, $level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $this->checkRotation();
        parent::write($record);
    }

    /**
     * 检测文件是否超过大小，若超过则切分.
     */
    private function checkRotation(): void
    {
        if (! file_exists($this->url)) {
            return;
        }

        clearstatcache(true, $this->url);
        $fileSize = filesize($this->url);

        if ($fileSize === false || $fileSize < $this->maxSize) {
            return;
        }

        $pathInfo = pathinfo($this->url);
        $dir      = $pathInfo['dirname'];
        $base     = $pathInfo['filename'];
        $ext      = $pathInfo['extension'] ?? 'log';

        $date  = date('Y-m-d');
        $index = 1;

        // 找到下一个未存在的编号
        do {
            $newName = sprintf('%s/%s-%s-%d.%s', $dir, $base, $date, $index, $ext);
            ++$index;
        } while (file_exists($newName));

        // 关闭旧文件句柄
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        // 重命名旧文件
        rename($this->url, $newName);

        // 清理旧日志
        $this->cleanupOldLogs($dir, $base, $ext);

        // 重新打开新日志文件
        $this->stream = fopen($this->url, 'a');
    }

    /**
     * 清理超过 keepDays 的日志文件.
     */
    private function cleanupOldLogs(string $dir, string $base, string $ext): void
    {
        $files = glob(sprintf('%s/%s-*.%s', $dir, $base, $ext));
        if (! $files) {
            return;
        }

        $now           = time();
        $expireSeconds = $this->keepDays * 86400;

        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }

            $mtime = filemtime($file);
            if ($mtime === false) {
                continue;
            }

            if (($now - $mtime) > $expireSeconds) {
                @unlink($file);
            }
        }
    }
}
