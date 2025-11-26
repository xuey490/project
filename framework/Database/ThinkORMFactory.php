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

use think\Container;
use think\facade\Db;
use think\Model;


/**
 * ThinkORM 模型工厂实现类
 * 使用 __invoke 自动实例化模型.
 */
class ThinkORMFactory implements ModelFactoryInterface
{
    /**
     * @var array 全局数据库连接配置（可选）
     */
    protected array $config;

    protected ?object $connection = null;

    /**
     * 构造函数接收数据库配置（可选）.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        /*
        // 尝试安全地设置/连接数据库（兼容不同 ThinkPHP 版本）
        try {
            if (!empty($this->config)) {
                // setConfig 只设置配置，connect 返回连接对象（若可用）
                $db = Db::setConfig($this->config);
                // 尝试 connect（若不存在则忽略异常）
                try {
                    $this->connection = Db::connect();
                } catch (Throwable $e) {
                    // 有些版本 Db::connect() 可能不存在或返回 void，忽略
                    $this->connection = null;
                }
            } else {
                // 没有配置时也允许默认连接
                try {
                    $this->connection = Db::connect();
                } catch (Throwable $_) {
                    $this->connection = null;
                }
            }
        } catch (Throwable $e) {
            // 记录但不抛出，避免构造时中断服务注册
            app('log')->error('DB init failed in ThinkORMFactory: ' . $e->getMessage());
            $this->connection = null;
        }


        // 获取 ThinkPHP 容器（单例）
        $container = Container::getInstance();

        $container->bind('model', fn() => $db);
        */


        // ✅ 核心：设置静态配置（ThinkORM 4.0 必须！）
        $db = Db::setConfig($config);

        // 可选：绑定到容器（供其他地方通过 Container::get('db') 使用）
        $container = Container::getInstance();
        // 注意：这里获取的是 Manager 实例，不是自己 new
        $container->bind('db', fn () => Db::getManager());

        // 开发环境开启 SQL 日志
        $appDebug = false;
        if (function_exists('app')) {
            try {
                /** @noinspection PhpUndefinedFunctionInspection */
                $cfg = app('config');
                if ($cfg !== null && method_exists($cfg, 'get')) {
                    $appDebug = (bool) $cfg->get('app.debug');
                }
            } catch (\Throwable $_) {
                $appDebug = false;
            }
        }

        if ($appDebug) {
            Db::listen(static function ($sql, $time, $explain): void {
                try {
                    app('log')->debug('[ThinkORM Info]', [
                        'sql'     => $sql,
                        'time'    => (string) $time . 's',
                        'explain' => $explain ?? [],
                    ]);
                } catch (\Throwable $_) {
                    // 忽略记录过程中的异常
                }
            });
        }
    }

    /**
     * 通过 __invoke 实现函数式调用
     * 示例: $factory('App\Model\User').
     *
     * @return BaseQuery|Model
     */
    /**
     * 支持函数式调用：($factory)('App\\Model\\User') 等价于 make().
     *
     * @return BaseQuery|Model
     */
    public function __invoke(string $modelClass): mixed
    {
        return $this->make($modelClass);
    }

    /**
     * 创建模型实例或查询构造器.
     *
     * @param  string          $modelClass 模型类名（完整命名空间）
     * @return BaseQuery|Model
     */
    public function make(string $modelClass): mixed
    {
        // 判断是否为有效模型类
        if (class_exists($modelClass) && is_subclass_of($modelClass, Model::class)) {
            return new $modelClass();
        }

        // 如果不是模型类，则返回 Db 查询构造器（兼容表名字符串）
        return Db::name($modelClass);
    }
}
