<?php

declare(strict_types=1);

namespace Framework\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Framework\Log\LoggerService;

class DatabaseFactory
{
    protected static $capsule;

    public function __construct(array $config, string $ormType, $logger = null)
    {
        echo "DatabaseFactory init: type=$ormType\n";
        if ($ormType === 'laravelORM') {
            $this->initEloquent($config);
        }
    }

    protected function initEloquent(array $config)
    {
        if (static::$capsule) {
            return;
        }

        echo "Initializing Eloquent Capsule...\n";
        $capsule = new Capsule;
        
        $default = $config['default'] ?? 'mysql';
        $connection = $config['connections'][$default] ?? [];
        
        // Map hostname to host if needed
        if (isset($connection['hostname']) && !isset($connection['host'])) {
            $connection['host'] = $connection['hostname'];
        }
        if (isset($connection['type']) && !isset($connection['driver'])) {
            $connection['driver'] = $connection['type'];
        }

        $capsule->addConnection($connection);
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        
        static::$capsule = $capsule;
    }

    public function __call($method, $parameters)
    {
        if (!static::$capsule) {
             throw new \RuntimeException("Database Capsule not initialized!");
        }
        return static::$capsule->getConnection()->$method(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        if (!static::$capsule) {
             throw new \RuntimeException("Database Capsule not initialized!");
        }
        return static::$capsule->getConnection()->$method(...$parameters);
    }
    
    public function connection($connection = null)
    {
        return static::$capsule->getConnection($connection);
    }
    
    public function schema()
    {
        return static::$capsule->schema();
    }
    
    public function transaction(\Closure $callback, $attempts = 1)
    {
        return static::$capsule->getConnection()->transaction($callback, $attempts);
    }
    
    public function foo()
    {
        echo "FOO called!\n";
    }
}
