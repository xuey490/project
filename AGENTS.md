# FssPHP 项目规范

## 项目概况

- **名称**: FssPHP — 轻量、高性能、安全 PHP 框架
- **PHP 版本**: >= 8.3（强制 `declare(strict_types=1)`）
- **许可证**: MIT
- **仓库**: https://github.com/xuey490/project
- **网站**: https://www.phpframe.org/
- **作者**: blue2004 (xuey863toy@gmail.com)

---

## 技术栈

| 类别 | 技术 |
|------|------|
| **核心运行时** | Workerman ^5.2 — 高性能异步 HTTP 服务器 |
| **PHP 框架** | 自研 FssPHP（基于 Symfony 7.x 组件） |
| **路由** | Symfony Routing 组件 + PHP 8 Attribute 自动路由 |
| **DI 容器** | Symfony DependencyInjection 封装 + Laravel 风格静态门面 |
| **ORM** | ThinkORM ^4.0 / Laravel Eloquent ^12.58（双 ORM 可切换） |
| **模板引擎** | Twig ^3.x + ThinkTemplate（双引擎） |
| **缓存** | Symfony Cache + ThinkCache + Predis |
| **安全** | Casbin 权限管理, lcobucci/jwt JWT, phpseclib 加密 |
| **日志** | Monolog + 自定义滚动日志 |
| **验证** | think-validate |
| **事件** | PSR-14 事件调度器 |
| **测试** | PHPUnit, PHP-CS-Fixer |
| **运行模式** | PHP-FPM (`public/index.php`) / Workerman (`server.php` / `watch.php`) |

---

## 目录结构

```
project/
├── app/                    # 应用代码
│   ├── Controllers/        # 默认应用控制器
│   ├── admin/Controllers/  # 管理后台
│   ├── system/Controllers/ # 系统 API
│   ├── test/Controllers/   # 测试应用
│   ├── Dao/                # 数据访问层（自动注册为服务）
│   ├── Middlewares/        # 自定义中间件
│   ├── Providers/          # 服务提供者（自动发现）
│   └── function.php        # 应用级辅助函数
├── config/                 # 配置
│   ├── app.php             # 应用配置
│   ├── apps.php            # 多应用配置
│   ├── routes.php          # 手动路由定义
│   ├── middleware.php       # 全局中间件栈
│   ├── database.php        # 数据库配置
│   ├── cache.php           # 缓存配置
│   ├── log.php             # 日志配置
│   ├── services.php        # DI 服务定义
│   └── plugin/plugins.php  # 插件配置
├── framework/              # 框架核心
│   ├── Core/               # 核心（Framework, App, Kernel, Router）
│   ├── Container/          # DI 容器封装
│   ├── Middleware/         # 中间件调度器
│   ├── Attributes/         # PHP 8 属性（Auth, Route, Action, Role）
│   ├── Basic/              # 基类（Controller, Service, Dao, JsonResponse + CRUD Traits）
│   ├── Security/           # CSRF Token 管理
│   ├── Event/              # PSR-14 事件调度器
│   ├── Validation/         # ThinkValidator 工厂
│   ├── Cache/              # 缓存封装
│   ├── DI/                 # 属性注入（Injectable Trait）
│   ├── ORM/Adapter/        # ORM 适配器工厂
│   ├── Plugin/             # 插件管理
│   ├── Utils/              # 工具类（Tree, Arr, BaseModel 别名等）
│   └── helpers.php         # 全局辅助函数
├── database/migrations/    # 数据库迁移（当前为空）
├── plugins/                # 插件目录（当前为空）
├── public/                 # Web 入口
│   ├── index.php           # FPM 入口
│   └── .htaccess           # Apache 重写规则
├── runtime/                # 运行时文件
├── storage/                # 存储（缓存、日志、视图缓存）
├── tests/Unit/             # PHPUnit 测试
├── vendor/                 # Composer 依赖
```

---

## 代码规范

### 强制规则

- 所有 PHP 文件必须使用 `declare(strict_types=1)`
- PHP 8.3+ 强类型 —— 所有属性和方法必须有类型声明
- 短数组语法 `[]`，禁止 `array()`
- 类名 PascalCase，方法名 camelCase
- Nullable 类型使用 `?Type` 语法
- 配置文件中使用 `return` 表达式模式

### 命名约定

| 元素 | 风格 | 示例 |
|------|------|------|
| 类 | PascalCase | `HomeController`, `MiddlewareDispatcher` |
| 接口 | PascalCase | `ListenerInterface`, `MiddlewareProviderInterface` |
| 方法 | camelCase | `getInstance()`, `handleRequest()` |
| 属性（私有/受保护） | camelCase | `$container`, `$compiledMetadata` |
| 常量 | UPPER_SNAKE | `BASE_PATH`, `AUTO_ROUTE_PREFIX` |
| 全局辅助函数 | 混合（详见下文） | `app()`, `config()`, `base_path()`, `cache_get()` |
| 配置数组键 | snake_case | `_controller`, `_middleware`, `log_channel` |

### 辅助函数命名（helpers.php）

已存在三类混用风格，**新辅助函数优先跟随同类已有风格**：
- `snake_case`: `base_path()`, `config_path()`, `cache_get()`, `cache_set()`, `caches_clear()`
- `camelCase`: `generateUuid()`, `generateRequestId()`, `redirectToRoute()`
- `PascalCase`（领域特定）: `WebCsrfField()`, `ThinkValidate()`, `EventDispatch()`

所有辅助函数以 `if (! function_exists('name'))` 包裹。

### 注释风格

- 文件头固定模板：`@Filename`, `@Date`, `@Developer`, `@Email` 标签
- 方法 docblock：中文描述 + `@param`/`@return` 标准标签
- 复杂类型用 `@var array<string, string>` PHPStan 兼容泛型写法
- 异常信息使用中文描述，用 `sprintf` 格式化

### 异常处理

- 主要使用 `RuntimeException` 和 `InvalidArgumentException`
- 可链式设置前一异常：`new RuntimeException("...", 0, $e)`
- 单例防反序列化：`__wakeup()` 固定抛 `RuntimeException`

### 属性（Attribute）用法

- 使用 PHP 8 命名参数：`#[Route(path: '/api/home', methods: ['GET'], name: 'home.index')]`
- 支持：`#[Route]`, `#[Action]`, `#[Auth]`, `#[Role]`, `#[Inject]`, `#[Autowire]`
- 生产环境下属性元数据可通过路由缓存预编译，避免运行时反射

---

## 架构模式

### 1. 三阶段 DI

```
Symfony DI Container (定义) → Container 封装 (Laravel 风格) → App 静态门面 (调用)
```
- 定义服务：`config/services.php` 使用 Symfony DSL 的闭包
- 获取服务：`App::make()`, `App::get()`, `app()`, `app(SomeClass::class)`
- 生产环境编译为缓存类：`storage/cache/container.php`
- 支持 `singleton`, `bind`, `factory`, `instance`, `lazy`, `tag`, `parameter`

### 2. 混合路由

```
手动路由（config/routes.php 中 Symfony RouteCollection）
  + 属性路由（控制器上的 #[Route]）
  + 自动推断路由（/Controller/Action/Params）
  + 插件自动路由
  + 域名绑定自动路由
```

路由优先级：手动 > 属性 > 自动推断。路由缓存支持 PSR-16 命中缓存和序列化缓存。

### 3. 多应用架构

`config/apps.php` 定义多个应用（default, admin, system, test），各自独立：
- 命名空间（如 `App\Admin\Controllers`）
- URL 前缀（如 `/admin`）
- 可选域名绑定
- 独立中间件栈

### 4. 中间件调度

洋葱模型。优先级：全局中间件（定义于 `Framework.php`）→ 应用中间件（`app/Middleware`）→ 路由中间件。

内置中间件：ContextInit, MethodOverride, RateLimit, IpBlock, XssFilter, CookieConsent, Debug。

### 5. 双 ORM 切换

通过环境变量 `ORM_DRIVER=thinkORM|laravelORM` 切换。`Framework\Utils\BaseModel` 通过 `class_alias` 透明切换适配器。DAO 层继承 `BaseDao`，自动适配当前 ORM。

### 6. CRUD 模式

`BaseController` 通过 Trait 组合提供标准 RESTful CRUD：
- `CrudActionTrait`: `index()`, `show()`, `store()`, `update()`, `destroy()`
- `CrudFilterTrait`: 输入过滤，按模型字段清洗
- `CrudFormatterTrait`: 输出格式化，支持树形结构
- `CrudQueryTrait`: 查询条件构建（`IN_`, `LIKE_`, `EQ_`, `GT_`, `BETWEEN_` 等前缀）

### 7. 三层架构

```
Controller（请求处理/响应）→ Service（业务逻辑/事务）→ Dao（数据访问/ORM）
```

所有三层通过 DI 容器自动注入。

### 8. 响应格式

JSON 响应统一使用 `BaseJsonResponse`：`success()`, `fail()`, `error()`, `unauthorized()`, `csrfExpired()`。

---

## 配置说明

### 关键环境变量

| 变量 | 说明 | 默认值 |
|------|------|--------|
| `APP_ENV` | 环境 | `prod` |
| `APP_DEBUG` | 调试模式 | `true` |
| `APP_URL` | 站点 URL | `http://localhost:8000` |
| `ORM_DRIVER` | ORM 引擎 | `thinkORM` |
| `DB_TYPE/HOST/PORT/NAME` | 数据库连接 | MySQL |
| `SESSION_STORAGE` | Session 驱动 | `redis` |
| `CACHE_DRIVER` | 缓存驱动 | `redis` |
| `COOKIE_SECRET` | Cookie 密钥 | - |
| `JWT_SINGLE_DEVICE_LOGIN` | JWT 单设备登录 | `false` |

### 服务层 DAO 自动注册

`app/Dao/` 中所有 PHP 文件自动注册为 DI 服务（autowire + autoconfigure + public）。

### 服务提供者自动发现

`app/Providers/` 和各应用下的 `Providers/` 目录中的服务提供者自动发现并启动。

---

## 运行模式

### PHP-FPM 模式
```
public/index.php → 定义 BASE_PATH → 加载 Composer + helpers → Framework::getInstance()->run()
```

### Workerman 热重载模式（开发）
```
php watch.php
```
文件变更自动重载（监控 app/, framework/, config/, routes/）。

### Workerman 生产模式
```
php server.php
```
支持 HTTP + WebSocket 双协议、健康检查、内存监控、日志轮转。

---

## 开发工具

- **代码格式化**: `php vendor/bin/php-cs-fixer fix`（基于 PSR12 + Symfony 规则）
- **测试**: `php vendor/bin/phpunit`（PHPUnit，测试目录 `tests/Unit/`）
- **依赖检查**: `composer-unused`

---

## 代码修改指南

1. 新增文件需文件头模板，包含 `@Filename`/`@Date`/`@Developer`/`@Email`
2. 新增辅助函数用 `if (! function_exists())` 包裹
3. 异常信息用中文描述，`RuntimeException` 优先
4. 新控制器继承 `BaseController`，新服务继承 `BaseService`，新 DAO 继承 `BaseDao`
5. 服务注册到 DI：`config/services.php` 中添加 DSL 配置
6. 路由优先使用手动定义；快捷用 `#[Route]` 属性
7. 避免在控制器中直接写业务逻辑 —— 委托给 Service 层
8. 事务管理放在 Service 层
9. 查询构建中使用 DAO 的 CRUD Trait 前缀机制
10. 运行 `php vendor/bin/php-cs-fixer fix` 确保代码风格统一
