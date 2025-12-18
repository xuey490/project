## 关于注解控制器的写法

我们这里演示如何使用注解声明

这个控制器类演示了如何在一个文件中混合使用 **Spring 风格路由注解** (`RequestMapping`, `GetMapping` 等)、**业务逻辑注解** (`Auth`, `Role`, `Log`) 以及 **通用中间件注入注解** (`Middlewares`)。

基于我们之前的分析，**路由器（Router）** 会先解析路由注解，**调度器（Dispatcher）** 随后会扫描所有注解并合并中间件，最终形成洋葱调用链。

### 示例代码：`UserController.php`

```php
<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: UserController.php
 * @Date: 2025-12-18
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace App\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

// 引入元数据注解
use Framework\Attributes\Auth;
use Framework\Attributes\Role;
use Framework\Attributes\Log;
use Framework\Attributes\Middlewares;
use Framework\Attributes\Transaction; // 顺便加上事务演示
// 假设你有一些 Spring 风格的路由注解
use Framework\Attributes\Routing\RestController;
use Framework\Attributes\Routing\GetMapping;
use Framework\Attributes\Routing\PostMapping;
use Framework\Attributes\Routing\DeleteMapping;

// 引入中间件类
use App\Middlewares\CorsMiddleware;
use App\Middlewares\RateLimitMiddleware;
use App\Middlewares\JsonFormatterMiddleware;

/**
 * 用户管理控制器
 * 
 * 1. #[RestController]: 声明这是一个 API 控制器，路由前缀为 /api/v1/users
 * 2. #[Middlewares]: 类级别的中间件，该类下所有方法都会经过 Cors 和 JsonFormatter
 */
#[RestController(prefix: '/api/v1/users')]
#[Middlewares([
    CorsMiddleware::class,          // 全局处理跨域
    JsonFormatterMiddleware::class  // 全局统一 JSON 返回格式
])]
class UserController
{
    /**
     * 用户登录
     * 
     * - 公开接口，不需要 #[Auth]
     * - 使用 #[Log] 记录尝试登录的行为
     * - 使用 #[PostMapping] 定义路由
     */
    #[PostMapping(path: '/login')]
    #[Log(description: '用户登录尝试', level: 'info')]
    public function login(Request $request): JsonResponse
    {
        // 模拟登录逻辑
        $username = $request->get('username');
        // ... 验证逻辑
        
        return new JsonResponse(['token' => 'mock_token_123456']);
    }

    /**
     * 获取个人资料
     * 
     * - #[Auth]: 需要登录才能访问
     * - #[Log]: 记录访问日志
     * - #[GetMapping]: GET 请求
     */
    #[GetMapping(path: '/profile')]
    #[Auth] 
    #[Log(description: '查看个人资料')]
    public function profile(): JsonResponse
    {
        // 中间件已验证身份，可直接获取当前用户
        // $user = app('auth')->user();
        
        return new JsonResponse([
            'id' => 1,
            'username' => 'xuey863toy',
            'role' => 'editor'
        ]);
    }

    /**
     * 管理员删除用户
     * 
     * 这是一个“重兵把守”的接口：
     * 1. #[Auth]: 必须登录
     * 2. #[Role]: 必须是 'admin' 或 'super_admin' 角色
     * 3. #[Middlewares]: 额外注入了 RateLimitMiddleware，防止暴力调用
     * 4. #[Transaction]: 数据库操作自动开启事务
     * 5. #[Log]: 记录高危操作日志，级别为 warning
     */
    #[DeleteMapping(path: '/{id}')]
    #[Auth]
    #[Role(roles: ['admin', 'super_admin'])]
    #[Middlewares([RateLimitMiddleware::class])] 
    #[Transaction]
    #[Log(description: '删除用户', level: 'warning')]
    public function delete(int $id): JsonResponse
    {
        // 业务逻辑：如果这里抛错，Transaction 会回滚
        // app('db')->table('users')->delete($id);
        
        return new JsonResponse(['message' => "User {$id} has been deleted."]);
    }
}
```

---

### 幕后：MiddlewareDispatcher 是如何工作的？

当请求 `DELETE /api/v1/users/1` 到达时，你的 **调度器 (Dispatcher)** 会按以下步骤“组装”中间件链：

1.  **收集 (Collect)**：
    *   **Global**: `[GlobalMiddlewareA, ...]`
    *   **Class (Middlewares)**: `[CorsMiddleware, JsonFormatterMiddleware]`
    *   **Method (Auth)**: `[AuthMiddleware]` (由 `#[Auth]` 提供)
    *   **Method (Role)**: `[RoleMiddleware]` (由 `#[Role]` 提供)
    *   **Method (Middlewares)**: `[RateLimitMiddleware]`
    *   **Method (Transaction)**: `[TransactionMiddleware]`
    *   **Method (Log)**: `[LogMiddleware]`

    *(注意：`#[RestController]` 和 `#[DeleteMapping]` 属于路由注解，主要用于 UrlMatcher 匹配路由，它们解析出的中间件（如果有）也会被放入集合)*

2.  **拍平 (Flatten)**：
    调度器调用 `flattenArray`，将上述嵌套结构变成一维数组。

3.  **排序与去重 (Merge & Sort)**：
    最终执行顺序（洋葱模型，从外到内）：
    1.  `GlobalMiddleware` ...
    2.  `CorsMiddleware` (类级别)
    3.  `JsonFormatterMiddleware` (类级别)
    4.  `AuthMiddleware` (方法级，身份验证优先)
    5.  `RoleMiddleware` (方法级，权限验证次之)
    6.  `RateLimitMiddleware` (方法级，注入的限流)
    7.  `LogMiddleware` (日志记录)
    8.  `TransactionMiddleware` (事务包裹)
    9.  **`delete()` 方法本体**

4.  **属性注入 (Injection)**：
    在反射扫描阶段，`#[Role(['admin'])]` 这个**对象实例**已经被注入到了 `$request->attributes->set(Role::class, $instance)`。
    当 `RoleMiddleware` 执行时，它不需要知道自己是在哪里被定义的，它只需要：
    ```php
    $config = $request->attributes->get(Role::class);
    // $config->roles 就是 ['admin', 'super_admin']
    ```

这就是构建的这套架构的强大之处：**注解负责声明（What），调度器负责组装（How），中间件负责执行（Do），完美解耦。**