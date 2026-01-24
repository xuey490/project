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
use Framework\Basic\BaseDao;
use Framework\DI\Injectable;

/**
 * 泛型 BaseService，支持子类指定具体 DAO 类型
 * @template T of BaseDao
 * @method getModel()
 */
abstract class BaseService
{

    use ServicesTrait;

    // 引入注入能力
    use Injectable;	
	
    /**
     * 模型注入：使用泛型类型
     * @var ?T
     */
    protected ?BaseDao $dao = null;

    // 构造函数不接受参数，完全由内部解决
    public function __construct()
    {
//$db = app('db');
		$this->inject();
		$this->initialize();
	}
	
    /**
     * 子类可根据需要覆盖 lifecycle
     */
    protected function initialize(): void
    {
    }

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
        if (!$isTran) {
            return $closure();
        }

        $framework = $framework ?? config('database.engine', 'thinkORM');

        return match ($framework) {
            'thinkORM'   => $this->thinkOrmTransaction($closure),
            'laravelORM' => $this->laravelOrmTransaction($closure),
            default      => throw new \InvalidArgumentException("Unsupported framework: {$framework}"),
        };
    }


    /**
     * ThinkORM 事务
     */
    protected function thinkOrmTransaction(callable $closure): mixed
    {
        return ThinkDb::transaction(function () use ($closure) {
            return $closure();
        });
    }

    /**
     * Laravel ORM 事务
     */
    protected function laravelOrmTransaction(callable $closure): mixed
    {
        return LaravelDb::transaction(function () use ($closure) {
            return $closure();
        });
    }

    public function setDao(BaseDao $dao): void
    {
        $this->dao = $dao;
    }

    /** 代理 DAO 调用
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!$this->dao) {
            throw new \RuntimeException("BaseService: DAO not initialized in service.");
        }

		// 直接尝试调用 DAO，让 BaseDao::__call() 处理代理到 ORM Adapter
		try {
			// 支持返回引用调用语法，也支持 PHP 8 call
			return $this->dao->{$name}(...$arguments);
		} catch (\BadMethodCallException $e) {
			// DAO 及其适配器都不支持该方法
			throw new \BadMethodCallException(
				"BaseService: Method {$name} not found in DAO adapter (" . get_class($this->dao) . " / adapter: " . get_class($this->dao->instance) . "): " . $e->getMessage()
			);
		} catch (\Throwable $e) {
			// 其它异常（例如 ORM 内部抛出的），直接向上抛或包装
			throw $e;
		}
		
		//最后兜底
		return call_user_func_array([$this->dao, $name], $arguments);
        //return $this->dao->$name(...$arguments);
		
    }
	
	
	
	
    /**
     * 规范化分页参数（从数组/请求中获取）
     *
     * 返回 [page, limit, offset]
     *
     * @param array|null $params 可为 null 或 [ 'page'=>..., 'limit'=>... ] 或 [page,limit]
     * @param int $defaultLimit 默认 limit
     */
    protected function PageParams(?array $params = null, int $defaultLimit = 10): array
    {
        $page = 1;
        $limit = $defaultLimit;

        if ($params === null) {
            return [$page, $limit, 0];
        }

        // 支持关联数组或索引数组
        if (array_is_list($params)) {
            $page = (int)($params[0] ?? 1);
            $limit = (int)($params[1] ?? $defaultLimit);
        } else {
            $page = (int)($params['page'] ?? $params['p'] ?? 1);
            $limit = (int)($params['limit'] ?? $params['per_page'] ?? $defaultLimit);
        }

        $page = max(1, $page);
        $limit = max(1, $limit);

        $offset = ($page - 1) * $limit;
        return [$page, $limit, $offset];
    }
	
}