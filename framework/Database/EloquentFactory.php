<?php

declare(strict_types=1);

/**
 * This file is part of FssPHP Framework.
 *
 * @link     https://github.com/xuey490/project
 * @license  https://github.com/xuey490/project/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-11-24
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Database;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Events\QueryExecuted; // 引入查询执行事件类
use Psr\Log\LoggerInterface;

class EloquentFactory implements DatabaseInterface
{
    protected array $config;
    protected Capsule $capsule;
    protected ?LoggerInterface $logger;

    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        $this->config  = $config;
        $this->logger  = $logger;

        $container = new Container();
		
		// ⚡ 关键1：给 Facade 设置容器
        Facade::setFacadeApplication($container);

        $this->capsule = new Capsule($container);

        try {
            $connection = $this->config['connections'][$this->config['default']] ?? [];
            
            if (! isset($connection['driver'])) {
                $connection = $this->convertThinkToEloquent($connection);
            }
            
            $this->capsule->addConnection($connection);
            // 必须：设置事件分发器，否则 Eloquent 的模型事件和 Query Listen 都不会生效
            // Capsule 内部会自动处理，但确保 Container 绑定是关键
            $this->capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher($container));

			// 全局静态访问
			$this->capsule->setAsGlobal();

            // 启动 Eloquent ORM
			$this->capsule->bootEloquent();

            // ---------------------------------------------------------
            // 核心修改：使用 listen 实时记录日志，而不是 enableQueryLog
            // ---------------------------------------------------------
            $this->capsule->getConnection()->listen(function (QueryExecuted $query) {
                
                // 仅当 logger 存在时记录
                if ($this->logger) {
                    $sql = $query->sql;
                    $bindings = $query->bindings;
                    $time = $query->time; // 执行时间(毫秒)

                    // 格式化 SQL (可选，方便阅读)
                    $fullSql = $this->formatSql($sql, $bindings);

                    // 记录日志：级别可以根据耗时调整，比如超过 100ms 记为 warning
                    $message = sprintf('[Illuminate Database] [%.2fms] %s', $time, $fullSql);
                    
                    if ($time > 100) {
                        $this->logger->warning($message . ' [SLOW QUERY]');
                    } else {
                        $this->logger->debug($message);
                    }
                }
            });

			// ⚡ 关键3：注册 db（DatabaseManager）
            // 绑定数据库管理器到容器
            $db = $this->capsule;
            $container->singleton('db', fn () => $db->getDatabaseManager());

        } catch (\Throwable $e) {
            $this->logWarn('Eloquent init failed: ' . $e->getMessage());
        }
    }

    // ($factory)('App\\Model\\User') —— 会调用 __invoke；
    // $factory->make('App\\Model\\User') —— 直接调用 make()（也可用）。
    public function __invoke(string $modelClass)
    {
        return $this->make($modelClass);
    }

    public function make(string $modelClass): mixed
    {
        if (class_exists($modelClass) && is_subclass_of($modelClass, Model::class)) {
            return new $modelClass();
        }
        return $this->capsule->table($modelClass);
    }


    /**
     * 辅助方法：将绑定的参数回填到 SQL 中（仅用于日志展示，不要用于实际执行，防止注入误解）
     */
    private function formatSql(string $sql, array $bindings): string
    {
        if (empty($bindings)) {
            return $sql;
        }

        // 处理 DateTime 对象等特殊绑定
        foreach ($bindings as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $bindings[$key] = $value->format('Y-m-d H:i:s');
            } elseif (is_string($value)) {
                $bindings[$key] = "'{$value}'"; // 给字符串加引号
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? '1' : '0';
            } elseif ($value === null) {
                $bindings[$key] = 'null';
            }
        }

        // 简单替换 ? 为参数
        return \Illuminate\Support\Str::replaceArray('?', $bindings, $sql);
    }

    protected function logWarn(string $msg, array $ctx = []): void
    {
        if ($this->logger) {
            $this->logger->warning($msg, $ctx);
        } else {
            error_log('[WARN] ' . $msg . ' ' . json_encode($ctx, JSON_UNESCAPED_UNICODE));
        }
    }

    private function convertThinkToEloquent(array $cfg): array
    {
        return [
            'driver'    => $cfg['type']      ?? 'mysql',
            'host'      => $cfg['hostname']  ?? $cfg['host'] ?? '127.0.0.1',
            'port'      => $cfg['port']      ?? '3306',
            'database'  => $cfg['database']  ?? '',
            'username'  => $cfg['username']  ?? '',
            'password'  => $cfg['password']  ?? '',
            'charset'   => $cfg['charset']   ?? 'utf8mb4',
            'collation' => $cfg['collation'] ?? 'utf8mb4_unicode_ci',
            'prefix'    => $cfg['prefix']    ?? '',
            'strict'    => $cfg['strict']    ?? true,
        ];
    }
}
