<?php

namespace Framework\Composer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ScriptHandler
{
    public static function copyDefaultConfig()
    {
        $filesystem = new Filesystem();

        // 获取当前包（框架）的根目录
        $packageRoot = dirname(__DIR__, 2); // 指向 vendor/blue2004/novaphp 目录
        $projectRoot = getcwd(); // 用户项目根目录（与 composer.json 同级）

        try {
            // 1. 复制 config 目录
            $sourceConfig = $packageRoot . '/config';
            $targetConfig = $projectRoot . '/config';
            
            if (!$filesystem->exists($targetConfig) && $filesystem->exists($sourceConfig)) {
                $filesystem->mirror($sourceConfig, $targetConfig);
                echo "✅ Copied config files to: $targetConfig\n";
            } else {
                echo "ℹ️ Config directory already exists or source missing. Skipping.\n";
            }

            // 2. 复制 view/errors 到 resource 目录
            $sourceView = $packageRoot . '/view/errors';
            $targetView = $projectRoot . '/resource/view/errors';
            
            // 确保目标目录存在（即使源目录为空也创建目录结构）
            if (!$filesystem->exists($targetView)) {
                $filesystem->mkdir($targetView, 0755);
            }
            
            if ($filesystem->exists($sourceView)) {
                $filesystem->mirror($sourceView, $targetView);
                echo "✅ Copied view files to: $targetView\n";
            } else {
                echo "ℹ️ View source directory missing. Skipping.\n";
            }

            // 3. 新增：复制 .env 文件到项目根目录
            $sourceEnv = $packageRoot . '/.env';
            $targetEnv = $projectRoot . '/.env';
            
            if ($filesystem->exists($sourceEnv) && !$filesystem->exists($targetEnv)) {
                $filesystem->copy($sourceEnv, $targetEnv);
                echo "✅ Copied .env file to: $targetEnv\n";
            } else {
                if ($filesystem->exists($targetEnv)) {
                    echo "ℹ️ .env file already exists. Skipping.\n";
                } else {
                    echo "ℹ️ .env source file missing. Skipping.\n";
                }
            }

        } catch (IOExceptionInterface $e) {
            echo "❌ Error copying files: " . $e->getMessage() . "\n";
        }
    }
}