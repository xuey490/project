
## 利用工厂接口类整合Eloquent  和 ThinkORM


- 接口定义 (DatabaseInterface)：明确契约。
- 主工厂 (DatabaseFactory)：负责分发，解耦具体实现。
- ThinkORM 实现：清理死代码，专注于 ThinkPHP ORM 的初始化。
- Eloquent 实现：清理容器绑定逻辑，专注于 Laravel ORM 的初始化。

## 初始化
现在你的调用方式更纯粹，不再依赖全局环境：

```
use Framework\Database\DatabaseFactory;
use Monolog\Logger; // 假设你使用 Monolog

// 1. 准备配置和日志
$config = [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type' => 'mysql',
            'hostname' => '127.0.0.1',
            'database' => 'test_db',
            'username' => 'root',
            'password' => '',
            'debug'    => true, // 控制是否记录日志
        ]
    ]
];

$logger = new Logger('db'); // 你的 PSR-3 Logger

// 2. 实例化工厂 (无缝切换 'think' 或 'eloquent')
$dbFactory = new DatabaseFactory($config, 'eloquent', $logger);

// 3. 使用
// 方式 A: 类名
$userModel = $dbFactory(App\Model\User::class); 

// 方式 B: 表名 (Eloquent模式下返回 Builder, Think模式下返回 Query)
$users = $dbFactory->make('users')->where('id', '>', 1)->get();

```
## 使用范例

根据上面的使用示例，下面补充几个使用表名，模型类来操作数据库的例子，结合illuminate和thinkphp的特性，比如查询(limit，排序，条件查询，分组，统计等)，分页，删除，修改更新，事物回滚等



**核心提示**：虽然通过 `DatabaseFactory` 统一了“获取数据库操作对象”的入口（make），但**获取到的对象**（Query Builder 或 Model）依然是各自 ORM 的原生对象。

因此，**ThinkPHP ORM** 和 **Illuminate (Laravel) ORM** 在具体的方法调用上（如 `get()` vs `select()`, `orderBy()` vs `order()`）是有区别的。

以下是结合这两种 ORM 特性的详细对比使用示例。

---

### 0. 初始化准备

为了演示，假设我们已经通过工厂初始化了数据库。

```php
use Framework\Database\DatabaseFactory;
// 假设已配置好 logger 和 config
// 切换驱动： 'eloquent' (Laravel) 或 'think' (ThinkPHP)
$driverType = 'eloquent'; 
$db = new DatabaseFactory($config, $driverType, $logger);
```

---

### 1. 查询构造器 (Table 模式)

当你传入字符串（表名）时，工厂返回的是查询构造器。

#### 场景：复杂条件、排序、分页、限制数量

```php
// 获取 user 表的操作对象
$query = $db->make('user');

// 通用逻辑：查找状态为1，年龄大于18，按ID倒序，取前10条
if ($driverType === 'eloquent') {
    // === Illuminate (Laravel) 风格 ===
    $list = $query->where('status', 1)
                  ->where('age', '>', 18)
                  ->orderBy('id', 'desc') // Laravel 用 orderBy
                  ->limit(10)
                  ->get(); // Laravel 必须调用 get() 结束
                  
    // 结果是 Illuminate\Support\Collection
    $list->each(function($item) {
        echo $item->username; // 对象方式访问 (配置 fetch mode 后) 或 数组方式
    });

} else {
    // === ThinkORM 风格 ===
    $list = $query->where('status', 1)
                  ->where('age', '>', 18)
                  ->order('id', 'desc') // ThinkPHP 用 order
                  ->limit(10)
                  ->select(); // ThinkPHP 必须调用 select() 结束
                  
    // 结果是 think\Collection
    $list->each(function($item) {
        echo $item['username']; // 默认是数组方式访问
    });
}
```

---

### 2. 模型模式 (Class 模式)

当你传入模型类名时，工厂返回模型实例。

假设定义了 User 模型：
*   Laravel: `class User extends \Illuminate\Database\Eloquent\Model {}`
*   Think: `class User extends \think\Model {}`

#### 场景：新增、修改、查询单条

```php
use App\Model\User;

// 工厂实例化模型 (等价于 new User)
$userModel = $db(User::class); 

// === 插入数据 (Create) ===
// 两种 ORM 这里语法极其相似
$userModel->username = 'xuey863toy';
$userModel->email = 'xuey863toy@gmail.com';
$userModel->save();
// 或者使用 create 方法 (需注意 fillable/allowField 配置)
// User::create([...]);

// === 修改数据 (Update) ===
// 假设我们要更新刚插入的数据
// 注意：工厂返回的是空模型，我们需要先查询出来
if ($driverType === 'eloquent') {
    $user = $userModel->where('username', 'xuey863toy')->first();
    if ($user) {
        $user->email = 'new_email@gmail.com';
        $user->save();
    }
    // 批量更新
    $userModel->where('status', 0)->update(['status' => 1]);

} else {
    // ThinkORM
    $user = $userModel->where('username', 'xuey863toy')->find();
    if ($user) {
        $user->email = 'new_email@gmail.com';
        $user->save();
    }
    // 批量更新
    $userModel->where('status', 0)->update(['status' => 1]);
}
```

---

### 3. 高级查询：分组与统计

语法上有细微差别：Laravel 使用 `groupBy`，ThinkPHP 使用 `group`。

```php
$query = $db->make('orders');

if ($driverType === 'eloquent') {
    // 查询每个用户的订单总金额
    $result = $query->selectRaw('user_id, sum(amount) as total')
                    ->groupBy('user_id')
                    ->having('total', '>', 100)
                    ->get();
                    
    // 统计总数
    $count = $db->make('users')->count();

} else {
    // ThinkORM
    $result = $query->field('user_id, sum(amount) as total')
                    ->group('user_id')
                    ->having('total', '>', 100)
                    ->select();
                    
    // 统计总数
    $count = $db->make('users')->count();
}
```

---

### 4. 分页 (Pagination)

这是最常用的功能，两者返回的分页对象不同，但调用方式类似。

```php
$pageSize = 20;

if ($driverType === 'eloquent') {
    // 返回 Illuminate\Pagination\LengthAwarePaginator
    $list = $db->make('user')->paginate($pageSize);
    
    // 获取数据
    $items = $list->items();
    // 获取总页数
    $lastPage = $list->lastPage();

} else {
    // 返回 think\Paginator
    $list = $db->make('user')->paginate($pageSize);
    
    // 获取数据 (ThinkPHP 分页对象可以直接当迭代器用)
    foreach ($list as $item) {
        // ...
    }
    // 获取总数
    $total = $list->total();
}
```

---

### 5. 删除操作 (Delete)

```php
// 按条件删除
$db->make('user')->where('id', 1)->delete();

// 清空表 (慎用)
// Eloquent: truncate()
// Think: query('TRUNCATE TABLE user') 或 Db::execute
```

---

### 6. 事务处理 (Transactions)

这是重构后最需要注意的地方。
由于工厂已经将 DB 库初始化为全局可用（Think 的 `Db::setConfig` 和 Eloquent 的 `Capsule::setAsGlobal`），我们建议**直接使用各自 ORM 的静态方法来管理事务**，因为事务通常是全局的，而不是绑定在某个模型实例上的。

```php
// 引入必要的 Facade 或 Manager
use Illuminate\Database\Capsule\Manager as Capsule;
use think\facade\Db as ThinkDb;

try {
    if ($driverType === 'eloquent') {
        // === Eloquent 事务 ===
        Capsule::beginTransaction();
        
        try {
            $db->make('user')->where('id', 1)->update(['money' => 0]);
            $db->make('log')->insert(['msg' => 'User 1 cleared']);
            
            Capsule::commit();
        } catch (\Throwable $e) {
            Capsule::rollBack();
            throw $e;
        }

        // 或者使用闭包方式 (推荐)
        Capsule::transaction(function () use ($db) {
            $db->make('user')->insert(['name' => 'A']);
            $db->make('user')->insert(['name' => 'B']);
        });

    } else {
        // === ThinkORM 事务 ===
        ThinkDb::startTrans();
        
        try {
            $db->make('user')->where('id', 1)->update(['money' => 0]);
            $db->make('log')->insert(['msg' => 'User 1 cleared']);
            
            ThinkDb::commit();
        } catch (\Throwable $e) {
            ThinkDb::rollback();
            throw $e;
        }
        
        // 或者使用闭包
        ThinkDb::transaction(function () use ($db) {
             $db->make('user')->insert(['name' => 'A']);
        });
    }
} catch (\Exception $e) {
    echo "事务回滚: " . $e->getMessage();
}
```

### 总结：如何屏蔽差异？

由于两个 ORM 语法（`get` vs `select`, `orderBy` vs `order`）差异客观存在，如果你想在业务代码中完全无感切换，通常有几种做法：

1.  **Repository 模式（推荐）**：在 Factory 之上再封装一层 Repository，比如 `UserRepository->findAll()`，在 Repository 内部根据驱动类型去写 if-else。
2.  **只使用交集语法**：但这很难，因为连最基础的查询结束符都不一样。
3.  **拥抱特定 ORM**：工厂模式主要解决了**初始化**和**多项目复用**的问题。在实际业务中，如果你决定用 Eloquent，就统一按 Eloquent 写；工厂的作用是让你在新开项目想切回 ThinkORM 时，只需改配置和少量业务逻辑，而不需要重写底层连接代码。