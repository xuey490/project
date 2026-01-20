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
$argv = $_SERVER['argv'];
if (count($argv) < 2) {
    die("使用方法: php {$argv[0]} 表名 [类名]\n示例: php {$argv[0]} ma_sys_notice Notice\n");
}

$tableName = trim($argv[1]);
$className = isset($argv[2]) ? trim($argv[2]) : tableNameToClassName($tableName);
$serviceClassName = rtrim($className, 's') . 'Service'; // Service类名（复数形式）
$daoClassName = $className . 'Dao'; // DAO类名

if (empty($tableName)) die("错误: 表名不能为空\n");
if (empty($className)) die("错误: 类名不能为空\n");

// ========================================
// 2. 基础配置与数据库连接
// ========================================
$configPath = __DIR__ . '/config/database.php';
if (!file_exists($configPath)) {
    die("错误: 数据库配置文件不存在 - $configPath\n");
}

$config = require $configPath;
if (!isset($config['connections']['mysql'])) {
    die("错误: 未找到MySQL数据库配置\n");
}
$dbConfig = $config['connections']['mysql'];

$dsn = sprintf(
    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
    $dbConfig['hostname'] ?? '127.0.0.1',
    $dbConfig['hostport'] ?? '3306',
    $dbConfig['database'] ?? '',
    $dbConfig['charset'] ?? 'utf8mb4'
);

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
$tablePrefix = isset($dbConfig['prefix']) ? trim($dbConfig['prefix']) : '';
$modelTableName = $tablePrefix ? preg_replace('/^' . preg_quote($tablePrefix, '/') . '/', '', $tableName) : $tableName;

if (empty($modelTableName)) {
    die("❌ 表名 $tableName 去掉前缀 $tablePrefix 后为空，请检查表名或前缀配置\n");
}

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

$pk = 'id';
$hasCreatedAt = false;
$hasUpdatedAt = false;
foreach ($columns as $col) {
    if ($col['Key'] === 'PRI') $pk = $col['Field'];
    if ($col['Field'] === 'created_at') $hasCreatedAt = true;
    if ($col['Field'] === 'updated_at') $hasUpdatedAt = true;
}

echo "✅ 成功获取表结构: 共 " . count($columns) . " 个字段，主键: $pk\n";
echo "✅ 表前缀处理: 原表名=$fullTableName, 模型表名=$modelTableName\n";

// ========================================
// 4. 生成文件（模型、DAO、控制器、Service）
// ========================================
$paths = [
    'model'      => __DIR__ . "/app/Models/",
    'dao'        => __DIR__ . "/app/Dao/",
    'controller' => __DIR__ . "/app/Controllers/",
    'service'    => __DIR__ . "/app/Services/"
];

// 确保目录存在
foreach ($paths as $type => $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "📁 创建目录: $path\n";
    }
}

// 生成各层文件
generateModelFile($paths['model'], $className, $modelTableName, $pk, $hasCreatedAt, $hasUpdatedAt);
generateDaoFile($paths['dao'], $daoClassName, $className); // 重构后的DAO生成
generateControllerFile($paths['controller'], $className, $serviceClassName);
generateServiceFile($paths['service'], $serviceClassName, $daoClassName); // 重构后的Service生成

echo "\n🎉 代码生成完成！\n";
echo "📋 生成文件清单:\n";
echo "  - 模型: {$paths['model']}{$className}.php\n";
echo "  - DAO: {$paths['dao']}{$daoClassName}.php\n";
echo "  - 控制器: {$paths['controller']}{$className}.php\n";
echo "  - 服务层: {$paths['service']}{$serviceClassName}.php\n";

// ========================================
// 核心函数定义
// ========================================

/**
 * 表名转类名（下划线转驼峰）
 */
function tableNameToClassName(string $tableName): string
{
    $tableName = preg_replace('/^[a-z0-9]+_/', '', $tableName);
    $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName)));
    return ucfirst($className);
}

/**
 * 生成模型文件
 */
function generateModelFile(string $dir, string $className, string $tableName, string $pk, bool $hasCreatedAt, bool $hasUpdatedAt): void
{
    $filePath = $dir . $className . '.php';
    
    $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Models;\n\nuse Framework\Utils\BaseModel;\n\nclass {$className} extends BaseModel\n{\n    protected \$name = '{$tableName}';\n    protected \$pk = '{$pk}';\n\n";

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
 * 重构后的DAO生成函数（匹配项目规范）
 */
function generateDaoFile(string $dir, string $daoClassName, string $modelClassName): void
{
    $filePath = $dir . $daoClassName . '.php';
    
    // 严格按照项目DAO规范生成，包含严格类型、命名空间、BaseDao继承、模型绑定
    $content = <<<PHP
<?php
declare(strict_types=1);

namespace App\Dao;

use Framework\Basic\BaseDao;
use App\Models\\{$modelClassName};

/**
 * {$modelClassName}数据访问层
 * @extends BaseDao<{$modelClassName}>
 */
class {$daoClassName} extends BaseDao
{
	protected string \$modelClass = {$modelClassName}::class;
	
    /**
     * 绑定模型类
     */
    protected function setModel(): string
    {
        return {$modelClassName}::class;
    }
}
PHP;

    file_put_contents($filePath, $content);
    echo "✅ 生成DAO文件: $filePath\n";
}

/**
 * 生成控制器文件（适配新的DAO/Service规范）
 */
function generateControllerFile(string $dir, string $className, string $serviceClassName): void
{
    $filePath = $dir . $className . '.php';
    
    $content = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Controllers;\n\nuse App\Services\\{$serviceClassName};\nuse Symfony\Component\HttpFoundation\Request;\nuse Symfony\Component\HttpFoundation\Response;\nuse Symfony\Component\HttpFoundation\JsonResponse;\n\nclass {$className}\n{\n    public function __construct(\n        private {$serviceClassName} \$dao\n    ) {}\n\n    public function index(Request \$request): Response\n    {\n        \$page = (int) \$request->get('page', 1);\n        \$size = (int) \$request->get('size', 10);\n        \$list = \$this->dao->selectList([], '*', \$page, \$size);\n        \n        return new JsonResponse([\n            'code' => 200,\n            'data' => \$list,\n            'message' => 'success'\n        ]);\n    }\n\n    public function show(int \$id): Response\n    {\n        \$item = \$this->dao->find(\$id);\n        if (!\$item) {\n            return new JsonResponse(['code' => 404, 'message' => 'Not Found'], 404);\n        }\n        return new JsonResponse(['code' => 200, 'data' => \$item]);\n    }\n\n    public function store(Request \$request): Response\n    {\n        \$data = \$request->request->all();\n        // TODO: Add validation based on table fields\n        \$id = \$this->dao->create(\$data);\n        return new JsonResponse(['code' => 201, 'data' => ['id' => \$id], 'message' => 'Created'], 201);\n    }\n\n    public function update(int \$id, Request \$request): Response\n    {\n        \$data = \$request->request->all();\n        \$this->dao->update(\$id, \$data);\n        return new JsonResponse(['code' => 200, 'message' => 'Updated']);\n    }\n\n    public function destroy(int \$id): Response\n    {\n        \$this->dao->delete(\$id);\n        return new JsonResponse(['code' => 200, 'message' => 'Deleted']);\n    }\n}\n";

    file_put_contents($filePath, $content);
    echo "✅ 生成控制器文件: $filePath\n";
}

/**
 * 重构后的Service生成函数（完全匹配示例规范）
 */
function generateServiceFile(string $dir, string $serviceClassName, string $daoClassName): void
{
    $filePath = $dir . $serviceClassName . '.php';
    
    // 1:1 复刻示例代码格式，包含Inject注解、DatabaseFactory构造注入、initialize方法
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
#use Framework\Database\DatabaseFactory;

/**
 * {$daoClassName}服务层
 * @extends BaseService<{$daoClassName}> // 指定泛型类型为 {$daoClassName}
 */
class {$serviceClassName} extends BaseService
{

    // 关键：通过 @Inject 注解注入 DAO
    #[Inject(id:{$daoClassName}::class)]
    protected ?BaseDao \$dao = null;

    public function __construct(
        //protected DatabaseFactory \$db // 构造函数注入
    ) {
        parent::__construct(); // 必须调用父类构造函数执行 inject()
    }

    /**
     * 子类可根据需要覆盖 lifecycle
     */
    protected function initialize(): void
    {
        #parent::initialize();
    }
	
}
PHP;

    file_put_contents($filePath, $content);
    echo "✅ 生成Service文件: $filePath\n";
}