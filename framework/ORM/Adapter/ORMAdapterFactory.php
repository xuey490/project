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

namespace Framework\ORM\Adapter;

use Framework\Core\App;
use Framework\ORM\Exception\Exception;
use Framework\ORM\Factories\LaravelORMFactory;
use Framework\ORM\Factories\ThinkphpORMFactory;

class ORMAdapterFactory
{
    /**
     * @param  string              $mode  模式 (thinkORM / laravelORM)
     * @param  mixed               $model 模型类名或实例
     * @return ORMAdapterInterface
     */
    public static function createAdapter(string $mode, mixed $model = null): mixed
    {
        // 对应 ThinkphpORMFactory 构造函数的参数名
        $params = ['model' => $model];
		#dump($mode);
        return match ($mode) {
            'thinkORM'   => App::make(ThinkphpORMFactory::class, $params),
            'laravelORM' => App::make(LaravelORMFactory::class, $params),
            default      => throw new Exception('Invalid ORM type: ' . $mode),
        };
    }
}
