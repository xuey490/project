<?php
declare(strict_types=1);

namespace Framework\Utils;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Psr\Log\LoggerInterface;
use Throwable;
use Framework\Utils\ModelFactoryInterface;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;


class EloquentFactory implements ModelFactoryInterface
{
    protected array $config;
    protected Capsule $capsule;
    protected ?LoggerInterface $logger;

    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger;
		
        $container = new Container;

        Facade::setFacadeApplication($container);
		
		$this->capsule = new Capsule($container);
		
        try {
            // 添加默认连接
			$connection = $this->config['connections'][$this->config['default']] ?? [];

			if (!isset($connection['driver'])) {
				// ThinkORM 格式 → 自动转换
				$connection = $this->convertThinkToEloquent($connection);
			}
            $this->capsule->addConnection($connection);

            // 可选：全局静态访问
            $this->capsule->setAsGlobal();

            // 启动 Eloquent ORM
            $this->capsule->bootEloquent();

            // SQL 日志监听
            $this->capsule->getConnection()->enableQueryLog();

			$db = $this->capsule;

			// **关键：绑定 db 到容器**
			$container->singleton('db', function() use ($db) {
				return $db->getDatabaseManager();
			});
        } catch (Throwable $e) {
            $this->logWarn("Eloquent init failed: " . $e->getMessage());
        }
    }
	
	private function convertThinkToEloquent(array $cfg): array
	{
		return [
			'driver'    => $cfg['type'] ?? 'mysql',
			'host'      => $cfg['hostname'] ?? $cfg['host'] ?? '127.0.0.1',
			'port'      => $cfg['port'] ?? '3306',
			'database'  => $cfg['database'] ?? '',
			'username'  => $cfg['username'] ?? '',
			'password'  => $cfg['password'] ?? '',
			'charset'   => $cfg['charset'] ?? 'utf8mb4',
			'collation' => $cfg['collation'] ?? 'utf8mb4_unicode_ci',
			'prefix'    => $cfg['prefix'] ?? '',
			'strict'    => $cfg['strict'] ?? true,
		];
	}

    public function make(string $modelClass):mixed
    {
        if (class_exists($modelClass) && is_subclass_of($modelClass, Model::class)) {
			dump($modelClass);
            return new $modelClass();
        }

        // 如果不是模型类，返回查询构造器（Table）
        return $this->capsule->table($modelClass);
    }

	//($factory)('App\\Model\\User') —— 会调用 __invoke；
	//$factory->make('App\\Model\\User') —— 直接调用 make()（也可用）。
    public function __invoke(string $modelClass)
    {
        return $this->make($modelClass);
    }

    protected function logWarn(string $msg, array $ctx = []): void
    {
        if ($this->logger) {
            $this->logger->warning($msg, $ctx);
        } else {
            error_log('[WARN] ' . $msg . ' ' . json_encode($ctx, JSON_UNESCAPED_UNICODE));
        }
    }
}
