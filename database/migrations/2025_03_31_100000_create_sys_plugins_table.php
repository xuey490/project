<?php

declare(strict_types=1);

/**
 * 插件管理数据表迁移
 */

use Framework\Database\DatabaseFactory;

return new class {
    public function up(): void
    {
        $db = app('db');
        $config = require BASE_PATH . '/config/database.php';
        $engine = $config['engine'] ?? 'thinkORM';

        if ($engine === 'laravelORM') {
            $db->getSchemaBuilder()->create('sys_plugins', function ($table) {
                $table->id();
                $table->string('name', 100)->unique()->comment('插件名称');
                $table->string('title', 200)->comment('插件标题');
                $table->string('version', 20)->comment('版本号');
                $table->text('description')->nullable()->comment('描述');
                $table->string('author', 100)->nullable()->comment('作者');
                $table->string('namespace', 200)->comment('命名空间');
                $table->string('path', 500)->comment('插件路径');
                $table->tinyInteger('status')->default(0)->comment('状态: 0=未安装, 1=已安装, 2=已启用');
                $table->tinyInteger('is_system')->default(0)->comment('是否系统插件');
                $table->json('config')->nullable()->comment('插件配置JSON');
                $table->timestamp('installed_at')->nullable()->comment('安装时间');
                $table->timestamps();

                $table->index('status');
                $table->index('name');
            });
        } else {
            $db->statement("
                CREATE TABLE IF NOT EXISTS `sys_plugins` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(100) NOT NULL UNIQUE COMMENT '插件名称',
                    `title` VARCHAR(200) NOT NULL COMMENT '插件标题',
                    `version` VARCHAR(20) NOT NULL COMMENT '版本号',
                    `description` TEXT NULL COMMENT '描述',
                    `author` VARCHAR(100) NULL COMMENT '作者',
                    `namespace` VARCHAR(200) NOT NULL COMMENT '命名空间',
                    `path` VARCHAR(500) NOT NULL COMMENT '插件路径',
                    `status` TINYINT DEFAULT 0 COMMENT '状态: 0=未安装, 1=已安装, 2=已启用',
                    `is_system` TINYINT DEFAULT 0 COMMENT '是否系统插件',
                    `config` JSON NULL COMMENT '插件配置JSON',
                    `installed_at` TIMESTAMP NULL COMMENT '安装时间',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX `idx_status` (`status`),
                    INDEX `idx_name` (`name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件管理表';
            ");
        }
    }

    public function down(): void
    {
        $db = app('db');
        $config = require BASE_PATH . '/config/database.php';
        $engine = $config['engine'] ?? 'thinkORM';

        if ($engine === 'laravelORM') {
            $db->getSchemaBuilder()->dropIfExists('sys_plugins');
        } else {
            $db->statement("DROP TABLE IF EXISTS `sys_plugins`;");
        }
    }
};
