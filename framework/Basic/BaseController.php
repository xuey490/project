<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: ControllersProvider.php
 * @Date: 2025-12-10
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */


namespace Framework\Basic;

use Symfony\Component\HttpFoundation\Request;
use Framework\Utils\Json;
use Framework\Basic\BaseService;
use Framework\Basic\Traits\CrudQueryTrait;
use Framework\Basic\Traits\CrudFilterTrait;
use Framework\Basic\Traits\CrudFormatterTrait;
use Framework\Basic\Traits\CrudActionTrait;
use Framework\Database\DatabaseFactory; 


abstract class BaseController
{
    use CrudQueryTrait;
    use CrudFilterTrait;
    use CrudFormatterTrait;
    use CrudActionTrait;

    protected Request $request;
    
    // 这里的类型最好稍微宽泛一点或者确定的接口，
    // 如果你有 BaseService 接口最好，没有的话用 object 也可以，但建议用 BaseService
    protected object $service; 
    
    // 新增：数据库工厂，设为 protected 供子类使用
    protected DatabaseFactory $db;

    protected ?object $validator = null;
    
    protected string $serviceClass = '';

    public function __construct(
        Request $request,
        DatabaseFactory $db,           // 1. 必传参数：DB工厂
        ?BaseService $service = null,  // 2. 可选参数：Service (放到最后)
        ?object $validator = null      // 3. 可选参数：验证器
    ) {
        $this->request = $request;
        $this->db      = $db;          // 保存 DB 实例
        $this->validator = $validator;

        // Service 的初始化逻辑
        if ($service !== null) {
            $this->service = $service;
        } elseif (!empty($this->serviceClass)) {
            $this->service = app()->make($this->serviceClass);
        } else {
            // 如果你的控制器只是简单的展示页面，不需要 Service，也可以不抛异常，视具体需求而定
            throw new \RuntimeException(static::class . ' 未指定 $serviceClass');
        }


        $this->initialize();
    }

    /**
     * 子类可根据需要覆盖 lifecycle
     */
    protected function initialize(): void
    {
    }


    /**
     * 返回成功 JSON
     */
    protected function success(mixed $data = [], string $msg = 'success'): BaseJsonResponse
    {
        return BaseJsonResponse::success($data, $msg);
    }

    /**
     * 返回失败 JSON
     */
    protected function fail(string $msg = 'error', int $code = 1): BaseJsonResponse
    {
        return BaseJsonResponse::fail($msg, $code);
    }

    /**
     * 服务端错误
     */
    protected function error(string $msg = 'server error'): BaseJsonResponse
    {
        return BaseJsonResponse::error($msg, 500);
    }


    /**
     * 获取请求参数，并支持可选的 XSS 过滤
     * 
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @param bool $filter 是否开启 XSS 过滤（默认开启）
     * @return mixed
     */
    protected function input(string $key, mixed $default = null, bool $filter = true): mixed
    {
        // 1. 优先从 Query (GET) 获取，其次从 Request (POST) 获取
        // 你也可以根据需要调整优先级
        $value = $this->request->query->get($key);
        if ($value === null) {
            $value = $this->request->request->get($key, $default);
        }

        // 2. 如果开启过滤，且值是字符串，则进行转义
        if ($filter && is_string($value)) {
            // strip_tags 用于去除 HTML 标签（彻底删掉 <script>）
            // 或者使用 htmlspecialchars 转义（保留符号但使其失效）
            
            // 方式 A：彻底移除标签（适合昵称、标题等纯文本）
            // $value = strip_tags($value); 
            
            // 方式 B：转义实体（适合不想丢失数据，但想安全显示的场景）
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }
	
}
