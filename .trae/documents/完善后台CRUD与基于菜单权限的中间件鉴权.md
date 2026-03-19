## 现状结论（基于已扫描代码）
- 你的 Admin 模块大多通过自动路由访问：`/Admin/SysUser/index` → `App\Controllers\Admin\SysUser::index`（见 [Router::matchAutoRoute](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/framework/Core/Router.php#L212-L258) / [buildControllerClass](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/framework/Core/Router.php#L451-L475)）。
- 因为当前安全策略 `requireExplicitAction=false`（见 [Framework.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/framework/Core/Framework.php#L213-L217)），导致所有 public 方法都可能被 URL 直接命中。
- `AuthMiddleware` 现在只在 `#[Auth]` 或 `_auth=true` 时才强制登录（见 [AuthMiddleware.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Middlewares/AuthMiddleware.php#L33-L38)），而你的 Admin 控制器（如 [SysDept](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admin/SysDept.php) / [SysMenu](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admin/SysMenu.php)）基本没有 `#[Auth]` 注解，因此“可直接访问”。
- 菜单权限数据在 `sys_role_menu` + `sys_menu.perms`（见 [init.sql](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/database/sql/init.sql#L144-L151) / [demo_data.sql](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/database/sql/demo_data.sql#L35-L51)），适合做“角色→菜单权限→接口访问控制”。

## 目标
1) 为每个资源补齐：删除、修改、更新、启用/禁用（status）能力（并保持你当前 controller/service 的写法风格）。
2) 中间件增加白名单：除登录/退出外，Admin 控制器必须登录后才能访问。
3) 中间件根据“角色的菜单权限（sys_menu.perms）”判断是否允许访问：无权限返回 403 JSON；有权限放行。

## 方案概览
### A. 认证白名单 + 强制登录（Admin 范围）
- 修改 [AuthMiddleware.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Middlewares/AuthMiddleware.php) ：
  - 增加“白名单”判断（按 path + controller/action 两种方式都支持），默认放行：`/api/admin/login`、`/api/admin/logout`（以及可选的 refresh/health）。
  - 只要 `_controller` 属于 `App\Controllers\Admin\` 且不在白名单内，即使没有 `#[Auth]` 也强制 `needAuth=true`。
  - 同时在注入 `current_user` 时 eager-load `roles.menus`，用于后续权限判断。
  - 额外做状态校验：`sys_user.status='1'` 或 `del_flag='2'` 直接 401/403（避免禁用账号仍可用 token）。

### B. 菜单权限拦截（perms）
- 新增 `App\Middlewares\MenuPermissionMiddleware`（新的文件）：
  - 仅对 `App\Controllers\Admin\` 生效（并排除 Auth 登录/退出）。
  - 从 `$request->attributes->get('_controller')` 解析出 `Controller::method`，映射成需要的 `perms`（例如 `SysUser::index` → `sys:user:list`）。
  - `super_admin`（或 user_name=super）直接放行。
  - 否则从 `$request->attributes->get('current_user')->roles->menus->pluck('perms')` 判断是否包含该 `perms`；不包含则返回 403 JSON：`{"code":403,"msg":"无权限"}`。
  - 对于“未配置映射”的方法，默认放行（避免你后续新增接口忘记配置导致全挂）；如果你希望更严格，可切换为默认拒绝。

### C. 全局启用中间件
- 更新 [config/middlewares.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/config/middlewares.php) ：在现有 `AccessLogDbMiddleware` 之前加入：
  - `\App\Middlewares\AuthMiddleware::class`
  - `\App\Middlewares\MenuPermissionMiddleware::class`

## CRUD + Status 功能落地
### 1) Controller 层补齐接口
对下列控制器补齐常用动作（保持你当前 Request/JsonResponse 风格）：
- [SysUser.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admin/SysUser.php)
- [SysRole.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admin/SysRole.php)
- [SysMenu.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admin/SysMenu.php)
- [SysDept.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admin/SysDept.php)
- [SysArticle.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Controllers/Admin/SysArticle.php)

统一新增/补齐方法（按实际表结构选择软删/硬删）：
- `show(Request $request)`（按 `id` 查询详情）
- `update(Request $request)`（你已有的保留/补齐）
- `destroy(Request $request)`（删除：有 `del_flag` 则软删，否则硬删）
- `changeStatus(Request $request)`（写 status 字段：0/1）

### 2) Service 层补齐能力
补齐/新增以下 Service 方法（并在 list 时默认过滤已删除/禁用）：
- [SysUserService.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Services/SysUserService.php)：`getById/delete/changeStatus`
- [SysRoleService.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Services/SysRoleService.php)：`getList/getById/delete/changeStatus`
- [SysMenuService.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Services/SysMenuService.php)：`getList/update/delete/changeStatus/getTreeForUser`
- [SysDeptService.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Services/SysDeptService.php)：`getById/update/delete/changeStatus`
- [SysArticleService.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/app/Services/SysArticleService.php)：`getById/update/delete/changeStatus`（update/delete 时用 `dataScope($currentUser)` 限制可操作范围）

删除的安全规则（避免误删导致树/关联破坏）：
- Dept：有子部门/有关联用户时拒绝删除。
- Menu：有子菜单时拒绝删除。
- Role/User：先 detach pivot（user_role/role_menu/role_dept/user_post）再软删。

## Demo 数据与权限映射补齐
- 目前 demo 的 `sys_menu.perms` 只有 `*:list`（见 [demo_data.sql](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/database/sql/demo_data.sql#L35-L44)），启用“接口级 perms 校验”后，新增/修改/删除/状态接口会因缺少 perms 而默认 403。
- 因此会扩展 [demo_data.sql](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/database/sql/demo_data.sql)：
  - 为每个模块增加 `menu_type='F'` 的按钮权限菜单（perms：add/edit/remove/status），并把这些 menu_id 绑定到 super_admin（全部）与 tech_admin（按你希望的范围）。

## 验证方式（不依赖前端）
- 新增一个测试脚本 `tests/manual_admin_guard_and_crud.php`：
  - 复用现有 migrate/seed 逻辑（类似 [manual_permissions_and_logs.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/tests/manual_permissions_and_logs.php) 的风格），构造 Request 调用中间件链：
    - 未登录访问 `Admin/SysUser/index` → 401
    - 登录后用不同账号访问：
      - super：允许所有 mapped perms
      - backend_1：仅允许 `cms:article:*`（按 demo 绑定结果）
    - 对一条记录执行：update/destroy/changeStatus，确认 DB 字段变化。

## 可选安全加固（不强制执行，除非你确认要更严格）
- 将 `requireExplicitAction=true`（见 [Framework.php](file:///c:/Users/Administrator/Desktop/project-root/NovaPHP0.0.9/project/framework/Core/Framework.php#L213-L217)），并给允许暴露的方法补 `#[Action]`，可以从根上减少“任意 public 方法被路由命中”的风险。

如果你确认这个计划，我会按顺序落地：中间件白名单+perms 校验 → CRUD/status → demo 数据补齐 → 脚本验证。