-- ========================================================
-- 多租户功能数据库迁移脚本
-- 创建时间: 2026-03-19
-- 说明: 创建租户相关表，并为现有表添加 tenant_id 字段
-- ========================================================

-- --------------------------------------------------------
-- 1. 创建租户表 (sys_tenant)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sys_tenant` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '租户ID',
    `tenant_name` VARCHAR(100) NOT NULL COMMENT '租户名称',
    `tenant_code` VARCHAR(50) NOT NULL COMMENT '租户编码（唯一）',
    `contact_name` VARCHAR(50) DEFAULT NULL COMMENT '联系人姓名',
    `contact_phone` VARCHAR(20) DEFAULT NULL COMMENT '联系人电话',
    `contact_email` VARCHAR(100) DEFAULT NULL COMMENT '联系人邮箱',
    `address` VARCHAR(255) DEFAULT NULL COMMENT '租户地址',
    `logo_url` VARCHAR(255) DEFAULT NULL COMMENT '租户Logo URL',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT '1' COMMENT '状态：0=禁用 1=启用',
    `expire_time` TIMESTAMP NULL DEFAULT NULL COMMENT '过期时间',
    `max_users` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最大用户数，0=无限制',
    `max_depts` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最大部门数，0=无限制',
    `max_roles` INT UNSIGNED NOT NULL DEFAULT '0' COMMENT '最大角色数，0=无限制',
    `remark` VARCHAR(500) DEFAULT NULL COMMENT '备注',
    `created_by` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新人ID',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `deleted_at` TIMESTAMP NULL DEFAULT NULL COMMENT '删除时间（软删除）',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tenant_code` (`tenant_code`),
    KEY `idx_status` (`status`),
    KEY `idx_expire_time` (`expire_time`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户信息表';

-- --------------------------------------------------------
-- 2. 创建用户-租户关联表 (sys_user_tenant)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sys_user_tenant` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
    `user_id` BIGINT UNSIGNED NOT NULL COMMENT '用户ID',
    `tenant_id` BIGINT UNSIGNED NOT NULL COMMENT '租户ID',
    `is_default` TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否默认租户：0=否 1=是',
    `join_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '加入时间',
    `created_by` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建人ID',
    `updated_by` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新人ID',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_tenant` (`user_id`, `tenant_id`),
    KEY `idx_tenant_user` (`tenant_id`, `user_id`),
    KEY `idx_user_default` (`user_id`, `is_default`),
    KEY `idx_join_time` (`join_time`),
    CONSTRAINT `fk_sut_user` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sut_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `sys_tenant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户-租户关联表';

-- --------------------------------------------------------
-- 3. 为现有表添加 tenant_id 字段
-- --------------------------------------------------------

-- 3.1 部门表 (sys_dept)
ALTER TABLE `sys_dept` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.2 角色表 (sys_role)
ALTER TABLE `sys_role` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.3 菜单表 (sys_menu)
ALTER TABLE `sys_menu` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.4 用户角色关联表 (sys_user_role) - 重要！支持用户在不同租户有不同角色
ALTER TABLE `sys_user_role` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `role_id`,
    DROP INDEX `idx_user_role`,
    ADD UNIQUE KEY `uk_user_role_tenant` (`user_id`, `role_id`, `tenant_id`),
    ADD KEY `idx_tenant_user` (`tenant_id`, `user_id`),
    ADD KEY `idx_tenant_role` (`tenant_id`, `role_id`);

-- 3.5 角色菜单关联表 (sys_role_menu)
ALTER TABLE `sys_role_menu` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `menu_id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.6 用户菜单关联表 (sys_user_menu)
ALTER TABLE `sys_user_menu` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `menu_id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.7 字典类型表 (sys_dict_type)
ALTER TABLE `sys_dict_type` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.8 字典数据表 (sys_dict_data)
ALTER TABLE `sys_dict_data` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.9 附件表 (sys_attachment)
ALTER TABLE `sys_attachment` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.10 附件分类表 (sys_attachment_category)
ALTER TABLE `sys_attachment_category` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.11 登录日志表 (sys_login_log)
ALTER TABLE `sys_login_log` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.12 操作日志表 (sys_operation_log)
ALTER TABLE `sys_operation_log` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- 3.13 通知公告表 (sys_notice)
ALTER TABLE `sys_notice` 
    ADD COLUMN `tenant_id` BIGINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '租户ID' AFTER `id`,
    ADD KEY `idx_tenant_id` (`tenant_id`);

-- --------------------------------------------------------
-- 4. 插入默认租户数据（用于现有数据迁移）
-- --------------------------------------------------------

-- 插入一个默认租户
INSERT INTO `sys_tenant` (
    `tenant_name`, 
    `tenant_code`, 
    `status`, 
    `max_users`, 
    `max_depts`, 
    `max_roles`, 
    `remark`
) VALUES (
    '默认租户', 
    'default', 
    1, 
    0, 
    0, 
    0, 
    '系统默认租户，用于兼容现有数据'
);

-- 获取默认租户ID（假设为1）
-- 注意：实际使用时请根据数据库返回的ID更新
SET @default_tenant_id = LAST_INSERT_ID();

-- --------------------------------------------------------
-- 5. 迁移现有数据到默认租户
-- --------------------------------------------------------

-- 5.1 将现有部门数据关联到默认租户
UPDATE `sys_dept` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.2 将现有角色数据关联到默认租户
UPDATE `sys_role` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.3 将现有菜单数据关联到默认租户
UPDATE `sys_menu` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.4 将现有用户角色关联数据关联到默认租户
UPDATE `sys_user_role` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.5 将现有角色菜单关联数据关联到默认租户
UPDATE `sys_role_menu` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.6 将现有用户菜单关联数据关联到默认租户
UPDATE `sys_user_menu` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.7 将现有字典类型数据关联到默认租户
UPDATE `sys_dict_type` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.8 将现有字典数据关联到默认租户
UPDATE `sys_dict_data` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.9 将现有附件数据关联到默认租户
UPDATE `sys_attachment` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.10 将现有附件分类数据关联到默认租户
UPDATE `sys_attachment_category` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.11 将现有登录日志数据关联到默认租户
UPDATE `sys_login_log` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.12 将现有操作日志数据关联到默认租户
UPDATE `sys_operation_log` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- 5.13 将现有通知公告数据关联到默认租户
UPDATE `sys_notice` SET `tenant_id` = @default_tenant_id WHERE `tenant_id` = 0;

-- --------------------------------------------------------
-- 6. 建立所有现有用户与默认租户的关联
-- --------------------------------------------------------

INSERT INTO `sys_user_tenant` (`user_id`, `tenant_id`, `is_default`, `join_time`)
SELECT `id`, @default_tenant_id, 1, NOW() FROM `sys_user`;

-- --------------------------------------------------------
-- 7. 创建视图（可选）- 方便查询当前租户的数据
-- --------------------------------------------------------

-- 创建当前租户部门视图
-- CREATE OR REPLACE VIEW `v_current_dept` AS
-- SELECT d.* FROM `sys_dept` d
-- WHERE d.`tenant_id` = (SELECT `tenant_id` FROM `sys_user_tenant` WHERE `is_default` = 1 LIMIT 1);

-- --------------------------------------------------------
-- 8. 添加外键约束（可选，根据性能需求决定是否添加）
-- --------------------------------------------------------

-- 注意：外键会影响性能，生产环境请根据实际情况决定是否添加
-- ALTER TABLE `sys_dept` ADD CONSTRAINT `fk_dept_tenant` 
--     FOREIGN KEY (`tenant_id`) REFERENCES `sys_tenant` (`id`);

-- ALTER TABLE `sys_role` ADD CONSTRAINT `fk_role_tenant` 
--     FOREIGN KEY (`tenant_id`) REFERENCES `sys_tenant` (`id`);

-- ALTER TABLE `sys_menu` ADD CONSTRAINT `fk_menu_tenant` 
--     FOREIGN KEY (`tenant_id`) REFERENCES `sys_tenant` (`id`);

-- --------------------------------------------------------
-- 9. 创建触发器（可选）- 自动填充 tenant_id
-- --------------------------------------------------------

-- 注意：触发器会影响性能，建议使用应用层代码控制
-- DELIMITER $$
-- CREATE TRIGGER `trg_sys_role_before_insert` 
-- BEFORE INSERT ON `sys_role`
-- FOR EACH ROW
-- BEGIN
--     IF NEW.`tenant_id` = 0 THEN
--         SET NEW.`tenant_id` = (SELECT `tenant_id` FROM `sys_user_tenant` WHERE `is_default` = 1 LIMIT 1);
--     END IF;
-- END$$
-- DELIMITER ;

-- --------------------------------------------------------
-- 迁移完成
-- --------------------------------------------------------
