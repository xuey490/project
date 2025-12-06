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

//use Psr\Log\LoggerInterface;
use InvalidArgumentException;

final class DatabaseFactory implements DatabaseInterface
{
    private DatabaseInterface $driver;
	
    /**
     * @param array                $config  数据库配置
     * @param string               $ormType ORM类型 ('laravelORM', 'thinkORM')
     * @param LoggerInterface|null $logger  自定义log类，PSR-3 日志记录器
     */
    public function __construct(
        array $config, 
        string $ormType = 'thinkORM', 
		protected ?object $logger = null
        //?LoggerInterface $logger = null
    ) {

        $this->driver = match ($ormType) {
            'laravelORM', 'laravel' 	=> new EloquentFactory($config, $logger),
            'thinkORM'               	=> new ThinkORMFactory($config, $logger),
            default               		=> throw new InvalidArgumentException("Unsupported ORM type: {$ormType}"),
        };
    }

    public function __invoke(string $modelClass): mixed
    {
        return $this->driver->make($modelClass);
    }

    public function make(string $modelClass): mixed
    {
        return $this->driver->make($modelClass);
    }
}