-- ============================================================================
-- 权限管理系统数据库表结构
-- 基于 PHP Casbin 和 illuminate/database
-- 创建时间: 2026-03-12
-- ============================================================================

-- ============================================================================
-- 1. Casbin 规则表 (RBAC 模型)
-- ============================================================================

-- Casbin 规则表 - 存储策略规则
CREATE TABLE IF NOT EXISTS `casbin_rule` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ptype` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '策略类型: p(权限) / g(角色继承) / g2(部门继承)',
    `v0` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '第1个参数: 用户ID/角色ID/部门ID',
    `v1` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '第2个参数: 资源/角色/部门',
    `v2` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '第3个参数: 操作/动作',
    `v3` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '第4个参数: 扩展字段',
    `v4` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '第5个参数: 扩展字段',
    `v5` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '第6个参数: 扩展字段',
    PRIMARY KEY (`id`),
    INDEX `idx_ptype` (`ptype`),
    INDEX `idx_v0` (`v0`),
    INDEX `idx_v1` (`v1`),
    INDEX `idx_v0_v1` (`v0`, `v1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Casbin权限规则表';

-- ============================================================================
-- 2. 用户表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_user` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID(雪花ID)',
    `username` VARCHAR(50) NOT NULL COMMENT '用户名',
    `password` VARCHAR(255) NOT NULL COMMENT '密码(bcrypt加密)',
    `nickname` VARCHAR(50) DEFAULT '' COMMENT '昵称',
    `email` VARCHAR(100) DEFAULT '' COMMENT '邮箱',
    `mobile` VARCHAR(20) DEFAULT '' COMMENT '手机号',
    `avatar` VARCHAR(255) DEFAULT '' COMMENT '头像URL',
    `dept_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '部门ID',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `is_admin` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否超级管理员: 0=否, 1=是',
    `last_login_ip` VARCHAR(50) DEFAULT '' COMMENT '最后登录IP',
    `last_login_time` DATETIME DEFAULT NULL COMMENT '最后登录时间',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_mobile` (`mobile`),
    INDEX `idx_dept_id` (`dept_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统用户表';

-- ============================================================================
-- 3. 角色表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_role` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '角色ID(雪花ID)',
    `role_name` VARCHAR(50) NOT NULL COMMENT '角色名称',
    `role_code` VARCHAR(50) NOT NULL COMMENT '角色编码(唯一标识)',
    `parent_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '父角色ID(角色继承)',
    `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(升序)',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_code` (`role_code`),
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统角色表';

-- ============================================================================
-- 4. 部门表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_dept` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '部门ID(雪花ID)',
    `parent_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '父部门ID(0表示顶级部门)',
    `dept_name` VARCHAR(50) NOT NULL COMMENT '部门名称',
    `dept_code` VARCHAR(50) NOT NULL COMMENT '部门编码',
    `leader` VARCHAR(50) DEFAULT '' COMMENT '负责人',
    `phone` VARCHAR(20) DEFAULT '' COMMENT '联系电话',
    `email` VARCHAR(100) DEFAULT '' COMMENT '邮箱',
    `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(升序)',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_dept_code` (`dept_code`),
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统部门表';

-- ============================================================================
-- 5. 菜单表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_menu` (
    `id` BIGINT UNSIGNED NOT NULL COMMENT '菜单ID(雪花ID)',
    `parent_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '父菜单ID(0表示顶级菜单)',
    `menu_name` VARCHAR(50) NOT NULL COMMENT '菜单名称',
    `menu_type` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '菜单类型: 1=目录, 2=菜单, 3=按钮, 4=外链',
    `path` VARCHAR(255) DEFAULT '' COMMENT '路由路径(前端路由)',
    `component` VARCHAR(255) DEFAULT '' COMMENT '组件路径(前端组件)',
    `permission` VARCHAR(100) DEFAULT '' COMMENT '权限标识(如: system:user:list)',
    `icon` VARCHAR(100) DEFAULT '' COMMENT '菜单图标',
    `sort` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序(升序)',
    `visible` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否可见: 0=隐藏, 1=显示',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态: 0=禁用, 1=启用',
    `is_frame` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否外链: 0=否, 1=是',
    `is_cache` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否缓存: 0=否, 1=是',
    `remark` VARCHAR(500) DEFAULT '' COMMENT '备注',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` DATETIME DEFAULT NULL COMMENT '删除时间(软删除)',
    PRIMARY KEY (`id`),
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_menu_type` (`menu_type`),
    INDEX `idx_status` (`status`),
    INDEX `idx_permission` (`permission`),
    INDEX `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统菜单表';

-- ============================================================================
-- 6. 用户角色关联表 (多对多)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_user_role` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    `role_id` BIGINT UNSIGNED NOT NULL COMMENT '角色ID',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_role` (`user_id`, `role_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户角色关联表';

-- ============================================================================
-- 7. 角色菜单关联表 (多对多)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_role_menu` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `role_id` BIGINT UNSIGNED NOT NULL COMMENT '角色ID',
    `menu_id` BIGINT UNSIGNED NOT NULL COMMENT '菜单ID',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_menu` (`role_id`, `menu_id`),
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色菜单关联表';

-- ============================================================================
-- 8. 用户菜单关联表 (多对多) - 用户个人菜单权限
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_user_menu` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    `menu_id` BIGINT UNSIGNED NOT NULL COMMENT '菜单ID',
    `created_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED DEFAULT 0 COMMENT '更新人ID',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_menu` (`user_id`, `menu_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户菜单关联表';

-- ============================================================================
-- 9. 操作日志表
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sys_operation_log` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
    `user_id` BIGINT UNSIGNED DEFAULT 0 COMMENT '操作用户ID',
    `username` VARCHAR(50) DEFAULT '' COMMENT '操作用户名',
    `module` VARCHAR(50) DEFAULT '' COMMENT '模块名称',
    `action` VARCHAR(100) DEFAULT '' COMMENT '操作类型',
    `method` VARCHAR(20) DEFAULT '' COMMENT '请求方法',
    `url` VARCHAR(500) DEFAULT '' COMMENT '请求URL',
    `ip` VARCHAR(50) DEFAULT '' COMMENT '操作IP',
    `user_agent` VARCHAR(500) DEFAULT '' COMMENT '用户代理',
    `params` TEXT COMMENT '请求参数',
    `result` TEXT COMMENT '操作结果',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '操作状态: 0=失败, 1=成功',
    `error_msg` TEXT COMMENT '错误信息',
    `duration` INT UNSIGNED DEFAULT 0 COMMENT '执行时长(毫秒)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_module` (`module`),
    INDEX `idx_action` (`action`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统操作日志表';

-- ============================================================================
-- 10. 初始化数据
-- ============================================================================

-- 初始化超级管理员角色
INSERT INTO `sys_role` (`id`, `role_name`, `role_code`, `parent_id`, `sort`, `status`, `remark`, `created_by`, `updated_by`)
VALUES (1, '超级管理员', 'super_admin', 0, 1, 1, '系统内置超级管理员角色，拥有所有权限', 0, 0);

-- 初始化普通用户角色
INSERT INTO `sys_role` (`id`, `role_name`, `role_code`, `parent_id`, `sort`, `status`, `remark`, `created_by`, `updated_by`)
VALUES (2, '普通用户', 'user', 0, 2, 1, '系统内置普通用户角色', 0, 0);

-- 初始化超级管理员用户 (密码: admin123, 使用 bcrypt 加密)
INSERT INTO `sys_user` (`id`, `username`, `password`, `nickname`, `email`, `mobile`, `dept_id`, `status`, `is_admin`, `remark`, `created_by`, `updated_by`)
VALUES (1, 'admin', '$2y$10$N.zmdr9k7uOCQb376NoUnuTJ8iAt6Z5EHsM8lE9lBOsl7iAt6Z5EH', '超级管理员', 'admin@example.com', '13800138000', 0, 1, 1, '系统内置超级管理员', 0, 0);

-- 关联超级管理员用户和角色
INSERT INTO `sys_user_role` (`user_id`, `role_id`, `created_by`, `updated_by`)
VALUES (1, 1, 0, 0);

-- 初始化顶级部门
INSERT INTO `sys_dept` (`id`, `parent_id`, `dept_name`, `dept_code`, `leader`, `phone`, `email`, `sort`, `status`, `remark`, `created_by`, `updated_by`)
VALUES (1, 0, '总公司', 'HQ', '管理员', '400-000-0000', 'hq@example.com', 1, 1, '总公司', 0, 0);

-- 初始化菜单 (示例)
INSERT INTO `sys_menu` (`id`, `parent_id`, `menu_name`, `menu_type`, `path`, `component`, `permission`, `icon`, `sort`, `visible`, `status`, `remark`, `created_by`, `updated_by`)
VALUES
(1, 0, '系统管理', 1, '/system', 'Layout', '', 'setting', 1, 1, 1, '系统管理目录', 0, 0),
(2, 1, '用户管理', 2, '/system/user', 'system/user/index', 'system:user:list', 'user', 1, 1, 1, '用户管理菜单', 0, 0),
(3, 1, '角色管理', 2, '/system/role', 'system/role/index', 'system:role:list', 'peoples', 2, 1, 1, '角色管理菜单', 0, 0),
(4, 1, '菜单管理', 2, '/system/menu', 'system/menu/index', 'system:menu:list', 'tree-table', 3, 1, 1, '菜单管理菜单', 0, 0),
(5, 1, '部门管理', 2, '/system/dept', 'system/dept/index', 'system:dept:list', 'tree', 4, 1, 1, '部门管理菜单', 0, 0),
(6, 2, '用户查询', 3, '', '', 'system:user:query', '', 1, 1, 1, '用户查询按钮', 0, 0),
(7, 2, '用户新增', 3, '', '', 'system:user:add', '', 2, 1, 1, '用户新增按钮', 0, 0),
(8, 2, '用户修改', 3, '', '', 'system:user:edit', '', 3, 1, 1, '用户修改按钮', 0, 0),
(9, 2, '用户删除', 3, '', '', 'system:user:delete', '', 4, 1, 1, '用户删除按钮', 0, 0),
(10, 2, '重置密码', 3, '', '', 'system:user:resetPwd', '', 5, 1, 1, '重置密码按钮', 0, 0);

-- ============================================================================
-- 11. Casbin 初始策略
-- ============================================================================

-- 超级管理员角色继承 (g = role inheritance)
INSERT INTO `casbin_rule` (`ptype`, `v0`, `v1`) VALUES ('g', 'super_admin', 'admin');
INSERT INTO `casbin_rule` (`ptype`, `v0`, `v1`) VALUES ('g', 'user', 'guest');

-- 超级管理员权限 (p = policy)
-- 格式: p, role, resource, action
INSERT INTO `casbin_rule` (`ptype`, `v0`, `v1`, `v2`) VALUES ('p', 'super_admin', '/*', '*');

-- 普通用户权限
INSERT INTO `casbin_rule` (`ptype`, `v0`, `v1`, `v2`) VALUES ('p', 'user', '/api/user/*', 'GET');
INSERT INTO `casbin_rule` (`ptype`, `v0`, `v1`, `v2`) VALUES ('p', 'user', '/api/profile/*', '*');

-- ============================================================================
-- 完成
-- ============================================================================
