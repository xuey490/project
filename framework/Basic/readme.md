
在某些业务情况下，需要多个service层，设计到 **业务编排（Orchestration）**的时候

当一个操作涉及多个独立的 Service（如用户 + 钱包）时，通常由 **Controller**（或者一个专门的 `RegistrationService`）来负责协调，并控制数据库事务。

针对你的框架架构，有两种主要方式来初始化这两个服务。

---

### 方案一：构造函数注入（推荐，最标准）

这是最符合依赖注入（DI）原则的写法。既然 `User` 控制器主要负责用户，但 `register` 方法需要 `WalletService`，我们就在构造函数里把它请进来。

**代码实现：**

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Framework\Database\DatabaseFactory;
use App\Services\UserService;
use App\Services\WalletService;

/**
 * @property UserService $service  // 告诉 IDE，$this->service 就是 UserService
 */
class User extends BaseController
{
    // 指定主服务，父类会自动处理 $this->service
    protected string $serviceClass = UserService::class;

    // 定义额外的服务属性
    private WalletService $walletService;

    public function __construct(
        Request $request, 
        DatabaseFactory $db, 
        UserService $userService,     // 注入主服务
        WalletService $walletService  // 注入副服务
    ) {
        // 1. 调用父类构造，初始化 $request, $db, 和主 $service
        parent::__construct($request, $db, $userService);

        // 2. 初始化副服务
        $this->walletService = $walletService;
    }

    public function register()
    {
        $input = $this->request->request->all();

        // 开启事务
        $this->db->beginTransaction();
        
        try {
            // 1. 创建用户 (使用主服务)
            // 注意：这里用 $this->service 和 $userService 是一样的引用
            $userId = $this->service->createUser($input); 
            
            if (!$userId) {
                throw new \Exception("创建用户失败");
            }

            // 2. 创建钱包 (使用副服务)
            // 假设钱包需要绑定用户ID
            $this->walletService->createWallet($userId); 

            // 提交事务
            $this->db->commit();

            return $this->success(['id' => $userId], '注册成功');

        } catch (\Throwable $e) {
            // 回滚事务
            $this->db->rollback();
            return $this->fail($e->getMessage());
        }
    }
}
```

**优点**：依赖关系清晰，一看构造函数就知道这个控制器依赖哪些服务。
**缺点**：需要写 `__construct`。

---

### 方案二：按需获取（懒人写法）

如果你不想写构造函数，或者 `WalletService` 只有在 `register` 这一处用到，不想污染整个类，可以使用全局容器函数 `app()->make()` 临时获取。

**代码实现：**

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Basic\BaseController;
use App\Services\UserService;
use App\Services\WalletService;

/**
 * @property UserService $service 
 */
class User extends BaseController
{
    protected string $serviceClass = UserService::class;

    // 不需要写 __construct，直接复用父类的

    public function register()
    {
        $input = $this->request->request->all();

        // 临时实例化 WalletService
        /** @var WalletService $walletService */
        $walletService = app()->make(WalletService::class);

        $this->db->beginTransaction();
        try {
            // 1. 主服务
            $userId = $this->service->createUser($input);
            
            // 2. 副服务
            $walletService->createWallet($userId);

            $this->db->commit();
            return $this->success(['id' => $userId]);

        } catch (\Throwable $e) {
            $this->db->rollback();
            return $this->fail('注册失败: ' . $e->getMessage());
        }
    }
}
```

---

### Service 层应该怎么写？

Service 层**不需要知道事务的存在**。它们只需要专注于执行 SQL。只要 Service 内部使用的 `$db` 实例和 Controller 里的 `$this->db` 是**同一个连接实例**（单例模式），事务就会自动生效。

**UserService.php:**
```php
class UserService extends BaseService
{
    public function createUser(array $data): int
    {
        // 这里只是单纯的执行 insert
        // 如果外层没开事务，这就直接写入
        // 如果外层开了事务，这句 SQL 就会等待 commit
        return $this->db->insert('users', $data); 
    }
}
```

**WalletService.php:**
```php
class WalletService extends BaseService
{
    public function createWallet(int $userId): bool
    {
        return (bool)$this->db->insert('wallets', [
            'user_id' => $userId,
            'balance' => 0
        ]);
    }
}
```

### 关键点总结

1.  **单例数据库连接**：确保你的 `DatabaseFactory` 或容器（Container）在一次请求中，返回给 Controller、UserService、WalletService 的是**同一个数据库连接对象**。如果不一致，Controller 开启的事务就管不到 Service 里的操作了。
2.  **异常捕获**：在 Controller 捕获异常并 Rollback 是正确的做法。
3.  **选择方案**：
    *   如果 `WalletService` 在这个控制器里好几个方法都要用 -> **用方案一（构造函数注入）**。
    *   如果只有 `register` 这一次用 -> **用方案二（app()->make）**，代码更少。