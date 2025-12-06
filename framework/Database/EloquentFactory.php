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
//use Psr\Log\LoggerInterface;

class EloquentFactory implements DatabaseInterface
{

    protected Capsule $capsule;

	
    public function __construct(
        protected array $config,
		protected ?object $logger = null,
        // protected ?LoggerInterface $logger = null
    ) {
        $this->boot();
    }
	
    protected function boot(): void
    {
        $container = new Container();
        
        // 必须设置 Facade Application，否则 Model 内部分功能无法使用
        Facade::setFacadeApplication($container);

        $this->capsule = new Capsule($container);

        // 1. 配置处理
        $defaultConn = $this->config['default'] ?? 'mysql';
        $connectionConfig = $this->config['connections'][$defaultConn] ?? [];

        // 兼容 ThinkPHP 格式配置
        if (!isset($connectionConfig['driver'])) {
            $connectionConfig = $this->convertThinkToEloquent($connectionConfig);
        }
		
        $this->capsule->addConnection($connectionConfig);

        // 2. 事件分发器 (必须绑定，否则无法监听 SQL)
        $this->capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher($container));

        // 3. 全局设置
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

		$isDebug = $connectionConfig['debug'] ?? false;
        // 4. 日志监听
        if ($this->logger && $isDebug ) {
            $this->listenQueryLog();
        }
    }	
	
    protected function listenQueryLog(): void
    {
        $this->capsule->getConnection()->listen(function (QueryExecuted $query) {
            $time = $query->time; // 毫秒
            
            // 简单格式化 SQL，避免在生产环境进行过重的字符串操作
            $sql = $this->formatSql($query->sql, $query->bindings);
            
            $message = sprintf('[Eloquent ORM] [%.2fms] %s', $time, $sql);

            if ($time > 100) { // 慢查询阈值
                $this->logger->warning($message . ' [SLOW]');
            } else {
                $this->logger->debug($message);
            }
        });
    }

    // ($factory)('App\\Model\\User') —— 会调用 __invoke；
    // $factory->make('App\\Model\\User') —— 直接调用 make()（也可用）。
    public function __invoke(string $modelClass): mixed
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
            'debug'    	=> $cfg['debug']     ?? true,
        ];
    }
}
