# NovaFrame 插件系统完善 - 实现总结

## 一、实现概览

本次实现了插件系统的完整功能，包括 CLI 命令、Web 管理 API、配置管理、缓存优化和插件市场。

---

## 二、CLI 命令

### 可用命令列表

```bash
# ============ 路由命令 ============
php novaphp route:list                    # 列出所有路由
php novaphp route:list --method=GET       # 按方法筛选
php novaphp route:list --path=/api        # 按路径筛选
php novaphp route:list --name=user        # 按名称筛选
php novaphp route:list --json             # JSON 格式输出

# ============ 插件管理 ============
php novaphp plugin:list                   # 列出所有插件
php novaphp plugin:install blog           # 安装插件
php novaphp plugin:uninstall blog         # 卸载插件
php novaphp plugin:enable blog            # 启用插件
php novaphp plugin:disable blog           # 禁用插件
php novaphp make:plugin myplugin          # 创建插件骨架

# ============ 缓存管理 ============
php novaphp plugin:cache:clear            # 清除所有缓存
php novaphp plugin:cache:clear --routes   # 仅清除路由缓存
php novaphp plugin:cache:clear --config   # 清除配置缓存
php novaphp plugin:cache:clear --stats    # 显示缓存统计

# ============ 插件市场 ============
php novaphp plugin:market search blog     # 搜索插件
php novaphp plugin:market detail blog     # 查看详情
php novaphp plugin:market install blog    # 安装插件
php novaphp plugin:market markets         # 列出所有市场
```

---

## 三、Web 管理 API

### API 接口

| 方法 | 路由 | 功能 |
|------|------|------|
| GET | `/api/system/plugins` | 获取插件列表 |
| GET | `/api/system/plugins/{name}` | 获取插件详情 |
| POST | `/api/system/plugins/scan` | 扫描可用插件 |
| POST | `/api/system/plugins/install` | 安装插件 |
| POST | `/api/system/plugins/uninstall` | 卸载插件 |
| PUT | `/api/system/plugins/{name}/enable` | 启用插件 |
| PUT | `/api/system/plugins/{name}/disable` | 禁用插件 |
| POST | `/api/system/plugins/upload` | 上传插件包 |
| GET | `/api/system/plugins/{name}/config` | 获取插件配置 |
| PUT | `/api/system/plugins/{name}/config` | 更新插件配置 |
| GET | `/api/system/plugins/market/search` | 搜索市场插件 |
| GET | `/api/system/plugins/market/{name}` | 市场插件详情 |
| POST | `/api/system/plugins/market/install` | 从市场安装 |
| GET | `/api/system/plugins/market/list` | 获取市场列表 |
| POST | `/api/system/plugins/check-updates` | 检查更新 |

---

## 四、新增文件清单

### 框架核心

| 文件 | 说明 |
|------|------|
| `framework/Console/Commands/RouteListCommand.php` | 路由列表命令 |
| `framework/Console/Commands/PluginCacheCommand.php` | 缓存管理命令 |
| `framework/Console/Commands/PluginMarketCommand.php` | 市场命令 |
| `framework/Plugin/PluginConfigManager.php` | 配置管理器 |
| `framework/Plugin/PluginCacheManager.php` | 缓存管理器 |
| `framework/Plugin/PluginMarketService.php` | 市场服务 |

### 应用层

| 文件 | 说明 |
|------|------|
| `app/Controllers/System/PluginController.php` | Web 管理控制器 |
| `app/Models/SysPlugin.php` | 插件模型 |
| `app/Services/PluginService.php` | 插件服务 |
| `app/Dao/SysPluginDao.php` | 数据访问层 |

### 配置文件

| 文件 | 说明 |
|------|------|
| `config/plugin/blog.php` | 博客插件配置示例 |
| `config/plugin/market.php` | 插件市场配置 |
| `database/migrations/2025_03_31_100000_create_sys_plugins_table.php` | 数据库迁移 |

### 插件示例

| 文件 | 说明 |
|------|------|
| `plugins/blog/config/config.php` | 博客插件默认配置 |

---

## 五、配置说明

### 插件配置层级

```
优先级从高到低：

1. 运行时配置: config/plugin/{name}.php
   ↓
2. 插件默认配置: plugins/{name}/config/config.php
   ↓
3. 全局默认配置: config/plugin/plugins.php -> defaults
```

### 读取插件配置

```php
use Framework\Plugin\PluginConfigManager;

$configManager = new PluginConfigManager();

// 获取整个配置
$config = $configManager->get('blog');

// 获取单个配置项
$perPage = $configManager->get('blog', 'posts_per_page', 10);

// 保存配置
$configManager->save('blog', [
    'posts_per_page' => 20,
    'enable_comments' => false,
]);
```

---

## 六、缓存机制

### 缓存类型

| 缓存 | 存储位置 | 刷新时机 |
|------|----------|----------|
| 路由缓存 | `storage/cache/routes.php` | 插件安装/卸载/启用/禁用 |
| 清单缓存 | `storage/cache/plugins/manifests.php` | 插件变更 |
| 配置缓存 | `storage/cache/plugins/configs/` | 配置更新 |

### 缓存管理

```php
use Framework\Plugin\PluginCacheManager;

$cacheManager = new PluginCacheManager();

// 清除所有缓存
$cacheManager->clearAll();

// 清除路由缓存
$cacheManager->clearRouteCache();

// 清除配置缓存
$cacheManager->clearConfigCache('blog');
```

---

## 七、插件市场

### 市场架构

```
┌─────────────────────────────────────────────────────────────┐
│                     NovaFrame 应用                          │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ PluginMarketService                                  │   │
│  │ - search()      搜索插件                              │   │
│  │ - detail()      获取详情                              │   │
│  │ - download()    下载插件包                            │   │
│  │ - install()     从市场安装                            │   │
│  │ - checkUpdates() 检查更新                             │   │
│  └─────────────────────────────────────────────────────┘   │
│                            │                                │
│                            ▼                                │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ 远程市场 API                                          │   │
│  │ - 官方市场: https://market.novaframe.cn/api          │   │
│  │ - 第三方市场: 可配置                                  │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

### 市场配置

```php
// config/plugin/market.php
return [
    'official_url' => env('PLUGIN_MARKET_URL', 'https://market.novaframe.cn/api'),
    'api_key' => env('PLUGIN_MARKET_KEY', ''),
    'allow_third_party' => true,
    'third_party_markets' => [],
];
```

---

## 八、数据库表结构

### sys_plugins 表

```sql
CREATE TABLE `sys_plugins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE COMMENT '插件名称',
    `title` VARCHAR(200) NOT NULL COMMENT '插件标题',
    `version` VARCHAR(20) NOT NULL COMMENT '版本号',
    `description` TEXT COMMENT '描述',
    `author` VARCHAR(100) COMMENT '作者',
    `namespace` VARCHAR(200) COMMENT '命名空间',
    `path` VARCHAR(500) COMMENT '插件路径',
    `status` TINYINT DEFAULT 0 COMMENT '状态: 0=未安装, 1=已安装, 2=已启用',
    `is_system` TINYINT DEFAULT 0 COMMENT '是否系统插件',
    `config` JSON COMMENT '插件配置JSON',
    `installed_at` TIMESTAMP NULL COMMENT '安装时间',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 九、使用示例

### 从市场安装插件

```bash
# CLI 方式
php novaphp plugin:market search blog
php novaphp plugin:market install blog

# API 方式
POST /api/system/plugins/market/install
{
    "name": "blog",
    "version": "latest"
}
```

### 上传插件包安装

```bash
# API 方式
POST /api/system/plugins/upload
Content-Type: multipart/form-data
plugin: [zip文件]
```

### 管理插件配置

```bash
# 获取配置
GET /api/system/plugins/blog/config

# 更新配置
PUT /api/system/plugins/blog/config
{
    "posts_per_page": 20,
    "enable_comments": false
}
```

---

## 十、下一步建议

1. **Web 前端界面**：开发插件管理的前端页面
2. **插件权限细化**：与 Casbin 更深度集成
3. **插件依赖管理**：自动解析和安装依赖插件
4. **插件更新通知**：定期检查更新并推送通知
5. **官方插件市场**：搭建真实的远程插件仓库
