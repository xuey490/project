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

namespace Framework\Basic;

use Framework\ORM\Trait\ServicesTrait;
use Illuminate\Support\Facades\DB as LaravelDb;
use think\facade\DB as ThinkDb;

/**
 * @method getModel()
 */
abstract class BaseService
{

    use ServicesTrait;

    /**
     * 模型注入
     */
    protected ?BaseDao $dao;

    /**
     * 执行指定框架的事务
     *
     * @param callable    $closure
     * @param bool        $isTran 是否启用事务
     * @param string|null $framework
     *
     * @return mixed
     */
    public function transaction(callable $closure, bool $isTran = true, ?string $framework = null): mixed
    {
        $framework = $framework ?? config('database.engine' , 'thinkORM'); // 默认使用 'thinkORM'
        return match ($framework) {
            'thinkORM' => $isTran ? $this->runThinkPhpTransaction($closure) : $closure(),
            'laravelORM' => $isTran ? $this->runLaravelTransaction($closure) : $closure(),
            default => throw new \InvalidArgumentException("Unsupported framework: $framework"),
        };
    }

    /**
     * 数据库事务操作
     *
     * @param callable $closure
     * @param bool     $isTran
     *
     * @return mixed
     */
    public function runThinkPhpTransaction(callable $closure, bool $isTran = true): mixed
    {
        return $isTran ? ThinkDB::transaction($closure) : $closure();
    }

    /**
     * 执行 Laravel 事务
     *
     * @param callable $closure
     * @param bool     $isTran
     *
     * @return mixed
     */
    private function runLaravelTransaction(callable $closure, bool $isTran = true): mixed
    {
        return $isTran ? LaravelDb::transaction($closure) : $closure();
    }


    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->dao, $name], $arguments);
    }
}