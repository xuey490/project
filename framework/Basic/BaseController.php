<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: BaseController.php
 * @Date: 2025-12-10
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Basic;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Framework\Core\App;
use Framework\Basic\Traits\CrudActionTrait;
use Framework\Basic\Traits\CrudFilterTrait;
use Framework\Basic\Traits\CrudFormatterTrait;
use Framework\Basic\Traits\CrudQueryTrait;
use Framework\Database\DatabaseFactory;
use Framework\DI\Injectable;
use Symfony\Component\HttpFoundation\Request;

/**
 * BaseController - 控制器基类
 *
 * 提供控制器通用功能：
 * - 依赖注入
 * - 服务自动实例化
 * - JSON 响应封装
 * - CRUD 操作 trait
 * - 请求参数获取与过滤
 *
 * 子类使用方式：
 * 1. 定义 $serviceClass 属性指定业务服务类
 * 2. 定义 $daoClass 属性指定数据访问对象（无 Service 时使用 GenericService）
 * 3. 定义 $validatorClass 属性指定验证器类
 *
 * @package Framework\Basic
 */
abstract class BaseController
{
    use CrudActionTrait;
    use CrudFilterTrait;
    use CrudFormatterTrait;
    use CrudQueryTrait;
    use CrudActionTrait;
    use Injectable;

    /**
     * 当前请求对象
     * @var Request
     */
    protected Request $request;

    /**
     * 数据库工厂实例
     * @var DatabaseFactory
     */
    protected DatabaseFactory $db;

    /**
     * 业务服务实例
     * @var object
     */
    protected object $service;

    /**
     * 业务服务类名
     * 子类需定义此属性指定具体服务类
     * @var string
     */
    protected string $serviceClass = '';

    /**
     * 数据访问对象类名
     * 当未定义 serviceClass 时，使用 GenericService 包装此 DAO
     * @var string
     */
    protected string $daoClass = '';

    /**
     * 验证器类名
     * @var string
     */
    protected string $validatorClass = '';

    /**
     * 验证器实例
     * @var object|null
     */
    protected ?object $validator = null;

    /**
     * 依赖注入容器
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * 构造函数
     *
     * 自动完成依赖注入、服务实例化和初始化钩子调用。
     */
    public function __construct()
    {
        $this->inject();

        $this->container = app();

        // 1. 获取全局 Request
        $this->request = app('request');

        // 2. 获取全局 DB
        $this->db = app('db');

        // 3. 自动初始化 Service
        // 场景1：定义了具体的业务 Service (如 ProductService)
        if (!empty($this->serviceClass)) {
            $this->service = App()->make($this->serviceClass);
        }
        // 场景2：只定义了 DAO，没有定义 Service -> 使用通用 Service 包装 DAO
        elseif (!empty($this->daoClass)) {
            // 实例化通用服务
            $genericService = App()->make(\Framework\Basic\GenericService::class);

            // 实例化 DAO
            $dao = App()->make($this->daoClass);

            // 手动注入 DAO 到通用服务中
            if (method_exists($genericService, 'setDao')) {
                $genericService->setDao($dao);
            }

            $this->service = $genericService;
        }

        // 4. 初始化验证器
        if (!empty($this->validatorClass)) {
            $this->validator = App()->make($this->validatorClass);
        }

        // 5. 钩子
        $this->initialize();
    }

    /**
     * 子类初始化钩子
     *
     * 子类可根据需要覆盖此方法进行初始化操作。
     *
     * @return void
     */
    protected function initialize(): void
    {
    }

    /**
     * 返回成功 JSON 响应
     *
     * @param mixed $data 响应数据
     * @param string $msg 响应消息
     * @return BaseJsonResponse JSON 响应对象
     */
    protected function success(mixed $data = [], string $msg = 'success'): BaseJsonResponse
    {
        return BaseJsonResponse::success($data, $msg);
    }

    /**
     * 返回失败 JSON 响应
     *
     * @param string $msg 错误消息
     * @param int $code 业务错误码
     * @return BaseJsonResponse JSON 响应对象
     */
    protected function fail(string $msg = 'error', int $code = 1): BaseJsonResponse
    {
        return BaseJsonResponse::fail($msg, [], $code);
    }

    /**
     * 返回服务器错误 JSON 响应
     *
     * @param string $msg 错误消息
     * @return BaseJsonResponse JSON 响应对象
     */
    protected function error(string $msg = 'server error'): BaseJsonResponse
    {
        return BaseJsonResponse::error($msg, 500);
    }

    /**
     * 获取请求参数
     *
     * 优先从 GET 参数获取，其次从 POST 参数获取。
     * 支持可选的 XSS 过滤。
     *
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @param bool $filter 是否开启 XSS 过滤（默认开启）
     * @return mixed 参数值
     */
    protected function input(string $key, mixed $default = null, bool $filter = true): mixed
    {
        // 1. 优先从 Query (GET) 获取，其次从 Request (POST) 获取
        $value = $this->request->query->get($key);
        if ($value === null) {
            $value = $this->request->request->get($key, $default);
        }

        // 2. 如果开启过滤，且值是字符串，则进行转义
        if ($filter && is_string($value)) {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }
}
