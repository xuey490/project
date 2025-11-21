<?php
declare(strict_types=1);

namespace Framework\Utils;

use Framework\Utils\ModelFactoryInterface;
use Framework\Utils\EloquentFactory;
use Framework\Utils\ThinkORMFactory;

final class ORMFactory implements ModelFactoryInterface
{
    protected ModelFactoryInterface $impl;

    /**
     * @param array $config 数据库配置
     * @param string $ormType 'think' 或 'eloquent'
     */
    public function __construct(array $config, string $ormType = 'think', ?LoggerInterface $logger = null)
    {
        switch (strtolower($ormType)) {
            case 'eloquent':
                $this->impl = new EloquentFactory($config, $logger);
                break;
            case 'think':
            default:
                $this->impl = new ThinkORMFactory($config, $logger);
                break;
        }
    }
	
    public function make(string $modelClass): mixed
    {
        return $this->impl->make($modelClass);
    }

    public function __invoke(string $modelClass): mixed
    {
        return $this->impl->__invoke($modelClass);
    }
}
