-- ============================================
-- 数据权限功能数据库迁移
-- 1. 为 sys_role 表添加 data_scope 字段
-- 2. 创建 sys_role_dept 表（角色-部门关联）
-- ============================================

-- 1. 为 sys_role 表添加 data_scope 字段
ALTER TABLE `sys_role`
ADD COLUMN `data_scope` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '数据权限范围: 1=全部, 2=本部门, 3=本部门及子部门, 4=仅本人, 5=本部门及子部门+本人, 6=自定义部门' AFTER `status`;

-- 为 data_scope 添加索引
ALTER TABLE `sys_role`
ADD INDEX `idx_data_scope` (`data_scope`);

-- 2. 创建 sys_role_dept 表（角色-部门关联，用于自定义数据权限）
CREATE TABLE IF NOT EXISTS `sys_role_dept` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `role_id` BIGINT UNSIGNED NOT NULL COMMENT '角色ID',
    `dept_id` BIGINT UNSIGNED NOT NULL COMMENT '部门ID',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_dept` (`role_id`, `dept_id`),
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_dept_id` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色-部门关联表(数据权限自定义部门)';

-- 3. 创建 sys_article 示例表（文章模块，用于演示数据权限）
CREATE TABLE IF NOT EXISTS `sys_article` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章ID',
    `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '租户ID',
    `title` VARCHAR(200) NOT NULL COMMENT '文章标题',
    `content` TEXT COMMENT '文章内容',
    `summary` VARCHAR(500) DEFAULT '' COMMENT '文章摘要',
    `cover_image` VARCHAR(500) DEFAULT '' COMMENT '封面图片',
    `category_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '分类ID',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=草稿, 1=已发布, 2=已下架',
    `view_count` INT UNSIGNED DEFAULT 0 COMMENT '浏览次数',
    `sort` INT UNSIGNED DEFAULT 0 COMMENT '排序',
    `published_at` DATETIME DEFAULT NULL COMMENT '发布时间',
    `dept_id` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属部门ID（用于数据权限）',
    `created_by` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建人ID（用于数据权限）',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    INDEX `idx_tenant_id` (`tenant_id`),
    INDEX `idx_dept_id` (`dept_id`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_status` (`status`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文章表(数据权限示例)';
