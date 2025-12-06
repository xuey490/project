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

//use think\Container;
use think\facade\Db;
use think\Model;

/**
 * ThinkORM 模型工厂实现类
 * 使用 __invoke 自动实例化模型.
 */
class ThinkORMFactory implements DatabaseInterface
{

    public function __construct(
        protected array $config,
		protected ?object $logger = null,
        //protected ?LoggerInterface $logger = null
    ) {
        $this->boot();
    }

    protected function boot(): void
    {
        // 1. 初始化配置
        // 注意：ThinkORM 最好使用 setConfig 进行全局静态初始化
        Db::setConfig($this->config);

        // 2. 日志监听 (仅当传入 logger 且配置开启 debug 时)
        // 建议在 config 数组中增加一个 'debug' => true 的字段来控制
        $isDebug = $this->config['connections'][$this->config['default']]['debug'] ?? false;

        if ($this->logger && $isDebug) {
            Db::listen(function ($sql, $time, $explain) {
                try {
                    $this->logger->debug('[ThinkORM Info]', [
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

		// 兼容传入表名的情况，例如 make('user') -> Db::name('user')
        // 如果不是模型类，则返回 Db 查询构造器（兼容表名字符串）
        return Db::name($modelClass);
    }
}
