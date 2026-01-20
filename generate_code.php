<?php
// generate_code.php

/**
 * 数据库代码生成器
 * 使用方式: php generate_code.php 表名 [类名前缀]
 * 示例: php generate_code.php notic Notice
 */

// ========================================
// 1. 命令行参数处理与校验
// ========================================
// 获取命令行参数
$argv = $_SERVER['argv'];

// 校验参数数量
if (count($argv) < 2) {
    die("使用方法: php {$argv[0]} 表名 [类名]\n示例: php {$argv[0]} notic Notice\n");
}

// 解析参数
$tableName = trim($argv[1]);
// 如果用户指定了类名则使用，否则自动从表名生成（下划线转驼峰）
$className = isset($argv[2]) ? trim($argv[2]) : tableNameToClassName($tableName);

// 校验必要参数
if (empty($tableName)) {
    die("错误: 表名不能为空\n");
}
if (empty($className)) {
    die("错误: 类名不能为空\n");
}

// 定义Service类名（遵循示例中的复数形式，如User->UsersService）
$serviceClassName = $className . 'Service';

// ========================================
// 2. 基础配置与数据库连接
// ========================================
// 数据库配置路径（可根据实际项目调整）
$configPath = __DIR__ . '/config/database.php';
if (!file_exists($configPath)) {
    die("错误: 数据库配置文件不存在 - $configPath\n");
}

// 加载数据库配置
$config = require $configPath;
if (!isset($config['connections']['mysql'])) {
    die("错误: 未找到MySQL数据库配置\n");
}
$dbConfig = $config['connections']['mysql'];

// 构建数据库连接DSN
$dsn = sprintf(
    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
    $dbConfig['hostname'] ?? '127.0.0.1',
    $dbConfig['hostport'] ?? '3306',
    $dbConfig['database'] ?? '',
    $dbConfig['charset'] ?? 'utf8mb4'
);

// 连接数据库
try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ 成功连接到数据库\n";
} catch (PDOException $e) {
    die("❌ 数据库连接失败: " . $e->getMessage() . "\n");
}

// ========================================
// 3. 处理表前缀并获取表结构信息
// ========================================
// 获取数据库表前缀
$tablePrefix = isset($dbConfig['prefix']) ? trim($dbConfig['prefix']) : '';
// 清理表名（去掉前缀）- 用于模型中的$table属性
$modelTableName = $tablePrefix ? preg_replace('/^' . preg_quote($tablePrefix, '/') . '/', '', $tableName) : $tableName;

// 验证清理后的表名是否有效
if (empty($modelTableName)) {
    die("❌ 表名 $tableName 去掉前缀 $tablePrefix 后为空，请检查表名或前缀配置\n");
}

// 拼接完整表名（带前缀）- 用于查询数据库表结构
$fullTableName = $tablePrefix . $modelTableName;

try {
    $stmt = $pdo->query("SHOW COLUMNS FROM `$fullTableName`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($columns)) {
        die("❌ 表 $fullTableName 不存在或无字段信息\n");
    }
} catch (PDOException $e) {
    die("❌ 获取表 $fullTableName 字段失败: " . $e->getMessage() . "\n");
}

// 解析表字段信息
$pk = 'id';
$hasCreatedAt = false;
$hasUpdatedAt = false;
$fields = [];

foreach ($columns as $col) {
    $fields[] = $col['Field'];
    // 识别主键
    if ($col['Key'] === 'PRI') {
        $pk = $col['Field'];
    }
    // 识别时间字段
    if ($col['Field'] === 'created_at') $hasCreatedAt = true;
    if ($col['Field'] === 'updated_at') $hasUpdatedAt = true;
}

echo "✅ 成功获取表结构: 共 " . count($fields) . " 个字段，主键: $pk\n";
echo "✅ 表前缀处理: 原表名=$fullTableName, 模型表名=$modelTableName\n";

// ========================================
// 4. 生成文件（模型、DAO、控制器、Service）
// ========================================
// 定义生成目录（可根据实际项目调整）
$paths = [
    'model'      => __DIR__ . "/app/Models/",
    'dao'        => __DIR__ . "/app/Dao/",
    'controller' => __DIR__ . "/app/Controllers/",
    'service'    => __DIR__ . "/app/Services/"  // 新增Service目录
];

// 确保目录存在
foreach ($paths as $type => $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "📁 创建目录: $path\n";
    }
}

// 4.1 生成模型文件（使用去掉前缀的表名）
generateModelFile($paths['model'], $className, $modelTableName, $pk, $hasCreatedAt, $hasUpdatedAt);

// 4.2 生成DAO文件
generateDaoFile($paths['dao'], $className);

// 4.3 生成控制器文件
generateControllerFile($paths['controller'], $className);

// 4.4 生成Service文件（新增）
generateServiceFile($paths['service'], $serviceClassName, $className);

echo "\n🎉 代码生成完成！\n";
echo "📋 生成文件清单:\n";
echo "  - 模型: {$paths['model']}{$className}.php\n";
echo "  - DAO: {$paths['dao']}{$className}Dao.php\n";
echo "  - 控制器: {$paths['controller']}{$className}.php\n";
echo "  - 服务层: {$paths['service']}{$serviceClassName}.php\n";

// ========================================
// 核心函数定义
// ========================================

/**
 * 表名转类名（下划线转驼峰）
 * @param string $tableName 数据库表名（可带前缀）
 * @return string 驼峰式类名
 */
function tableNameToClassName(string $tableName): string
{
    // 先去掉可能的表前缀（这里是通用处理，实际前缀已在主逻辑处理）
    $tableName = preg_replace('/^[a-z0-9]+_/', '', $tableName);
    // 下划线转驼峰
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName)));
    // 确保首字母大写
    return ucfirst($className);
}

/**
 * 生成模型文件
 * @param string $dir 生成目录
 * @param string $className 类名
 * @param string $tableName 表名（去掉前缀的）
 * @param string $pk 主键
 * @param bool $hasCreatedAt 是否有created_at字段
 * @param bool $hasUpdatedAt 是否有updated_at字段
 */
function generateModelFile(string $dir, string $className, string $tableName, string $pk, bool $hasCreatedAt, bool $hasUpdatedAt): void
{
    $filePath = $dir . $className . '.php';
    
    $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Models;\n\nuse Framework\Utils\BaseModel;\n\nclass {$className} extends BaseModel\n{\n    protected \$table = '{$tableName}';\n    protected \$pk = '{$pk}';\n\n";

    if ($hasCreatedAt || $hasUpdatedAt) {
        $content .= "    protected \$autoWriteTimestamp = true;\n";
        if ($hasCreatedAt) $content .= "    protected \$createTime = 'created_at';\n";
        if ($hasUpdatedAt) $content .= "    protected \$updateTime = 'updated_at';\n";
    }

    $content .= "}\n";

    file_put_contents($filePath, $content);
    echo "✅ 生成模型文件: $filePath\n";
}

/**
 * 生成DAO文件
 * @param string $dir 生成目录
 * @param string $className 类名
 */
function generateDaoFile(string $dir, string $className): void
{
    $daoClassName = $className . 'Dao';
    $filePath = $dir . $daoClassName . '.php';
    
    $content = "<?php\n\nnamespace App\Dao;\n\nuse Framework\Basic\BaseDao;\nuse App\Models\\{$className};\n\nclass {$daoClassName} extends BaseDao\n{\n    protected function setModel(): string\n    {\n        return {$className}::class;\n    }\n}\n";

    file_put_contents($filePath, $content);
    echo "✅ 生成DAO文件: $filePath\n";
}

/**
 * 生成控制器文件
 * @param string $dir 生成目录
 * @param string $className 类名
 */
function generateControllerFile(string $dir, string $className): void
{
    $serviceClassName = $className . 'Service';
    $filePath = $dir . $className . '.php';
    
    $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Controllers;\n\nuse App\Services\\{$serviceClassName};\nuse Symfony\Component\HttpFoundation\Request;\nuse Symfony\Component\HttpFoundation\Response;\nuse Symfony\Component\HttpFoundation\JsonResponse;\n\nclass {$className}\n{\n    public function __construct(\n        private {$serviceClassName} \$service\n    ) {}\n\n    public function index(Request \$request): Response\n    {\n        \$page = (int) \$request->get('page', 1);\n        \$size = (int) \$request->get('size', 10);\n        // selectList(array \$where = [], string \$field = '*', int \$page = 1, int \$limit = 10, string \$order = '', bool \$paginate = false)\n        \$list = \$this->service->selectList([], '*', \$page, \$size);\n        \n        return new JsonResponse([\n            'code' => 200,\n            'data' => \$list,\n            'message' => 'success'\n        ]);\n    }\n\n    public function show(int \$id): Response\n    {\n        \$item = \$this->service->get(\$id);\n        if (!\$item) {\n            return new JsonResponse(['code' => 404, 'message' => 'Not Found'], 404);\n        }\n        return new JsonResponse(['code' => 200, 'data' => \$item]);\n    }\n\n    public function store(Request \$request): Response\n    {\n        \$data = \$request->request->all();\n        // TODO: Add validation based on table fields\n        \$id = \$this->service->create(\$data);\n        return new JsonResponse(['code' => 201, 'data' => ['id' => \$id], 'message' => 'Created'], 201);\n    }\n\n    public function update(int \$id, Request \$request): Response\n    {\n        \$data = \$request->request->all();\n        \$this->service->update(\$id, \$data);\n        return new JsonResponse(['code' => 200, 'message' => 'Updated']);\n    }\n\n    public function destroy(int \$id): Response\n    {\n        \$this->service->delete(\$id);\n        return new JsonResponse(['code' => 200, 'message' => 'Deleted']);\n    }\n}\n";

    file_put_contents($filePath, $content);
    echo "✅ 生成控制器文件: $filePath\n";
}

/**
 * 生成Service层文件（新增）
 * 完全仿照示例代码的样式、命名空间和依赖注入方式
 * @param string $dir 生成目录
 * @param string $serviceClassName Service类名（如UsersService）
 * @param string $className 基础类名（如User）
 */
function generateServiceFile(string $dir, string $serviceClassName, string $className): void
{
    $daoClassName = $className . 'Dao';
    $filePath = $dir . $serviceClassName . '.php';
    
    // 严格按照示例代码的格式生成，包括注释、泛型注解、引入的类等
    $content = <<<PHP
<?php
declare(strict_types=1);

namespace App\Services;

use Framework\Basic\BaseService;
use App\Dao\\{$daoClassName};
use Framework\Core\App;
use Framework\DI\Attribute\Inject;
use Framework\DI\Attribute\Autowire;
use Framework\Basic\BaseDao; // 引入父类类型

/**
 * {$className}服务层
 * @extends BaseService<{$daoClassName}> // 指定泛型类型为 {$daoClassName}
 */
class {$serviceClassName} extends BaseService
{

    #protected ?{$daoClassName} \$dao; 


    public function __construct()
    {
        parent::__construct();
        \$this->dao = App::make({$daoClassName}::class);
        
    }
}	
PHP;

    file_put_contents($filePath, $content);
    echo "✅ 生成Service文件: $filePath\n";
}