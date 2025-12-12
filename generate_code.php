<?php
// generate_code.php

// Basic configuration
$tableName = 'ma_sys_notice';
$className = 'Notice';

// Load DB config
$config = require __DIR__ . '/config/database.php';
$dbConfig = $config['connections']['mysql'];

$dsn = "mysql:host={$dbConfig['hostname']};port={$dbConfig['hostport']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo "Connected to database.\n";

// Get columns
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM $tableName");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to get columns for table $tableName: " . $e->getMessage() . "\n");
}

$pk = 'id';
$hasCreatedAt = false;
$hasUpdatedAt = false;
$fields = [];

foreach ($columns as $col) {
    $fields[] = $col['Field'];
    if ($col['Key'] === 'PRI') {
        $pk = $col['Field'];
    }
    if ($col['Field'] === 'created_at') $hasCreatedAt = true;
    if ($col['Field'] === 'updated_at') $hasUpdatedAt = true;
}

echo "Found " . count($fields) . " columns. PK: $pk\n";

// 1. Generate Model
$modelContent = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Models;\n\nuse Framework\Utils\BaseModel;\n\nclass $className extends BaseModel\n{\n    protected \$table = '$tableName';\n    protected \$pk = '$pk';\n\n";

if ($hasCreatedAt || $hasUpdatedAt) {
    $modelContent .= "    protected \$autoWriteTimestamp = true;\n";
    if ($hasCreatedAt) $modelContent .= "    protected \$createTime = 'created_at';\n";
    if ($hasUpdatedAt) $modelContent .= "    protected \$updateTime = 'updated_at';\n";
}

$modelContent .= "}\n";

$modelPath = __DIR__ . "/app/Models/$className.php";
file_put_contents($modelPath, $modelContent);
echo "Generated $modelPath\n";

// 2. Generate DAO
$daoContent = "<?php\n\nnamespace App\Dao;\n\nuse Framework\Basic\BaseDao;\nuse App\Models\\$className;\n\nclass {$className}Dao extends BaseDao\n{\n    protected function setModel(): string\n    {\n        return $className::class;\n    }\n}\n";

$daoPath = __DIR__ . "/app/Dao/{$className}Dao.php";
file_put_contents($daoPath, $daoContent);
echo "Generated $daoPath\n";

// 3. Generate Controller
$controllerContent = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\Controllers;\n\nuse App\Dao\\{$className}Dao;\nuse Symfony\Component\HttpFoundation\Request;\nuse Symfony\Component\HttpFoundation\Response;\nuse Symfony\Component\HttpFoundation\JsonResponse;\n\nclass $className\n{\n    public function __construct(\n        private {$className}Dao \$dao\n    ) {}\n\n    public function index(Request \$request): Response\n    {\n        \$page = (int) \$request->get('page', 1);\n        \$size = (int) \$request->get('size', 10);\n        // selectList(array \$where = [], string \$field = '*', int \$page = 1, int \$limit = 10, string \$order = '', bool \$paginate = false)\n        \$list = \$this->dao->selectList([], '*', \$page, \$size);\n        \n        return new JsonResponse([\n            'code' => 200,\n            'data' => \$list,\n            'message' => 'success'\n        ]);\n    }\n\n    public function show(int \$id): Response\n    {\n        \$item = \$this->dao->find(\$id);\n        if (!\$item) {\n            return new JsonResponse(['code' => 404, 'message' => 'Not Found'], 404);\n        }\n        return new JsonResponse(['code' => 200, 'data' => \$item]);\n    }\n\n    public function store(Request \$request): Response\n    {\n        \$data = \$request->request->all();\n        // TODO: Add validation based on table fields\n        \$id = \$this->dao->create(\$data);\n        return new JsonResponse(['code' => 201, 'data' => ['id' => \$id], 'message' => 'Created']);\n    }\n\n    public function update(int \$id, Request \$request): Response\n    {\n        \$data = \$request->request->all();\n        \$this->dao->update(\$id, \$data);\n        return new JsonResponse(['code' => 200, 'message' => 'Updated']);\n    }\n\n    public function destroy(int \$id): Response\n    {\n        \$this->dao->delete(\$id);\n        return new JsonResponse(['code' => 200, 'message' => 'Deleted']);\n    }\n}\n";

$controllerPath = __DIR__ . "/app/Controllers/$className.php";
file_put_contents($controllerPath, $controllerContent);
echo "Generated $controllerPath\n";

echo "Done.\n";
