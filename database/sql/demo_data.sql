SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `sys_user_post`;
DELETE FROM `sys_role_dept`;
DELETE FROM `sys_role_menu`;
DELETE FROM `sys_user_role`;
DELETE FROM `sys_article`;
DELETE FROM `sys_menu`;
DELETE FROM `sys_role`;
DELETE FROM `sys_post`;
DELETE FROM `sys_user`;
DELETE FROM `sys_dept`;
DELETE FROM `sys_dict`;
DELETE FROM `sys_dict_item`;
DELETE FROM `sys_config`;

INSERT INTO `sys_dept` (`id`,`pid`,`ancestors`,`dept_name`,`order_num`,`leader`,`phone`,`email`,`enabled`,`del_flag`,`created_by`,`created_at`,`updated_by`,`updated_at`) VALUES
(1,0,'0','总部',1,'super',NULL,NULL,1,'0','system',NOW(),'system',NOW()),
(2,1,'0,1','技术部',1,'tech_admin',NULL,NULL,1,'0','system',NOW(),'system',NOW()),
(3,2,'0,1,2','后端组',1,'backend_leader',NULL,NULL,1,'0','system',NOW(),'system',NOW()),
(4,2,'0,1,2','前端组',2,'frontend_leader',NULL,NULL,1,'0','system',NOW(),'system',NOW()),
(5,1,'0,1','人事部',2,'hr_leader',NULL,NULL,1,'0','system',NOW(),'system',NOW()),
(6,1,'0,1','运营部',3,'ops_leader',NULL,NULL,1,'0','system',NOW(),'system',NOW());

INSERT INTO `sys_post` (`id`,`post_code`,`post_name`,`post_sort`,`enabled`,`created_by`,`created_at`,`updated_by`,`updated_at`,`remark`) VALUES
(1,'DEV','开发',1,1,'system',NOW(),'system',NOW(),NULL),
(2,'HR','人事',2,1,'system',NOW(),'system',NOW(),NULL),
(3,'OPS','运营',3,1,'system',NOW(),'system',NOW(),NULL);

INSERT INTO `sys_role` (`id`,`role_name`,`role_key`,`role_sort`,`data_scope`,`enabled`,`del_flag`,`created_by`,`created_at`,`updated_by`,`updated_at`,`remark`) VALUES
(1,'超级管理员','super_admin',1,'1',1,'0','system',NOW(),'system',NOW(),'全部数据权限'),
(2,'技术部管理员','tech_admin',2,'4',1,'0','system',NOW(),'system',NOW(),'本部门及以下数据权限'),
(3,'后端组用户','backend_user',3,'3',1,'0','system',NOW(),'system',NOW(),'本部门数据权限'),
(4,'仅本人数据','self_user',4,'5',1,'0','system',NOW(),'system',NOW(),'个人数据权限'),
(5,'人事自定义范围','custom_dept_role',5,'2',1,'0','system',NOW(),'system',NOW(),'自定义部门数据权限');

INSERT INTO `sys_menu` (`id`,`title`,`pid`,`sort`,`path`,`component`,`type`,`is_show`,`enabled`,`code`,`icon`,`created_by`,`created_at`,`updated_by`,`updated_at`,`remark`) VALUES
(1,'系统管理',0,1,'/system',NULL,1,1,1,NULL,'cog','system',NOW(),'system',NOW(),''),
(2,'用户管理',1,1,'/system/user',NULL,2,1,1,'sys:user:list','users','system',NOW(),'system',NOW(),''),
(3,'角色管理',1,2,'/system/role',NULL,2,1,1,'sys:role:list','id-badge','system',NOW(),'system',NOW(),''),
(4,'菜单管理',1,3,'/system/menu',NULL,2,1,1,'sys:menu:list','list','system',NOW(),'system',NOW(),''),
(5,'部门管理',1,4,'/system/dept',NULL,2,1,1,'sys:dept:list','sitemap','system',NOW(),'system',NOW(),''),
(6,'职位管理',1,5,'/system/post',NULL,2,1,1,'sys:post:list','briefcase','system',NOW(),'system',NOW(),''),
(7,'内容管理',0,2,'/content',NULL,1,1,1,NULL,'file-alt','system',NOW(),'system',NOW(),''),
(8,'文章管理',7,1,'/content/article',NULL,2,1,1,'cms:article:list','file','system',NOW(),'system',NOW(),''),
(9,'用户查询',2,1,'',NULL,3,1,1,'sys:user:query','#','system',NOW(),'system',NOW(),''),
(10,'用户新增',2,2,'',NULL,3,1,1,'sys:user:add','#','system',NOW(),'system',NOW(),''),
(11,'用户修改',2,3,'',NULL,3,1,1,'sys:user:edit','#','system',NOW(),'system',NOW(),''),
(12,'用户删除',2,4,'',NULL,3,1,1,'sys:user:remove','#','system',NOW(),'system',NOW(),''),
(13,'用户状态',2,5,'',NULL,3,1,1,'sys:user:status','#','system',NOW(),'system',NOW(),''),
(14,'角色查询',3,1,'',NULL,3,1,1,'sys:role:query','#','system',NOW(),'system',NOW(),''),
(15,'角色新增',3,2,'',NULL,3,1,1,'sys:role:add','#','system',NOW(),'system',NOW(),''),
(16,'角色修改',3,3,'',NULL,3,1,1,'sys:role:edit','#','system',NOW(),'system',NOW(),''),
(17,'角色删除',3,4,'',NULL,3,1,1,'sys:role:remove','#','system',NOW(),'system',NOW(),''),
(18,'角色状态',3,5,'',NULL,3,1,1,'sys:role:status','#','system',NOW(),'system',NOW(),''),
(19,'菜单查询',4,1,'',NULL,3,1,1,'sys:menu:query','#','system',NOW(),'system',NOW(),''),
(20,'菜单新增',4,2,'',NULL,3,1,1,'sys:menu:add','#','system',NOW(),'system',NOW(),''),
(21,'菜单修改',4,3,'',NULL,3,1,1,'sys:menu:edit','#','system',NOW(),'system',NOW(),''),
(22,'菜单删除',4,4,'',NULL,3,1,1,'sys:menu:remove','#','system',NOW(),'system',NOW(),''),
(23,'菜单状态',4,5,'',NULL,3,1,1,'sys:menu:status','#','system',NOW(),'system',NOW(),''),
(24,'部门查询',5,1,'',NULL,3,1,1,'sys:dept:query','#','system',NOW(),'system',NOW(),''),
(25,'部门新增',5,2,'',NULL,3,1,1,'sys:dept:add','#','system',NOW(),'system',NOW(),''),
(26,'部门修改',5,3,'',NULL,3,1,1,'sys:dept:edit','#','system',NOW(),'system',NOW(),''),
(27,'部门删除',5,4,'',NULL,3,1,1,'sys:dept:remove','#','system',NOW(),'system',NOW(),''),
(28,'部门状态',5,5,'',NULL,3,1,1,'sys:dept:status','#','system',NOW(),'system',NOW(),''),
(29,'文章查询',8,1,'',NULL,3,1,1,'cms:article:query','#','system',NOW(),'system',NOW(),''),
(30,'文章新增',8,2,'',NULL,3,1,1,'cms:article:add','#','system',NOW(),'system',NOW(),''),
(31,'文章修改',8,3,'',NULL,3,1,1,'cms:article:edit','#','system',NOW(),'system',NOW(),''),
(32,'文章删除',8,4,'',NULL,3,1,1,'cms:article:remove','#','system',NOW(),'system',NOW(),''),
(33,'文章状态',8,5,'',NULL,3,1,1,'cms:article:status','#','system',NOW(),'system',NOW(),''),
(34,'职位查询',6,1,'',NULL,3,1,1,'sys:post:query','#','system',NOW(),'system',NOW(),''),
(35,'职位新增',6,2,'',NULL,3,1,1,'sys:post:add','#','system',NOW(),'system',NOW(),''),
(36,'职位修改',6,3,'',NULL,3,1,1,'sys:post:edit','#','system',NOW(),'system',NOW(),''),
(37,'职位删除',6,4,'',NULL,3,1,1,'sys:post:remove','#','system',NOW(),'system',NOW(),''),
(38,'职位状态',6,5,'',NULL,3,1,1,'sys:post:status','#','system',NOW(),'system',NOW(),''),
(39,'日志管理',1,6,'/system/log',NULL,1,1,1,NULL,'history','system',NOW(),'system',NOW(),''),
(40,'登录日志',39,1,'/system/log/login',NULL,2,1,1,'sys:loginlog:list','sign-in','system',NOW(),'system',NOW(),''),
(41,'访问日志',39,2,'/system/log/access',NULL,2,1,1,'sys:accesslog:list','eye','system',NOW(),'system',NOW(),''),
(42,'登录日志查询',40,1,'',NULL,3,1,1,'sys:loginlog:query','#','system',NOW(),'system',NOW(),''),
(43,'登录日志删除',40,2,'',NULL,3,1,1,'sys:loginlog:remove','#','system',NOW(),'system',NOW(),''),
(44,'登录日志清空',40,3,'',NULL,3,1,1,'sys:loginlog:clean','#','system',NOW(),'system',NOW(),''),
(45,'访问日志查询',41,1,'',NULL,3,1,1,'sys:accesslog:query','#','system',NOW(),'system',NOW(),''),
(46,'访问日志删除',41,2,'',NULL,3,1,1,'sys:accesslog:remove','#','system',NOW(),'system',NOW(),''),
(47,'访问日志清空',41,3,'',NULL,3,1,1,'sys:accesslog:clean','#','system',NOW(),'system',NOW(),''),
(48,'分配用户',3,6,'',NULL,3,1,1,'sys:role:allocate','#','system',NOW(),'system',NOW(),'');

INSERT INTO `sys_role_menu` (`role_id`,`menu_id`) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),
(1,9),(1,10),(1,11),(1,12),(1,13),
(1,14),(1,15),(1,16),(1,17),(1,18),
(1,19),(1,20),(1,21),(1,22),(1,23),
(1,24),(1,25),(1,26),(1,27),(1,28),
(1,29),(1,30),(1,31),(1,32),(1,33),
(1,34),(1,35),(1,36),(1,37),(1,38),
(1,39),(1,40),(1,41),(1,42),(1,43),(1,44),(1,45),(1,46),(1,47),(1,48),
(2,1),(2,2),(2,7),(2,8),
(2,9),(2,10),(2,11),(2,12),(2,13),
(2,29),(2,30),(2,31),(2,32),(2,33),
(3,7),(3,8),(3,29),
(4,7),(4,8),(4,29),
(5,7),(5,8),(5,29);

INSERT INTO `sys_role_dept` (`role_id`,`dept_id`) VALUES
(5,5);

INSERT INTO `sys_user` (`id`,`dept_id`,`user_name`,`nick_name`,`real_name`,`user_type`,`email`,`mobile_phone`,`sex`,`avatar`,`password`,`enabled`,`del_flag`,`login_ip`,`login_date`,`created_by`,`created_at`,`updated_by`,`updated_at`,`remark`) VALUES
(1,1,'super','超级管理员','超级管理员','00','super@example.com','','0','','123456',1,'0','',NULL,'system',NOW(),'system',NOW(),NULL),
(2,2,'tech_admin','技术部管理员','技术部管理员','00','tech_admin@example.com','','0','','123456',1,'0','',NULL,'system',NOW(),'system',NOW(),NULL),
(3,3,'backend_1','后端用户A','后端用户A','00','backend1@example.com','','0','','123456',1,'0','',NULL,'system',NOW(),'system',NOW(),NULL),
(4,4,'frontend_1','前端用户A','前端用户A','00','frontend1@example.com','','0','','123456',1,'0','',NULL,'system',NOW(),'system',NOW(),NULL),
(5,5,'hr_1','人事用户A','人事用户A','00','hr1@example.com','','0','','123456',1,'0','',NULL,'system',NOW(),'system',NOW(),NULL);

INSERT INTO `sys_user_role` (`user_id`,`role_id`) VALUES
(1,1),
(2,2),
(3,3),
(4,4),
(5,5);

INSERT INTO `sys_user_post` (`user_id`,`post_id`) VALUES
(1,1),
(2,1),
(3,1),
(4,1),
(5,2);

INSERT INTO `sys_article` (`id`,`dept_id`,`title`,`content`,`author_id`,`status`,`del_flag`,`created_at`,`updated_at`) VALUES
(1,3,'后端文章-1','backend dept article 1',3,'1','0',NOW(),NOW()),
(2,3,'后端文章-2','backend dept article 2',3,'1','0',NOW(),NOW()),
(3,4,'前端文章-1','frontend dept article 1',4,'1','0',NOW(),NOW()),
(4,4,'前端文章-2','frontend dept article 2',4,'1','0',NOW(),NOW()),
(5,2,'技术部文章-1','tech dept article 1',2,'1','0',NOW(),NOW()),
(6,5,'人事文章-1','hr dept article 1',5,'1','0',NOW(),NOW());

INSERT INTO `sys_dict` (`id`, `name`, `code`, `sort`, `enabled`, `description`, `created_by`, `created_at`, `updated_by`, `updated_at`, `del_flag`) VALUES
(1, '系统开关', 'sys_normal_disable', 1, 1, '系统开关字典', 'system', NOW(), 'system', NOW(), '0');

INSERT INTO `sys_dict_item` (`id`, `dict_id`, `label`, `value`, `code`, `sort`, `enabled`, `color`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 1, '启用', '1', 'sys_normal_disable', 1, 1, 'success', 'system', NOW(), 'system', NOW()),
(2, 1, '禁用', '0', 'sys_normal_disable', 2, 1, 'danger', 'system', NOW(), 'system', NOW());

SET FOREIGN_KEY_CHECKS = 1;
