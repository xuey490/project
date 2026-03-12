-- ============================================================================
-- 系统管理模块数据库表结构
-- 包含: 数据字典、附件管理、登录日志、操作日志
-- 创建时间: 2026-03-12
-- ============================================================================

-- ============================================================================
-- 1. 数据字典类型表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_dict_type` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '字典类型ID(雪花ID)',
    `dict_name` VARCHAR(100) NOT NULL COMMENT '字典名称',
    `dict_code` VARCHAR(100) NOT NULL COMMENT '字典标识(唯一编码)',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_dict_code` (`dict_code`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据字典类型表';

-- ============================================================================
-- 2. 数据字典数据表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_dict_data` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '字典数据ID(雪花ID)',
    `dict_type_id` BIGINT UNSIGNED NOT NULL COMMENT '字典类型ID',
    `dict_label` VARCHAR(100) NOT NULL COMMENT '字典标签(显示值)',
    `dict_value` VARCHAR(100) NOT NULL COMMENT '字典键值(实际值)',
    `dict_sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '字典排序',
    `color` VARCHAR(50) DEFAULT '' COMMENT '样式颜色(如: primary, success, warning, danger, info)',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    INDEX `idx_dict_type_id` (`dict_type_id`),
    INDEX `idx_dict_value` (`dict_value`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据字典数据表';

-- ============================================================================
-- 3. 附件分类表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_attachment_category` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '分类ID(雪花ID)',
    `parent_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '父分类ID(0表示顶级分类)',
    `category_name` VARCHAR(100) NOT NULL COMMENT '分类名称',
    `category_code` VARCHAR(100) DEFAULT '' COMMENT '分类编码',
    `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(升序)',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='附件分类表';

-- ============================================================================
-- 4. 附件表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_attachment` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '附件ID(雪花ID)',
    `category_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '分类ID',
    `file_name` VARCHAR(255) NOT NULL COMMENT '原始文件名',
    `file_path` VARCHAR(500) NOT NULL COMMENT '存储路径',
    `file_url` VARCHAR(500) DEFAULT '' COMMENT '访问URL',
    `file_size` BIGINT UNSIGNED DEFAULT 0 COMMENT '文件大小(字节)',
    `file_ext` VARCHAR(20) DEFAULT '' COMMENT '文件扩展名',
    `file_type` VARCHAR(50) DEFAULT '' COMMENT '文件MIME类型',
    `md5` VARCHAR(32) DEFAULT '' COMMENT '文件MD5值',
    `storage_driver` VARCHAR(50) DEFAULT 'local' COMMENT '存储驱动: local, oss, cos, qiniu',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_file_ext` (`file_ext`),
    INDEX `idx_md5` (`md5`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='附件表';

-- ============================================================================
-- 5. 登录日志表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_login_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
    `user_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '用户ID',
    `username` VARCHAR(50) DEFAULT '' COMMENT '用户名',
    `login_status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '登录状态: 0=失败, 1=成功',
    `login_message` VARCHAR(255) DEFAULT '' COMMENT '登录消息(失败原因)',
    `login_ip` VARCHAR(50) DEFAULT '' COMMENT '登录IP',
    `login_location` VARCHAR(255) DEFAULT '' COMMENT '登录地点',
    `login_time` DATETIME NOT NULL COMMENT '登录时间',
    `browser` VARCHAR(100) DEFAULT '' COMMENT '浏览器',
    `os` VARCHAR(100) DEFAULT '' COMMENT '操作系统',
    `user_agent` VARCHAR(500) DEFAULT '' COMMENT '用户代理',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_login_status` (`login_status`),
    INDEX `idx_login_ip` (`login_ip`),
    INDEX `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='登录日志表';

-- ============================================================================
-- 6. 操作日志表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_operation_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
    `user_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '操作用户ID',
    `username` VARCHAR(50) DEFAULT '' COMMENT '操作用户名',
    `module` VARCHAR(50) DEFAULT '' COMMENT '模块名称',
    `business_type` VARCHAR(50) DEFAULT '' COMMENT '业务类型(新增/修改/删除/查询等)',
    `method` VARCHAR(20) DEFAULT '' COMMENT '请求方法',
    `url` VARCHAR(500) DEFAULT '' COMMENT '请求URL',
    `route_name` VARCHAR(100) DEFAULT '' COMMENT '路由名称',
    `operation_ip` VARCHAR(50) DEFAULT '' COMMENT '操作IP',
    `operation_location` VARCHAR(255) DEFAULT '' COMMENT '操作地点',
    `operation_time` DATETIME NOT NULL COMMENT '操作时间',
    `request_params` TEXT COMMENT '请求参数',
    `response_result` TEXT COMMENT '响应结果',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '操作状态: 0=失败, 1=成功',
    `error_msg` TEXT COMMENT '错误信息',
    `duration` INT UNSIGNED DEFAULT 0 COMMENT '执行时长(毫秒)',
    `browser` VARCHAR(100) DEFAULT '' COMMENT '浏览器',
    `os` VARCHAR(100) DEFAULT '' COMMENT '操作系统',
    `user_agent` VARCHAR(500) DEFAULT '' COMMENT '用户代理',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_module` (`module`),
    INDEX `idx_business_type` (`business_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_operation_ip` (`operation_ip`),
    INDEX `idx_operation_time` (`operation_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='操作日志表';

-- ============================================================================
-- 7. 初始化数据
-- ============================================================================

-- 初始化数据字典类型
INSERT INTO `sys_dict_type` (`id`, `dict_name`, `dict_code`, `status`, `remark`, `created_by`, `updated_by`)
VALUES
(1, '状态', 'status', 1, '通用状态字典', 0, 0),
(2, '性别', 'gender', 1, '性别字典', 0, 0),
(3, '菜单类型', 'menu_type', 1, '菜单类型字典', 0, 0),
(4, '文件类型', 'file_type', 1, '文件类型字典', 0, 0);

-- 初始化数据字典数据
INSERT INTO `sys_dict_data` (`id`, `dict_type_id`, `dict_label`, `dict_value`, `dict_sort`, `color`, `status`, `created_by`, `updated_by`)
VALUES
-- 状态字典
(1, 1, '启用', '1', 1, 'success', 1, 0, 0),
(2, 1, '禁用', '0', 2, 'danger', 1, 0, 0),
-- 性别字典
(3, 2, '男', '1', 1, 'primary', 1, 0, 0),
(4, 2, '女', '2', 2, 'danger', 1, 0, 0),
(5, 2, '未知', '0', 3, 'info', 1, 0, 0),
-- 菜单类型字典
(6, 3, '目录', '1', 1, 'primary', 1, 0, 0),
(7, 3, '菜单', '2', 2, 'success', 1, 0, 0),
(8, 3, '按钮', '3', 3, 'warning', 1, 0, 0),
(9, 3, '外链', '4', 4, 'info', 1, 0, 0);

-- 初始化附件分类
INSERT INTO `sys_attachment_category` (`id`, `parent_id`, `category_name`, `category_code`, `sort`, `status`, `remark`, `created_by`, `updated_by`)
VALUES
(1, 0, '默认分类', 'default', 0, 1, '系统默认分类', 0, 0),
(2, 0, '图片', 'image', 1, 1, '图片文件分类', 0, 0),
(3, 0, '文档', 'document', 2, 1, '文档文件分类', 0, 0),
(4, 0, '视频', 'video', 3, 1, '视频文件分类', 0, 0);

-- ============================================================================
-- 完成
-- ============================================================================
