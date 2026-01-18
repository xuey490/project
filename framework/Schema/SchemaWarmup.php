<?php
declare(strict_types=1);

namespace Framework\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Framework\Basic\BaseLaORMModel;

/**
 * ====================================================
 * SchemaWarmup: 自动扫描 BaseModel 子类并预热
 * ====================================================
 */
final class SchemaWarmup
{
    /** @var string 模型根目录 */
    private static string $modelRootPath = '';

    /** @var string 模型根命名空间 */
    private static string $modelBaseNamespace = '';

    /** @var array 忽略的模型类 */
    private static array $ignoreModels = [];

    /**
     * 设置模型扫描目录
     *
     * @param string $rootPath
     * @param string $baseNamespace
     */
    public static function setScanPath(string $rootPath, string $baseNamespace): void
    {
		$db = app('db');
        self::$modelRootPath = rtrim($rootPath, DIRECTORY_SEPARATOR);
        self::$modelBaseNamespace = rtrim($baseNamespace, '\\');
    }

    /**
     * 设置忽略的模型类
     *
     * @param array $classes
     */
    public static function ignore(array $classes): void
    {
        self::$ignoreModels = $classes;
    }

    /**
     * 自动扫描并 warmup 所有 BaseModel 子类
     */
    public static function warmupAll(): void
    {
        if (!self::$modelRootPath || !self::$modelBaseNamespace) {
            throw new RuntimeException("Model scan path or namespace not set");
        }

        $models = self::scanModels(self::$modelRootPath, self::$modelBaseNamespace);

        foreach ($models as $modelClass) {
            self::warmupModel($modelClass);
        }
    }

    /* ==================== 内部方法 ==================== */

    /**
     * 扫描目录，返回所有 BaseModel 子类
     *
     * @param string $path
     * @param string $namespace
     * @return array
     */
    private static function scanModels(string $path, string $namespace): array
    {
        $classes = [];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            // 解析类名
            $relative = str_replace($path, '', $file->getPathname());
            $relative = trim(str_replace(['/', '\\'], '\\', $relative), '\\');
            $class = $namespace . '\\' . str_replace('.php', '', $relative);

            if (!class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);

            // 忽略抽象类和非 BaseModel 子类
            if ($ref->isAbstract() ||
                !$ref->isSubclassOf(\Framework\Utils\BaseModel::class) ||
                in_array($class, self::$ignoreModels, true)
            ) {
                continue;
            }

            $classes[] = $class;
        }

        return array_values(array_unique($classes));
    }

    /**
     * 预热单个模型
     *
     * @param string $modelClass
     */
    protected static function warmupModel(string $modelClass): void
    {
        // 支持动态别名 BaseModel
        if (!is_subclass_of($modelClass, Model::class) &&
            !is_subclass_of($modelClass, \Framework\Utils\BaseModel::class)
        ) {
            return;
        }

        /** @var Model $model */
        $model = new $modelClass();
		
        $resolver = Model::getConnectionResolver();
        if (!$resolver) {
            throw new RuntimeException('Eloquent ConnectionResolver not initialized');
        }

        // 获取连接
        $connection = $model->getConnectionName() ?? $resolver->getDefaultConnection();
        $conn = $resolver->connection($connection);
        $schema = $conn->getSchemaBuilder();

        // 获取逻辑表名（不带前缀，SchemaBuilder 会自动加前缀）
        $table = $model->getTable();

        // 注册到 SchemaRegistry
        SchemaRegistry::register(
            $table,
            $schema->getColumnListing($table),
            self::loadIndexes($conn, $table),
            self::resolveAuditFields($modelClass)
        );
    }

    /**
     * 加载表索引（演示示例，可根据实际情况扩展）
     *
     * @param \Illuminate\Database\Connection $conn
     * @param string $table
     * @return array
     */
    protected static function loadIndexes($conn, string $table): array
    {
        $indexes = $conn->select("SHOW INDEX FROM `{$conn->getTablePrefix()}{$table}`");
        return $indexes ?: [];
    }

    /**
     * 获取审计字段（演示示例，可根据 BaseModel 约定）
     *
     * @param string $modelClass
     * @return array
     */
    protected static function resolveAuditFields(string $modelClass): array
    {
        $fields = [];

        if (!class_exists($modelClass)) {
            return $fields;
        }

        $ref = new ReflectionClass($modelClass);

        if ($ref->hasProperty('auditColumns')) {
            $prop = $ref->getProperty('auditColumns');
            $prop->setAccessible(true);
            $fields = $prop->getValue(new $modelClass()) ?: [];
        }

        return $fields;
    }
}