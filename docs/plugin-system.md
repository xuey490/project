# NovaFrame PHP 框架插件系统 - 实现总结

## 一、已完成的工作

### 1. 核心组件

| 文件 | 说明 |
|------|------|
| `framework/Plugin/PluginInterface.php` | 插件接口，定义生命周期方法 |
| `framework/Plugin/PluginManifest.php` | 插件清单解析器，解析 plugin.json |
| `framework/Plugin/PluginManager.php` | 插件管理器，负责发现、加载、安装、卸载等 |

### 2. 数据库迁移系统

| 文件 | 说明 |
|------|------|
| `framework/Plugin/Migration/Migration.php` | 迁移基类 |
| `framework/Plugin/Migration/MigrationRunner.php` | 迁移执行器 |

### 3. 框架集成

| 文件 | 修改内容 |
|------|----------|
| `framework/Core/Framework.php` | 集成 PluginManager，加载插件路由 |
| `framework/Core/AttributeRouteLoader.php` | 新增 `loadRoutesFromMultipleDirs()` 支持多目录扫描 |

### 4. CLI 命令

| 命令 | 文件 | 说明 |
|------|------|------|
| `plugin:list` | PluginListCommand.php | 列出所有插件 |
| `plugin:install` | PluginInstallCommand.php | 安装插件 |
| `plugin:uninstall` | PluginUninstallCommand.php | 卸载插件 |
| `plugin:enable` | PluginEnableCommand.php | 启用插件 |
| `plugin:disable` | PluginDisableCommand.php | 禁用插件 |
| `make:plugin` | MakePluginCommand.php | 创建插件骨架 |

### 5. 配置文件

| 文件 | 说明 |
|------|------|
| `config/plugin/plugins.php` | 插件配置模板 |
| `config/plugin/migration.php` | 迁移配置 |

### 6. 示例博客插件

```
plugins/blog/
├── Controllers/
│   └── PostController.php      # 文章控制器
├── Models/
│   ├── Post.php                # 文章模型
│   ├── Category.php            # 分类模型
│   └── Tag.php                 # 标签模型
├── Services/
│   └── PostService.php         # 文章服务
├── database/migrations/
│   ├── 2025_03_31_100000_create_blog_posts_table.php
│   ├── 2025_03_31_100001_create_blog_categories_table.php
│   └── 2025_03_31_100002_create_blog_tags_table.php
├── Hooks/
│   ├── InstallHook.php         # 安装钩子
│   └── UninstallHook.php       # 卸载钩子
├── config/
├── resources/views/
└── plugin.json                 # 插件清单
```

---

## 二、使用方法

### 1. 创建新插件

```bash
php novaphp make:plugin myplugin
```

### 2. 安装插件

```bash
php novaphp plugin:install blog
```

### 3. 启用/禁用插件

```bash
php novaphp plugin:enable blog
php novaphp plugin:disable blog
```

### 4. 卸载插件

```bash
php novaphp plugin:uninstall blog
```

### 5. 查看插件列表

```bash
php novaphp plugin:list
```

---

## 三、插件开发指南

### 1. plugin.json 配置说明

```json
{
    "name": "插件名称（唯一标识）",
    "title": "插件显示标题",
    "version": "语义化版本号",
    "description": "插件描述",
    "author": "作者",
    "namespace": "插件命名空间",
    "requires": {
        "php": "^8.3",
        "novaframe": "^0.8.0"
    },
    "dependencies": {
        "其他插件名": "^1.0.0"
    },
    "hooks": {
        "install": "安装钩子类",
        "uninstall": "卸载钩子类",
        "enable": "启用钩子类",
        "disable": "禁用钩子类"
    },
    "routes": {
        "prefix": "路由前缀",
        "middleware": ["中间件列表"]
    }
}
```

### 2. 迁移文件命名规范

```
YYYY_MM_DD_HHMMSS_迁移名称.php
```

示例：`2025_03_31_100000_create_blog_posts_table.php`

### 3. 控制器路由注解

插件控制器使用与主应用相同的路由注解：

```php
#[Route(path: '/api/blog/posts', methods: ['GET'], name: 'blog.post.list')]
public function list(Request $request): BaseJsonResponse
{
    // ...
}
```

---

## 四、目录结构

```
/workspace
├── app/                          # 主应用
├── plugins/                      # 插件根目录
│   └── blog/                     # 博客插件
├── config/
│   ├── plugin/                   # 插件配置
│   │   ├── plugins.php           # 已安装插件清单
│   │   └── migration.php         # 迁移配置
│   └── ...
└── framework/
    ├── Plugin/                   # 插件核心组件
    │   ├── PluginInterface.php
    │   ├── PluginManifest.php
    │   ├── PluginManager.php
    │   └── Migration/
    │       ├── Migration.php
    │       └── MigrationRunner.php
    └── Console/Commands/
        ├── PluginListCommand.php
        ├── PluginInstallCommand.php
        └── ...
```

---

## 五、向后兼容性

- ✅ 主应用 `app/` 目录结构不变
- ✅ 现有控制器无需修改
- ✅ 现有路由注解继续有效
- ✅ 不安装插件时，框架行为与原来一致

---

## 六、下一步建议

1. **完善 CLI 入口**：需要在 `novaphp` 入口文件中注册新的命令
2. **Web 管理界面**：可后续扩展插件管理的 Web 界面
3. **插件市场**：支持从远程仓库安装插件
4. **插件权限系统**：与现有 Casbin 权限系统集成
5. **缓存优化**：插件路由和配置的缓存机制
