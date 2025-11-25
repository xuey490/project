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

final class ORMFactory implements ModelFactoryInterface
{
    private ModelFactoryInterface $impl;

    /**
     * @param array  $config  数据库配置
     * @param string $ormType 'thinkORM' 或 'laravelORM'
     */
    public function __construct(array $config, string $ormType = 'thinkORM', ?LoggerInterface $logger = null)
    {
        switch ($ormType) {
            case 'laravelORM':
                $this->impl = new EloquentFactory($config, $logger);
                break;
            case 'thinkORM':
            default:
                $this->impl = new ThinkORMFactory($config, $logger);
                break;
        }
    }

    public function __invoke(string $modelClass): mixed
    {
        return $this->impl->__invoke($modelClass);
    }

    public function make(string $modelClass): mixed
    {
        return $this->impl->make($modelClass);
    }
}
