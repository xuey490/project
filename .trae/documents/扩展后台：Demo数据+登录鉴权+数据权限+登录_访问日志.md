## 现状扫描结论（用于对齐改造点）
- 登录/Token 现有能力：已有 [Jwt.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Jwt.php)（签发/刷新/登出）与 [AuthMiddleware.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Middlewares/AuthMiddleware.php)（解析 Bearer/Cookie、角色校验、自动续期、注入 `user`）
- 访问日志现有能力：已有 [LogMiddleware.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Middlewares/LogMiddleware.php)（写文件/Monolog，不入库）
- 示例后台路由： [Admins.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admins.php) 通过 `#[Route(..., middleware: [...])]` 组合 Auth+Log
- 中间件全局注册方式：框架流水线会读取 `config('middlewares')`（当前工程缺少 `config/middlewares.php`，见搜索结果）

## 目标
1) 在现有 sys_* 体系上补充更多可直接演示“角色/菜单/数据权限”的 Demo 数据
2) 实现“真实登录接口”：用户名/密码登录 → 签发 JWT（携带 role 信息）→ AuthMiddleware 校验
3) 登录后根据用户角色的数据权限实现不同的数据访问（All/Custom/Dept/Dept+Sub/Self）
4) 新增两类入库日志：登录日志、每个 URL 的访问日志，并提供建表 SQL

## 1) 数据库：新增表 + 调整字段（SQL）
### 1.1 新增：登录日志表（sys_login_log）
- 字段建议：
  - `id`(PK)
  - `user_id`(nullable，失败时可为空)
  - `user_name`
  - `ip`
  - `user_agent`
  - `status`(tinyint：1成功/0失败)
  - `message`(失败原因/备注)
  - `login_time`(datetime)
  - 索引：`idx_user_id`、`idx_login_time`

### 1.2 新增：访问日志表（sys_access_log）
- 字段建议：
  - `id`(PK)
  - `user_id`(nullable)
  - `user_name`(nullable)
  - `ip`
  - `method`
  - `path`
  - `query_string`
  - `status_code`
  - `duration_ms`
  - `user_agent`
  - `referer`
  - `request_body`(json/text，做脱敏)
  - `created_at`
  - 索引：`idx_user_id`、`idx_created_at`、`idx_path`

### 1.3 为“数据权限演示”补强业务表字段（推荐）
- 由于“部门/部门及以下”数据权限需要可过滤字段：
  - 给 `sys_article` 增加 `dept_id`（或通过 join sys_user 过滤，但会更复杂、性能更差）
  - `sys_article` 创建时自动写入作者所属 dept_id

### 1.4 SQL 文件组织
- 扩展 [init.sql](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/database/sql/init.sql)：
  - 追加 `sys_login_log`、`sys_access_log` 建表
  - 对 `sys_article` 做 `ALTER TABLE` 或重建（按你项目风格选一种）
- 新增：`database/sql/demo_data.sql`
  - 只放 INSERT demo 数据，方便你反复重置数据库

## 2) Demo 数据：更丰富、可验证数据权限
在 `demo_data.sql` 中提供一套可直接跑通的演示数据（建议密码统一 123456）：
- 部门：至少 3 级树（总部 → 技术部 → 后端组 / 前端组）
- 职位：开发、HR、运营等
- 角色（至少 5 个，覆盖数据权限）：
  - `super_admin`：All
  - `dept_admin`：Dept+Sub
  - `dept_user`：Dept
  - `self_user`：Self
  - `custom_dept_role`：Custom（绑定指定 dept_id）
- 菜单：系统管理（用户/角色/菜单/部门/职位）、内容管理（文章）
- 角色菜单权限：不同角色绑定不同菜单
- 用户：
  - 超管 1 个
  - 技术部管理员 1 个
  - 后端组普通用户 1 个
  - 前端组普通用户 1 个
  - 自定义部门范围用户 1 个
- 文章：按 dept_id 分布，让不同数据权限能看到不同条数

实现方式：
- 方案 A（纯 SQL）：先把用户 `password` 写成明文，并在登录校验中兼容明文（便于演示）
- 方案 B（更安全，推荐）：新增 `database/seed_demo.php`，用 `password_hash()` 生成 bcrypt 后写入；SQL 里只写其他业务数据

## 3) 登录模块（基于现有 Jwt/AuthMiddleware）
### 3.1 新增后台登录控制器
- 新增：`App\Controllers\Admin\Auth`（或 `Admin\Login`）
- 提供接口：
  - `POST /api/admin/login`：username/password → 校验 → 签发 JWT
  - `POST /api/admin/logout`：可选（清 cookie/刷新 token 吊销逻辑）

### 3.2 登录流程细节
- 使用现有 `app('jwt')`（参考 [Jwt.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Jwt.php) 的签发/写 cookie 方式）
- 从 `sys_user` 查用户，校验密码
- 加载用户角色：`SysUser::with('roles')->...`
- **Token claims**：至少写入
  - `uid`
  - `role`（为了兼容 [AuthMiddleware.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Middlewares/AuthMiddleware.php) 当前的单角色校验逻辑）
  - 可选 `roles`（数组，后续做多角色校验更合理）

## 4) “登录后不同权限看到不同数据”落地方式
### 4.1 AuthMiddleware 注入完整用户模型
- 在 [AuthMiddleware.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Middlewares/AuthMiddleware.php) 解析出 uid 后：
  - 查询 `SysUser::with(['roles','dept','roles.depts'])->find($uid)`
  - 注入到 `$request->attributes`：例如 `current_user`（模型对象）

### 4.2 Service 层统一应用 dataScope
- 在查询列表时统一做：`Model::query()->dataScope($currentUser)`
- 至少做两个演示接口：
  - 用户列表：不同角色看到不同 dept 的用户
  - 文章列表：不同角色看到不同 dept 的文章（依赖 sys_article.dept_id）

## 5) 入库日志
### 5.1 登录日志入库
- 新增模型：`SysLoginLog` 对应 `sys_login_log`
- 登录成功/失败都写一条：
  - 成功：写 user_id/user_name/ip/ua/status=1/login_time
  - 失败：写 user_name/ip/ua/status=0/message/login_time
- 可复用事件体系：把“写库”作为 Listener（参考 [LogUserLogin.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Listeners/LogUserLogin.php) 现有事件监听风格），减少控制器耦合

### 5.2 访问日志入库（每个 URL）
- 新增中间件：`AccessLogDbMiddleware`
  - before：记录开始时间
  - after：拿 status_code、duration、path、method、ip、ua、params（脱敏复用 LogMiddleware 的逻辑）
  - 从 request attributes 取 `current_user`（若存在写 user_id/user_name）
  - 写入 `sys_access_log`
- 全局启用方式（不改框架源码）：新增 `config/middlewares.php` 返回数组：
  - `return [\App\Middlewares\AccessLogDbMiddleware::class];`
  - 因为框架 Dispatcher 会读取 `config('middlewares')` 作为 appMiddleware

## 6) 验证方式（实现完成后我会跑通）
- 运行 SQL：init.sql + demo_data.sql（或 seed_demo.php）
- 调用登录接口：不同账号登录拿到 token
- 分别请求“用户列表/文章列表”：验证返回条数符合 data_scope
- 随机访问多个 URL：检查 `sys_access_log` 有记录
- 登录成功/失败各一次：检查 `sys_login_log` 有记录

---
如果你确认这个计划，我将开始落地：补 SQL、补 demo 数据、实现登录控制器、把 dataScope 串进 service、实现并全局启用访问日志中间件、并把登录日志写入数据库。