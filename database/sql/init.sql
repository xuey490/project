SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for sys_dept
-- ----------------------------
DROP TABLE IF EXISTS `sys_dept`;
CREATE TABLE `sys_dept` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Dept ID',
  `pid` bigint(20) DEFAULT '0' COMMENT 'Parent Dept ID',
  `ancestors` varchar(50) DEFAULT '' COMMENT 'Ancestors',
  `dept_name` varchar(30) DEFAULT '' COMMENT 'Dept Name',
  `order_num` int(4) DEFAULT '0' COMMENT 'Order Num',
  `leader` varchar(20) DEFAULT NULL COMMENT 'Leader',
  `phone` varchar(11) DEFAULT NULL COMMENT 'Phone',
  `email` varchar(50) DEFAULT NULL COMMENT 'Email',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled (1=Yes 0=No)',
  `del_flag` char(1) DEFAULT '0' COMMENT 'Del Flag (0=Normal 2=Deleted)',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  `remark` varchar(500) DEFAULT NULL COMMENT 'Remark',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Department Table';

-- ----------------------------
-- Table structure for sys_user
-- ----------------------------
DROP TABLE IF EXISTS `sys_user`;
CREATE TABLE `sys_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'User ID',
  `dept_id` bigint(20) DEFAULT NULL COMMENT 'Dept ID',
  `user_name` varchar(30) NOT NULL COMMENT 'Username',
  `nick_name` varchar(30) NOT NULL COMMENT 'Nickname',
  `real_name` varchar(30) DEFAULT '' COMMENT 'Real Name',
  `user_type` varchar(2) DEFAULT '00' COMMENT 'User Type',
  `email` varchar(50) DEFAULT '' COMMENT 'Email',
  `mobile_phone` varchar(11) DEFAULT '' COMMENT 'Mobile Phone',
  `sex` char(1) DEFAULT '0' COMMENT 'Sex (0=Male 1=Female 2=Unknown)',
  `avatar` varchar(100) DEFAULT '' COMMENT 'Avatar',
  `password` varchar(100) DEFAULT '' COMMENT 'Password',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled (1=Yes 0=No)',
  `del_flag` char(1) DEFAULT '0' COMMENT 'Del Flag',
  `login_ip` varchar(128) DEFAULT '' COMMENT 'Login IP',
  `login_date` datetime DEFAULT NULL COMMENT 'Login Date',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  `remark` varchar(500) DEFAULT NULL COMMENT 'Remark',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User Table';

-- ----------------------------
-- Table structure for sys_role
-- ----------------------------
DROP TABLE IF EXISTS `sys_role`;
CREATE TABLE `sys_role` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
  `role_name` varchar(30) NOT NULL COMMENT 'Role Name',
  `role_key` varchar(100) NOT NULL COMMENT 'Role Key',
  `role_sort` int(4) NOT NULL COMMENT 'Role Sort',
  `data_scope` char(1) DEFAULT '1' COMMENT 'Data Scope',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled',
  `del_flag` char(1) DEFAULT '0' COMMENT 'Del Flag',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  `remark` varchar(500) DEFAULT NULL COMMENT 'Remark',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Role Table';

-- ----------------------------
-- Table structure for sys_menu
-- ----------------------------
DROP TABLE IF EXISTS `sys_menu`;
CREATE TABLE `sys_menu` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Menu ID',
  `title` varchar(50) NOT NULL COMMENT 'Menu Title',
  `pid` bigint(20) DEFAULT '0' COMMENT 'Parent ID',
  `sort` int(4) DEFAULT '0' COMMENT 'Sort',
  `path` varchar(200) DEFAULT '' COMMENT 'Path',
  `component` varchar(255) DEFAULT NULL COMMENT 'Component',
  `type` tinyint(1) DEFAULT '1' COMMENT 'Type (1=Dir 2=Menu 3=Button)',
  `is_show` tinyint(1) DEFAULT '1' COMMENT 'Is Show',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled',
  `code` varchar(100) DEFAULT NULL COMMENT 'Perm Code',
  `icon` varchar(100) DEFAULT '#' COMMENT 'Icon',
  `is_cache` tinyint(1) DEFAULT '0' COMMENT 'Keep Alive',
  `is_affix` tinyint(1) DEFAULT '0' COMMENT 'Affix',
  `is_link` tinyint(1) DEFAULT '0' COMMENT 'Is Link',
  `link_url` varchar(255) DEFAULT NULL COMMENT 'Link URL',
  `del_flag` char(1) DEFAULT '0' COMMENT 'Del Flag',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  `remark` varchar(500) DEFAULT '' COMMENT 'Remark',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Menu Table';

-- ----------------------------
-- Table structure for sys_post
-- ----------------------------
DROP TABLE IF EXISTS `sys_post`;
CREATE TABLE `sys_post` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Post ID',
  `post_code` varchar(64) NOT NULL COMMENT 'Post Code',
  `post_name` varchar(50) NOT NULL COMMENT 'Post Name',
  `post_sort` int(4) NOT NULL COMMENT 'Post Sort',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled',
  `del_flag` char(1) DEFAULT '0' COMMENT 'Del Flag',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  `remark` varchar(500) DEFAULT NULL COMMENT 'Remark',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Post Table';

-- ----------------------------
-- Table structure for sys_dict
-- ----------------------------
DROP TABLE IF EXISTS `sys_dict`;
CREATE TABLE `sys_dict` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Dict ID',
  `name` varchar(100) DEFAULT '' COMMENT 'Dict Name',
  `code` varchar(100) DEFAULT '' COMMENT 'Dict Code',
  `sort` int(4) DEFAULT '0' COMMENT 'Sort',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled',
  `description` varchar(500) DEFAULT '' COMMENT 'Description',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  `del_flag` char(1) DEFAULT '0' COMMENT 'Del Flag',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Dictionary Table';

-- ----------------------------
-- Table structure for sys_dict_item
-- ----------------------------
DROP TABLE IF EXISTS `sys_dict_item`;
CREATE TABLE `sys_dict_item` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'Item ID',
  `dict_id` bigint(20) NOT NULL COMMENT 'Dict ID',
  `label` varchar(100) DEFAULT '' COMMENT 'Label',
  `value` varchar(100) DEFAULT '' COMMENT 'Value',
  `code` varchar(100) DEFAULT '' COMMENT 'Code',
  `sort` int(4) DEFAULT '0' COMMENT 'Sort',
  `enabled` tinyint(1) DEFAULT '1' COMMENT 'Enabled',
  `color` varchar(50) DEFAULT '' COMMENT 'Color',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  PRIMARY KEY (`id`),
  KEY `idx_dict_id` (`dict_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Dictionary Item Table';

-- ----------------------------
-- Table structure for sys_config
-- ----------------------------
DROP TABLE IF EXISTS `sys_config`;
CREATE TABLE `sys_config` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `config_name` varchar(100) DEFAULT '' COMMENT 'Config Name',
  `group_code` varchar(50) DEFAULT '' COMMENT 'Group Code',
  `config_key` varchar(100) DEFAULT '' COMMENT 'Config Key',
  `config_value` text COMMENT 'Config Value',
  `created_by` varchar(64) DEFAULT '' COMMENT 'Created By',
  `created_at` datetime DEFAULT NULL COMMENT 'Created Time',
  `updated_by` varchar(64) DEFAULT '' COMMENT 'Updated By',
  `updated_at` datetime DEFAULT NULL COMMENT 'Updated Time',
  `remark` varchar(500) DEFAULT NULL COMMENT 'Remark',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Config Table';

-- ----------------------------
-- Table structure for sys_user_role
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_role`;
CREATE TABLE `sys_user_role` (
  `user_id` bigint(20) NOT NULL COMMENT 'User ID',
  `role_id` bigint(20) NOT NULL COMMENT 'Role ID',
  PRIMARY KEY (`user_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User-Role Pivot';

-- ----------------------------
-- Table structure for sys_role_menu
-- ----------------------------
DROP TABLE IF EXISTS `sys_role_menu`;
CREATE TABLE `sys_role_menu` (
  `role_id` bigint(20) NOT NULL COMMENT 'Role ID',
  `menu_id` bigint(20) NOT NULL COMMENT 'Menu ID',
  PRIMARY KEY (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Role-Menu Pivot';

-- ----------------------------
-- Table structure for sys_role_dept
-- ----------------------------
DROP TABLE IF EXISTS `sys_role_dept`;
CREATE TABLE `sys_role_dept` (
  `role_id` bigint(20) NOT NULL COMMENT 'Role ID',
  `dept_id` bigint(20) NOT NULL COMMENT 'Dept ID',
  PRIMARY KEY (`role_id`,`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Role-Dept Pivot';

-- ----------------------------
-- Table structure for sys_user_post
-- ----------------------------
DROP TABLE IF EXISTS `sys_user_post`;
CREATE TABLE `sys_user_post` (
  `user_id` bigint(20) NOT NULL COMMENT 'User ID',
  `post_id` bigint(20) NOT NULL COMMENT 'Post ID',
  PRIMARY KEY (`user_id`,`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='User-Post Pivot';

-- ----------------------------
-- Table structure for sys_login_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_login_log`;
CREATE TABLE `sys_login_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `user_name` varchar(30) NOT NULL DEFAULT '',
  `ip` varchar(128) NOT NULL DEFAULT '',
  `user_agent` varchar(512) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=success 0=fail',
  `message` varchar(255) DEFAULT NULL,
  `login_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Login Log';

-- ----------------------------
-- Table structure for sys_access_log
-- ----------------------------
DROP TABLE IF EXISTS `sys_access_log`;
CREATE TABLE `sys_access_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `user_name` varchar(30) DEFAULT NULL,
  `ip` varchar(128) NOT NULL DEFAULT '',
  `method` varchar(10) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `query_string` varchar(1024) DEFAULT NULL,
  `status_code` int(11) NOT NULL DEFAULT 0,
  `duration_ms` int(11) NOT NULL DEFAULT 0,
  `user_agent` varchar(512) DEFAULT NULL,
  `referer` varchar(512) DEFAULT NULL,
  `request_body` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Access Log';

-- ----------------------------
-- Table structure for sys_article (Kept but updated del_flag)
-- ----------------------------
DROP TABLE IF EXISTS `sys_article`;
CREATE TABLE `sys_article` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `dept_id` bigint(20) NOT NULL COMMENT 'Dept ID',
  `title` varchar(255) NOT NULL COMMENT 'Title',
  `content` text COMMENT 'Content',
  `author_id` bigint(20) NOT NULL COMMENT 'Author ID',
  `status` char(1) DEFAULT '0' COMMENT 'Status',
  `del_flag` char(1) DEFAULT '0' COMMENT 'Del Flag',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dept_id` (`dept_id`),
  KEY `idx_author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Article Table';

SET FOREIGN_KEY_CHECKS = 1;
