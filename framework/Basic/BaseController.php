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
use Framework\DI\Injectable;

abstract class BaseController
{
    use CrudQueryTrait;
    use CrudFilterTrait;
    use CrudFormatterTrait;
    use CrudActionTrait;
    // 引入注入能力
    use Injectable;
    protected Request $request;
	
    protected DatabaseFactory $db;
	
    protected object $service;
    
    // 子类只需要定义这个属性
    protected string $serviceClass = '';
	
	protected ?object $validator = null;

    // 构造函数不接受参数，完全由内部解决
    public function __construct()
    {
		$this->inject();
        // 1. 获取全局 Request
        $this->request = app('request'); 
        
        // 2. 获取全局 DB
        $this->db = app('db'); 
        
        // 3. 【自动初始化 Service】
        // 只有子类定义了 serviceClass 父类自动帮你实例化，没定义就算了，说明这个控制器不需要通用CRUD
        if (!empty($this->serviceClass)) {
            $this->service = app()->make($this->serviceClass);
        }
		
        // 4. 钩子
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
