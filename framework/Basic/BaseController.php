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

use Symfony\Component\HttpFoundation\Request;
use Framework\Basic\BaseService;

/**
 * 控制器基础类（最终版）
 */
abstract class BaseController
{
    /**
     * 当前请求
     */
    protected Request $request;

    /**
     * 业务服务类
     */
    protected ?BaseService $service;

    /**
     * 验证器
     */
    protected ?object $validate;

    /**
     * 控制器中间件
     */
    protected array $middleware = [];

    /**
     * 需要授权的接口地址
     */
    protected array $authRule = [];

    /**
     * 构造方法
     *
     * 可在应用控制器中自由扩展
     */
    public function __construct(
		//Request $request,
        ?BaseService $service = null,
        ?object $validate = null
    ) {
		//$this->request = $request;
        $this->service  = $service;
        $this->validate = $validate;
    }
	
	
    /**
     * 初始化处理（需由子类实现）
     */
    //abstract protected function initialize(): void;
	
}
