# 权限管理系统

基于 PHP Casbin 和 illuminate/database 开发的权限管理系统。

## 功能特性

- **用户管理**: 用户增删改查、状态管理、密码重置
- **角色管理**: 角色增删改查、角色继承、权限分配
- **部门管理**: 部门增删改查、无限级层级结构
- **菜单管理**: 菜单增删改查、无限分级、支持目录/菜单/按钮/外链
- **权限控制**: 基于 Casbin 的 RBAC 权限控制
- **JWT认证**: 支持 JWT Token 认证

## 数据库表结构

| 表名 | 说明 |
|------|------|
| sys_user | 用户表 |
| sys_role | 角色表 |
| sys_dept | 部门表 |
| sys_menu | 菜单表 |
| sys_user_role | 用户角色关联表 |
| sys_role_menu | 角色菜单关联表 |
| sys_user_menu | 用户菜单关联表 |
| casbin_rule | Casbin权限规则表 |
| sys_operation_log | 操作日志表 |

## 安装步骤

### 1. 导入数据库

```bash
mysql -u root -p your_database < database/migrations/20260312000001_create_permission_tables.sql
```

### 2. 配置环境

确保 `.env` 文件中数据库配置正确：

```env
DB_TYPE=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=your_database
DB_USERNAME=root
DB_PASSWORD=your_password
DB_PREFIX=oa_
```

### 3. 默认账号

- 用户名: `admin`
- 密码: `admin123`

## API 接口

### 认证接口

| 方法 | 路径 | 说明 |
|------|------|------|
| POST | /api/auth/login | 用户登录 |
| POST | /api/auth/logout | 用户登出 |
| GET | /api/auth/me | 获取当前用户信息 |
| GET | /api/auth/menus | 获取当前用户菜单 |
| GET | /api/auth/permissions | 获取当前用户权限 |
| PUT | /api/auth/change-password | 修改密码 |

### 用户管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /api/system/user/list | 用户列表 |
| GET | /api/system/user/detail/{id} | 用户详情 |
| POST | /api/system/user/create | 创建用户 |
| PUT | /api/system/user/update/{id} | 更新用户 |
| DELETE | /api/system/user/delete/{id} | 删除用户 |
| PUT | /api/system/user/status/{id} | 更新状态 |
| PUT | /api/system/user/reset-password/{id} | 重置密码 |

### 角色管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /api/system/role/list | 角色列表 |
| GET | /api/system/role/all | 所有启用角色 |
| GET | /api/system/role/tree | 角色树 |
| GET | /api/system/role/detail/{id} | 角色详情 |
| POST | /api/system/role/create | 创建角色 |
| PUT | /api/system/role/update/{id} | 更新角色 |
| DELETE | /api/system/role/delete/{id} | 删除角色 |
| PUT | /api/system/role/status/{id} | 更新状态 |
| PUT | /api/system/role/assign-menus/{id} | 分配菜单 |

### 部门管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /api/system/dept/list | 部门列表 |
| GET | /api/system/dept/tree | 部门树 |
| GET | /api/system/dept/detail/{id} | 部门详情 |
| POST | /api/system/dept/create | 创建部门 |
| PUT | /api/system/dept/update/{id} | 更新部门 |
| DELETE | /api/system/dept/delete/{id} | 删除部门 |
| PUT | /api/system/dept/status/{id} | 更新状态 |

### 菜单管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | /api/system/menu/list | 菜单列表 |
| GET | /api/system/menu/tree | 菜单树 |
| GET | /api/system/menu/user-tree | 用户菜单树 |
| GET | /api/system/menu/user-permissions | 用户权限列表 |
| GET | /api/system/menu/permission-tree | 权限分配树 |
| GET | /api/system/menu/detail/{id} | 菜单详情 |
| POST | /api/system/menu/create | 创建菜单 |
| PUT | /api/system/menu/update/{id} | 更新菜单 |
| DELETE | /api/system/menu/delete/{id} | 删除菜单 |
| PUT | /api/system/menu/status/{id} | 更新状态 |

## 权限设计

### 菜单类型

- **目录(1)**: 菜单分组，不对应具体页面
- **菜单(2)**: 具体页面菜单
- **按钮(3)**: 页面内的操作按钮权限
- **外链(4)**: 外部链接

### 权限标识格式

权限标识格式: `模块:控制器:操作`

示例:
- `system:user:list` - 用户列表
- `system:user:add` - 添加用户
- `system:user:edit` - 编辑用户
- `system:user:delete` - 删除用户

### 权限合并规则

用户最终权限 = 角色菜单 ∪ 用户个人菜单

## 目录结构

```
app/
├── Controllers/
│   ├── AuthController.php      # 认证控制器
│   ├── UserController.php      # 用户控制器
│   ├── RoleController.php      # 角色控制器
│   ├── DeptController.php      # 部门控制器
│   └── MenuController.php      # 菜单控制器
├── Models/
│   ├── SysUser.php             # 用户模型
│   ├── SysRole.php             # 角色模型
│   ├── SysDept.php             # 部门模型
│   ├── SysMenu.php             # 菜单模型
│   ├── SysUserRole.php         # 用户角色关联
│   ├── SysRoleMenu.php         # 角色菜单关联
│   └── SysUserMenu.php         # 用户菜单关联
├── Services/
│   ├── SysUserService.php      # 用户服务
│   ├── SysRoleService.php      # 角色服务
│   ├── SysDeptService.php      # 部门服务
│   ├── SysMenuService.php      # 菜单服务
│   └── Casbin/
│       ├── CasbinService.php   # Casbin服务
│       └── DatabaseAdapter.php # 数据库适配器
├── Dao/
│   ├── SysUserDao.php          # 用户DAO
│   ├── SysRoleDao.php          # 角色DAO
│   ├── SysDeptDao.php          # 部门DAO
│   └── SysMenuDao.php          # 菜单DAO
├── Middlewares/
│   └── PermissionMiddleware.php # 权限中间件
config/
├── casbin.php                  # Casbin配置
└── casbin_rbac_model.conf      # Casbin RBAC模型
database/
└── migrations/
    └── 20260312000001_create_permission_tables.sql
```
