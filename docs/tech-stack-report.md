# 项目技术栈分析报告

## 1. 项目概览

该项目是一个基于 **PHP 8.3** 的后端 Web 项目，同时也是 **FssPhp** 自研框架的实践型示例工程。项目并不是 Laravel、Symfony、ThinkPHP 的标准脚手架，而是以 `framework/` 目录为核心，自行组合 Symfony 组件、Workerman、Illuminate Database、ThinkORM、Twig、Casbin 等能力，形成一套混合式框架。

从目录组织看，项目明显分为两层：

- `framework/`：框架内核与通用基础设施。
- `app/`：具体业务应用代码。
- `plugins/`：插件式扩展模块。
- `config/`：配置中心。
- `resource/`：模板、翻译等资源。
- `database/`：初始化 SQL 与迁移脚本。
- `tests/`：单元测试与手工验证脚本。

关键入口：

- FPM / PHP 内置 Server 入口：[public/index.php](file:///c:/Users/Administrator/Desktop/fssphp/project/public/index.php)
- Workerman 常驻入口：[server.php](file:///c:/Users/Administrator/Desktop/fssphp/project/server.php)
- 启动说明文档：[readme.md](file:///c:/Users/Administrator/Desktop/fssphp/project/readme.md)

## 2. 语言与依赖管理

### 2.1 语言版本

- `composer.json` 要求 `php: ^8.3`，说明该项目以 PHP 8.3 为主要运行目标。[composer.json](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.json)
- 控制器中广泛使用 PHP 8 Attributes，如 `#[Route]`、`#[Auth]`，说明项目已采用现代 PHP 语言特性。[PluginController](file:///c:/Users/Administrator/Desktop/fssphp/project/app/Controllers/System/PluginController.php)

### 2.2 包管理与自动加载

- 依赖管理工具：**Composer**。[composer.json](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.json)
- 自动加载方式：**PSR-4**。
- 命名空间映射：
  - `Framework\\` -> `framework/`
  - `App\\` -> `app/`
  - `Plugins\\` -> `plugins/`

这意味着项目具备较清晰的框架层、应用层、插件层边界，后续扩展方式也围绕 Composer 自动加载机制展开。

## 3. 核心技术栈

### 3.1 Web 框架内核

该项目最核心的技术判断是：

**它是一个自研 PHP 框架应用，而不是直接基于某个单一现成框架。**

证据：

- HTTP 入口通过 `Framework::getInstance()->run()` 启动框架主流程。[public/index.php](file:///c:/Users/Administrator/Desktop/fssphp/project/public/index.php)
- 框架核心类位于 [Framework](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Core/Framework.php)，内部完成：
  - 基础目录初始化
  - 容器初始化
  - Kernel 启动
  - 路由加载
  - 插件加载
  - 中间件调度器初始化
- 内核初始化逻辑位于 [Kernel](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Core/Kernel.php)，负责容器别名、时区、异常处理接管。

结论：

- 框架形态：**自研框架**
- 设计风格：**Symfony 组件化 + 自定义内核封装**

### 3.2 Symfony 组件

虽然项目不是标准 Symfony 应用，但大量底层能力依赖 Symfony 组件提供：

- `symfony/http-foundation`
- `symfony/http-kernel`
- `symfony/routing`
- `symfony/dependency-injection`
- `symfony/cache`
- `symfony/config`
- `symfony/translation`
- `symfony/dotenv`

依赖声明见：[composer.json](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.json)

锁定版本可从 `composer.lock` 看到，例如：

- `symfony/http-foundation`: `v7.4.7`
- `symfony/routing`: `v7.4.6`
- `symfony/dependency-injection`: `v7.4.7`

版本证据来自：[composer.lock](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.lock)

实际代码中，Symfony 主要承担：

- Request / Response 抽象
- 路由匹配
- 依赖注入容器
- 配置与环境变量处理
- 翻译与缓存能力

### 3.3 Workerman

项目同时支持 **高性能常驻进程运行模式**，核心依赖是：

- `workerman/workerman`

锁定版本：

- `workerman/workerman`: `v5.1.10`

证据：

- 依赖声明：[composer.json](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.json)
- 锁定版本：[composer.lock](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.lock)
- 运行入口：[server.php](file:///c:/Users/Administrator/Desktop/fssphp/project/server.php)
- 重启脚本：[start.bat](file:///c:/Users/Administrator/Desktop/fssphp/project/start.bat)

`server.php` 中实现了：

- Workerman HTTP / WebSocket 启动
- Workerman Request 到 Symfony Request 的桥接
- Symfony Response 到 Workerman Response 的桥接
- 健康检查文件写入
- 日志轮转

这说明项目运行模式不是单一 FPM，而是 **FPM + Workerman 双模式兼容**。

## 4. 路由与控制器体系

### 4.1 路由实现

项目采用 **混合路由模式**：

- 手工注册路由集合：[config/routes.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/routes.php)
- 自动推断路由 + Attribute 元数据扫描：[Router](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Core/Router.php)

`Router` 的特征非常明确：

- 支持 Symfony 定义路由
- 支持自动路由
- 支持路由命中缓存
- 支持 Attribute 元数据提取
- 支持安全策略、白名单、黑名单

这说明项目在路由设计上做了“声明式 + 约定式”的折中。

### 4.2 控制器元数据

业务控制器大量使用 PHP 8 Attributes：

- `#[Route(...)]`
- `#[Auth(...)]`
- 以及框架中的 `Action`、`Role`、`Permission`、`Validate` 等能力

代表文件：

- [PluginController](file:///c:/Users/Administrator/Desktop/fssphp/project/app/Controllers/System/PluginController.php)
- [DeptControllerCrud](file:///c:/Users/Administrator/Desktop/fssphp/project/app/Controllers/DeptControllerCrud.php)
- [RoleController](file:///c:/Users/Administrator/Desktop/fssphp/project/app/Controllers/RoleController.php)

结论：

- 控制器风格：**注解/属性驱动**
- API 风格：**以系统管理类 REST 接口为主**

## 5. 容器与服务注册

项目的依赖注入体系建立在 Symfony DI 之上，但通过自定义容器进行封装：

- 容器封装类：[Container](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Container/Container.php)
- 服务注册配置：[config/services.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/services.php)

核心特征：

- 启动时加载 `.env`
- 使用 `ContainerBuilder`
- 支持编译缓存到 `storage/cache/container.php`
- 支持 Provider 机制
- 支持 Attribute 注入
- 服务默认 `autowire`、`autoconfigure`、`public`

这套设计说明框架并不是简单的“文件 include 风格”，而是明确朝着 **组件化容器架构** 在做。

## 6. 数据层与 ORM

### 6.1 ORM 策略

项目最大的技术特点之一，是同时兼容两套 ORM：

- **Illuminate Database / Eloquent**
- **ThinkORM**

依赖声明：

- `illuminate/database`
- `topthink/think-orm`
- `topthink/think-cache`
- `topthink/think-validate`
- `topthink/think-template`

证据：

- 依赖清单：[composer.json](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.json)
- 锁定版本：[composer.lock](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.lock)
- ORM 配置：[config/database.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/database.php)
- 服务别名切换：[config/services.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/services.php)

锁定版本示例：

- `illuminate/database`: `v12.56.0`
- `topthink/think-orm`: `v4.0.51`

### 6.2 当前默认 ORM

数据库配置显示：

- `engine => laravelORM`

即当前默认走的是 **Eloquent 风格适配层**，但框架又保留了 ThinkORM 适配能力，因此可以判断该项目的 ORM 策略是：

**双 ORM 兼容，当前默认使用 Laravel ORM 适配。**

### 6.3 数据库与迁移

数据相关文件包括：

- 配置：[config/database.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/database.php)
- 初始化 SQL：[database/sql/init.sql](file:///c:/Users/Administrator/Desktop/fssphp/project/database/sql/init.sql)
- 迁移脚本：[database/migrate.php](file:///c:/Users/Administrator/Desktop/fssphp/project/database/migrate.php)
- 增量迁移：[database/migrations](file:///c:/Users/Administrator/Desktop/fssphp/project/database/migrations)

数据库配置默认目标：

- MySQL
- 默认字符集 `utf8mb4`
- 支持 SQL 调试

说明项目的数据层既考虑了框架抽象，也保留了传统 SQL 初始化方式。

## 7. 视图与模板

项目是一个后端主导项目，但并不只提供 JSON API，也具备服务端渲染能力。

### 7.1 模板引擎

支持双模板引擎：

- `Twig`
- `ThinkTemplate`

证据：

- 依赖声明：[composer.json](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.json)
- 视图配置：[config/view.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/view.php)
- Twig Provider：[TwigServiceProvider](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Providers/TwigServiceProvider.php)
- ThinkTemplate Provider：[ThinkTempServiceProvider](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Providers/ThinkTempServiceProvider.php)

### 7.2 模板资源

模板目录：

- `resource/view/`
- `resource/acme/blog/`

翻译目录：

- `resource/translations/`

从资源文件可以看出项目具备：

- 页面模板渲染
- 组件模板
- 错误页模板
- 多语言消息文件

结论：

- 表现层是 **SSR 模板渲染 + API 并存**
- 国际化通过 Symfony Translation 路线实现

## 8. 安全与权限体系

这是该项目的一个重点技术栈区域。

### 8.1 JWT 认证

使用：

- `lcobucci/jwt`
- `lcobucci/clock`

锁定版本：

- `lcobucci/jwt`: `5.6.0`

配置文件：

- [config/jwt.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/jwt.php)

可见能力：

- Token TTL / Refresh TTL
- 黑名单
- 单设备登录开关
- 多租户 Header / Query 参数支持
- `jwt | session | auto` 认证模式

这意味着项目认证方案支持 **JWT 与 Session 混合模式**。

### 8.2 Casbin 权限控制

使用：

- `casbin/casbin`

锁定版本：

- `casbin/casbin`: `v4.1.1`

配置与模型：

- [config/permission.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/permission.php)
- [config/casbin-model.conf](file:///c:/Users/Administrator/Desktop/fssphp/project/config/casbin-model.conf)
- [config/casbin_rbac_model.conf](file:///c:/Users/Administrator/Desktop/fssphp/project/config/casbin_rbac_model.conf)

可见特征：

- 默认 enforcer
- restful enforcer
- 数据库存储规则
- Redis watcher 同步权限变更

结论：

- 权限体系是 **JWT + Casbin + Attribute 权限元数据** 的组合式方案。

### 8.3 中间件安全防护

安全相关中间件与配置主要包括：

- CSRF 防护
- Referer 检查
- 速率限制
- 测试环境写操作保护
- XSS 过滤
- IP 黑白名单

证据：

- 全局安全配置：[config/middleware.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/middleware.php)
- 中间件清单：[config/middlewares.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/middlewares.php)
- 中间件实现目录：[framework/Middleware](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Middleware)
- 应用中间件目录：[app/Middlewares](file:///c:/Users/Administrator/Desktop/fssphp/project/app/Middlewares)

整体判断：

- 项目对 Web 安全的关注度较高，安全能力不是“外部网关承担”，而是框架内部内建。

## 9. 缓存、日志与基础设施

### 9.1 缓存

可见缓存技术包括：

- Symfony Cache
- PSR-16 缓存接口
- Think Cache
- Redis 缓存

证据：

- 依赖：[composer.json](file:///c:/Users/Administrator/Desktop/fssphp/project/composer.json)
- 缓存目录：`storage/cache/`
- Router 中存在路由命中缓存逻辑：[Router](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Core/Router.php)

### 9.2 日志

日志技术：

- `monolog/monolog`

日志目录：

- `storage/logs/`
- `storage/workerman/`
- `runtime/logs/`

说明：

- 框架既保留了常规应用日志，也有 Workerman 常驻模式的专用日志。

### 9.3 Redis

Redis 不是单一附属组件，而是贯穿多个能力：

- 缓存
- Session
- Casbin watcher
- Redis 监控模块

配置文件：

- [config/redis.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/redis.php)

其配置形式支持多节点降级，说明作者考虑了高可用场景。

## 10. 插件系统

插件系统是该项目非常鲜明的特征之一。

证据：

- 插件目录：[plugins](file:///c:/Users/Administrator/Desktop/fssphp/project/plugins)
- 插件管理器：[PluginManager](file:///c:/Users/Administrator/Desktop/fssphp/project/framework/Plugin/PluginManager.php)
- 插件配置目录：[config/plugin](file:///c:/Users/Administrator/Desktop/fssphp/project/config/plugin)
- 示例插件清单：[plugin.json](file:///c:/Users/Administrator/Desktop/fssphp/project/plugins/blog/plugin.json)

从实现上看，插件系统支持：

- 插件发现
- 启用/禁用
- 安装/卸载 hook
- 插件路由
- 插件数据库迁移
- 插件市场相关接口

说明该项目并不是简单的单体应用，而是有意设计成 **可扩展平台型后端**。

## 11. 多租户能力

多租户能力在代码中非常明确：

- 配置文件：[config/tenant.php](file:///c:/Users/Administrator/Desktop/fssphp/project/config/tenant.php)
- 相关模型：`SysTenant`、`SysUserTenant`
- 租户上下文：`Framework/Tenant/*`
- 单元测试：[TenantTest](file:///c:/Users/Administrator/Desktop/fssphp/project/tests/Unit/TenantTest.php)

从 `config/jwt.php` 与多租户测试可以看出：

- 租户 ID 可通过 Header / Query 获取
- 租户隔离可显式控制
- 已存在租户切换与数据隔离的测试意图

结论：

- 多租户是该项目的核心领域能力之一，不是后续临时附加特性。

## 12. 测试与开发质量

### 12.1 自动化测试

测试框架：

- PHPUnit

证据：

- 配置文件：[phpunit.xml](file:///c:/Users/Administrator/Desktop/fssphp/project/phpunit.xml)
- 测试目录：[tests/Unit](file:///c:/Users/Administrator/Desktop/fssphp/project/tests/Unit)

锁定版本：

- `phpunit/phpunit`: `12.5.15`

现状判断：

- 已有单元测试入口
- 自动化测试主要集中在局部能力，如多租户
- 仍存在较多手工验证脚本，说明测试体系尚未完全系统化

### 12.2 代码风格

可见代码规范工具：

- `friendsofphp/php-cs-fixer`

相关文件：

- [.php-cs-fixer.php](file:///c:/Users/Administrator/Desktop/fssphp/project/.php-cs-fixer.php)
- [.php-cs-fixer.cache](file:///c:/Users/Administrator/Desktop/fssphp/project/.php-cs-fixer.cache)

## 13. 部署与运行方式

项目支持至少两种运行方式：

### 13.1 传统模式

根据文档，可通过：

```bash
php -S localhost:8000 -t public
```

说明文档见：[readme.md](file:///c:/Users/Administrator/Desktop/fssphp/project/readme.md)

### 13.2 Workerman 模式

根据文档与脚本，可通过 Workerman 运行，适合常驻进程场景：

- [server.php](file:///c:/Users/Administrator/Desktop/fssphp/project/server.php)
- [start.bat](file:///c:/Users/Administrator/Desktop/fssphp/project/start.bat)

## 14. 综合判断

### 14.1 技术栈画像

这个项目的技术画像可以概括为：

- 语言：PHP 8.3
- 类型：自研框架后端项目
- Web 基础：Symfony 组件
- 高性能运行：Workerman
- 数据层：Eloquent + ThinkORM 双适配
- 模板层：Twig + ThinkTemplate
- 权限：Casbin
- 认证：JWT + Session 混合
- 缓存/消息基础：Redis
- 日志：Monolog
- 测试：PHPUnit
- 扩展机制：插件系统
- 业务特征：系统管理、多租户、权限控制

### 14.2 项目定位

从代码结构与功能模块看，这不是一个轻量演示项目，而更像：

**面向后台管理系统 / 平台型业务系统的 PHP 框架化工程。**

它比较适合：

- 中后台系统
- 插件式平台
- 多租户 SaaS 后端
- 同时需要 FPM 与常驻进程模式的项目

## 15. 风险与建议

### 15.1 优势

- 组件选型成熟，底层依赖质量较高。
- 自研框架边界清晰，扩展点较多。
- 安全、中间件、权限、租户、插件能力比较完整。
- 同时兼容 FPM 与 Workerman，运行模式灵活。

### 15.2 风险

- 双 ORM 兼容会增加维护复杂度。
- 自研框架对团队熟悉度要求较高，新成员上手成本高于标准 Laravel/Symfony 项目。
- 配置与缓存生成物并存，若环境管理不严格，容易出现“配置文件与缓存结果不一致”的问题。
- 自动化测试目前看还不够全面，部分验证仍依赖手工脚本。

### 15.3 建议

- 若用于长期生产，建议进一步统一 ORM 主路线，减少双栈心智负担。
- 建议将插件系统、租户能力、权限链路的关键流程补充为集成测试。
- 建议将运行模式、缓存策略、生产环境配置差异整理为单独运维文档。

---

## 结论

该项目的技术栈并非“某个现成框架 + 少量业务代码”，而是一个以 **FssPhp 自研框架** 为核心、以 **Symfony 组件** 为基础设施、以 **Workerman / Eloquent / ThinkORM / Twig / Casbin / JWT / Redis** 为主要支撑能力的 **平台型 PHP 后端工程**。

它最突出的三个技术特征是：

1. **自研框架内核 + Symfony 组件化底座**
2. **FPM 与 Workerman 双运行模式**
3. **插件系统、多租户、权限体系三者并重**

