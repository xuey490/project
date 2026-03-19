<?php

declare(strict_types=1);

/**
 * 数据库维护服务
 *
 * @package App\Services
 * @author  Genie
 * @date    2026-03-12
 */

namespace App\Services;

use Framework\Basic\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DatabaseMaintainService 数据库维护服务
 */
class DatabaseMaintainService extends BaseService
{
    /**
     * 获取所有数据表
     *
     * @return array
     */
    public function getAllTables(): array
    {
        $tables = [];
        $prefix = config('database.prefix', '');

        try {
            $connection = DB::connection();
            $database = config('database.database');

            // 获取所有表
            $rows = $connection->select("
                SELECT
                    TABLE_NAME as table_name,
                    TABLE_COMMENT as table_comment,
                    TABLE_ROWS as table_rows,
                    DATA_LENGTH as data_length,
                    INDEX_LENGTH as index_length,
                    CREATE_TIME as create_time,
                    UPDATE_TIME as update_time
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = ?
            ", [$database]);

            foreach ($rows as $row) {
                $tables[] = [
                    'table_name' => $row->table_name,
                    'table_comment' => $row->table_comment ?? '',
                    'table_rows' => (int)$row->table_rows,
                    'data_length' => $this->formatBytes((int)$row->data_length),
                    'index_length' => $this->formatBytes((int)$row->index_length),
                    'total_length' => $this->formatBytes((int)$row->data_length + (int)$row->index_length),
                    'create_time' => $row->create_time,
                    'update_time' => $row->update_time,
                ];
            }
        } catch (\Exception $e) {
                // 备用方案：直接查询
                $result = DB::select("SHOW TABLES");
                foreach ($result as $row) {
                    $tableName = array_values((array)$row)[0];
                    $tables[] = [
                        'table_name' => $tableName,
                        'table_comment' => '',
                        'table_rows' => 0,
                        'data_length' => 'N/A',
                        'index_length' => 'N/A',
                        'total_length' => 'N/A',
                    ];
                }
            }

        return $tables;
    }

    /**
     * 获取表字段信息
     *
     * @param string $tableName 表名
     * @return array
     */
    public function getTableColumns(string $tableName): array
    {
        $columns = [];

        try {
            $connection = DB::connection();
            $database = config('database.database');

            $rows = $connection->select("
                SELECT
                    COLUMN_NAME as column_name,
                    COLUMN_TYPE as column_type,
                    IS_NULLABLE as is_nullable,
                    COLUMN_DEFAULT as column_default,
                    COLUMN_COMMENT as column_comment,
                    CHARACTER_MAXIMUM_LENGTH as max_length,
                    ORDINAL_POSITION as position
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ", [$database, $tableName]);

            foreach ($rows as $row) {
                $columns[] = [
                    'column_name' => $row->column_name,
                    'column_type' => $row->column_type,
                    'is_nullable' => $row->is_nullable === 'YES',
                    'column_default' => $row->column_default,
                    'column_comment' => $row->column_comment ?? '',
                    'max_length' => $row->max_length,
                    'position' => (int)$row->position,
                ];
            }
        } catch (\Exception $e) {
            // 备用方案：使用 DESCRIBE
            $result = DB::select("DESCRIBE `{$tableName}`");
            foreach ($result as $row) {
                $columns[] = [
                    'column_name' => $row->Field,
                    'column_type' => $row->Type,
                    'is_nullable' => $row->Null === 'YES',
                    'column_default' => $row->Default,
                    'column_comment' => '',
                    'position' => 0,
                ];
            }
        }

        return $columns;
    }

    /**
     * 获取表索引信息
     *
     * @param string $tableName 表名
     * @return array
     */
    public function getTableIndexes(string $tableName): array
    {
        $indexes = [];

        try {
            $connection = DB::connection();
            $database = config('database.database');

            $rows = $connection->select("
                SELECT
                    INDEX_NAME as index_name,
                    COLUMN_NAME as column_name,
                    NON_UNIQUE as non_unique,
                    SEQ_IN_INDEX as seq
                FROM information_schema.STATISTICS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                ORDER BY INDEX_NAME, SEQ_IN_INDEX
            ", [$database, $tableName]);

            foreach ($rows as $row) {
                $indexName = $row->index_name;
                if (!isset($indexes[$indexName])) {
                    $indexes[$indexName] = [
                        'index_name' => $indexName,
                        'columns' => [],
                        'non_unique' => (bool)$row->non_unique,
                    ];
                }
                $indexes[$indexName]['columns'][] = $row->column_name;
            }
        } catch (\Exception $e) {
            // 备用方案：使用 SHOW INDEX
            $result = DB::select("SHOW INDEX FROM `{$tableName}`");
            foreach ($result as $row) {
                $indexName = $row->Key_name;
                if (!isset($indexes[$indexName])) {
                    $indexes[$indexName] = [
                        'index_name' => $indexName,
                        'columns' => [],
                        'non_unique' => !((bool)$row->Non_unique),
                    ];
                }
                $indexes[$indexName]['columns'][] = $row->Column_name;
            }
        }

        return array_values($indexes);
    }

    /**
     * 优化表
     *
     * @param string $tableName 表名
     * @return array
     */
    public function optimizeTable(string $tableName): array
    {
        try {
            DB::select("OPTIMIZE TABLE `{$tableName}`");
            return [
                'success' => true,
                'message' => "表 {$tableName} 优化完成",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "优化失败: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 分析表
     *
     * @param string $tableName 表名
     * @return array
     */
    public function analyzeTable(string $tableName): array
    {
        try {
            $result = DB::select("ANALYZE TABLE `{$tableName}`");
            return [
                'success' => true,
                'message' => "表 {$tableName} 分析完成",
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "分析失败: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 修复表
     *
     * @param string $tableName 表名
     * @return array
     */
    public function repairTable(string $tableName): array
    {
        try {
            DB::select("REPAIR TABLE `{$tableName}`");
            return [
                'success' => true,
                'message' => "表 {$tableName} 修复完成",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "修复失败: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 清理表碎片
     *
     * @param string $tableName 表名
     * @return array
     */
    public function defragmentTable(string $tableName): array
    {
        try {
            // 先优化
            DB::select("OPTIMIZE TABLE `{$tableName}`");

            // 再分析
            DB::select("ANALYZE TABLE `{$tableName}`");

            return [
                'success' => true,
                'message' => "表 {$tableName} 碎片清理完成",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => "碎片清理失败: " . $e->getMessage(),
            ];
        }
    }

    /**
     * 获取表状态
     *
     * @param string $tableName 表名
     * @return array
     */
    public function getTableStatus(string $tableName): array
    {
        try {
            $result = DB::select("SHOW TABLE STATUS LIKE '{$tableName}'");

            if (!empty($result)) {
                $row = $result[0];
                return [
                    'table_name' => $row->Name,
                    'engine' => $row->Engine,
                    'version' => $row->Version,
                    'row_format' => $row->Row_format,
                    'table_rows' => (int)$row->Rows,
                    'avg_row_length' => (int)$row->Avg_row_length,
                    'data_length' => $this->formatBytes((int)$row->Data_length),
                    'max_data_length' => $this->formatBytes((int)$row->Max_data_length),
                    'index_length' => $this->formatBytes((int)$row->Index_length),
                    'max_index_length' => $this->formatBytes((int)$row->Max_index_length),
                    'data_free' => $this->formatBytes((int)$row->Data_free),
                    'auto_increment' => $row->Auto_increment,
                    'create_time' => $row->Create_time,
                    'update_time' => $row->Update_time,
                    'collation' => $row->Collation,
                    'comment' => $row->Comment ?? '',
                ];
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return [];
    }

    /**
     * 批量优化表
     *
     * @param array $tableNames 表名数组
     * @return array
     */
    public function batchOptimize(array $tableNames): array
    {
        $results = [];

        foreach ($tableNames as $tableName) {
            $results[$tableName] = $this->optimizeTable($tableName);
        }

        return $results;
    }

    /**
     * 格式化字节
     *
     * @param int $bytes 字节数
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $i = 0;

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
