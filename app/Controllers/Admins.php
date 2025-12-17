<?php
declare(strict_types=1);

namespace App\Controllers;

use Framework\Attributes\Auth;
use Framework\Attributes\Menu;
use Framework\Attributes\Log;
use Framework\Attributes\Role;
use Framework\Attributes\Cache;
use Framework\Basic\BaseJsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Framework\Attributes\Route;

/**
 * 示例后台控制器：展示 Attribute 与 DocBlock 两种方式 # [Auth(roles: ['admin'])] ,require 模式是true，如果设置false，则匿名访问
 *
 * 注意：
 * - 推荐使用 PHP8 Attribute（Auth / Menu） 
 * - 保留 DocBlock (@auth / @menu) 以兼容旧代码或脚本扫描@#auth true
 *
 * @#auth true
 * @#role super
 * 控制器级别示例：将整个控制器默认标记为后台菜单项（可选）
 */


#[Route(prefix: '/vvv1/admins', group: 'apssi', middleware: [\App\Middlewares\AuthMiddleware::class, \App\Middlewares\LogMiddleware::class])]
##[Auth(required: true, roles: ['admins'])] // 如开启，则整个页面需要认证，哪怕方法类没有进行设置
##[Menu(title: '系统管理', icon: 'cog', order: 100)]
class Admins
{
    /**
     * 公共列表页 —— 无需登录（默认不带 #[Auth]）
     *
     * DocBlock 说明示例（可选）：
     * @menu 列表页
     */
	##[Route(path: '/',   roles: ['admin'], methods: ['GET'], name: 'demoaa1.index')] //注解路由的auth roles
    /**
     * 旧 DocBlock 
     * 旧式的写法，role admin,super 用,隔开，不能用其他符号
     * @auth true
     * @role Super
     * @menu 内容管理
     */
	
    public function index(Request $request): Response
    {
        // 可以通过 $request->attributes->get('user') 读取经过中间件注入的用户信息（若有）
        return new Response(json_encode([
            'ok' => true,
            'action' => 'index',
            'note' => 'public index (no auth)'
        ]), 200, ['Content-Type' => 'application/json']);
    }

    // 场景1：普通数据接口，缓存 1 分钟
    #[Cache(ttl: 60)]
    public function getHotList(): JsonResponse
    {
        // 模拟耗时查询
        $data = ['item1', 'item2', 'item3', date('Y-m-d H:i:s')]; 
        return new JsonResponse($data);
    }

    // 场景2：指定 Key，适用于需要手动清除缓存的场景
    // 例如：后台更新了配置，你可以手动调用 app('cache')->delete('sys_config')
    #[Cache(ttl: 600, key: 'sys_config')]
    public function getConfig(): JsonResponse
    {
        return new JsonResponse(['site_name' => 'My FssPHP']);
    }


    /**
     * test
     *
     * DocBlock 风格：
     * @auth true
     * @role admin
     * @menu test首页
     */
	 
	 
    /**
     * 创建新产品 vvv1/admins/add?page=11 && /Admins/test
     * 
     * @method get
     * @path /add
     * @name products.storeaa
     * @auth true
     * @role admin,manager
     * @middleware App\Middlewares\AuthMiddleware, App\Middlewares\LogMiddleware
     * @menu 创建产品
     */
	#[Role(['admin'])] // 只有超级管理员能访问
	#[Log(description: '创建新产品', level: 'warning')]
    public function test(Request $request): Response
    {
        // 从 AuthMiddleware 注入的用户信息
        $user = $request->attributes->get('user', null);

        return new Response(json_encode([
            'ok' => true,
            'action' => 'index',
            'user' => $user,
            'message' => '欢迎访问后台首页',
        ]), 200, ['Content-Type' => 'application/json']);
    }



	#[Auth(roles: ['admin'])]
    public function testadmin(Request $request): Response
    {
        // 从 AuthMiddleware 注入的用户信息
        $user = $request->attributes->get('user', null);

        return new Response(json_encode([
            'ok' => true,
            'action' => 'index',
            'user' => $user,
            'message' => 'testadmin',
        ]), 200, ['Content-Type' => 'application/json']);
    }


    /**
     * 旧 DocBlock 注解示例 兼容写法
     * 旧式的写法，role admin,super 用,隔开，不能用其他符号
     * @auth true
     * @role Super
     * @menu 内容管理
     */
    public function contentManager(Request $request): Response
    {
        $user = $request->attributes->get('user', null);

		return BaseJsonResponse::success([
			'action' => 'contentManager',
			'user'   => $user
		], '内容管理页面');
    }



    /**
     * 后台管理入口
     * @auth true
     */
	#[Auth(required: true, roles: ['admin'])]
	public function legacyAdmin(Request $request): Response
	{
		$user = $request->attributes->get('user', null);

		return BaseJsonResponse::success([
			'action' => 'legacyAdmin',
			'user'   => $user
		], '后台管理页面');
	}

    /**
     * 方法级 Attribute（推荐）
     * - 需要登录
     * - 只允许 roles = ['admin', 'super']
     * - 注册为菜单项：用户管理
     */
    #[Auth(roles: ['test','super'], required: true)]
    #[Menu(title: '用户管理', icon: 'users', order: 90)]
    public function userList(Request $request): Response
    {
        $user = $request->attributes->get('user', null);
        return new Response(json_encode([
            'ok' => true,
            'action' => 'userList',
            'user' => $user,
        ]), 200, ['Content-Type' => 'application/json']);
    }

    /**
     * 覆盖类级别或路由级别的 Auth 配置：允许匿名访问（required: false）
     *
     * 这里用 Attribute 显式标明不需要认证，示范 method-level 覆盖
     */
    #[Auth(required: false)]
    public function publicInfo(Request $request): Response
    {
        return new Response(json_encode([
            'ok' => true,
            'action' => 'publicInfo',
            'note' => 'explicitly public by #[Auth(required:false)]',
        ]), 200, ['Content-Type' => 'application/json']);
    }
	
	
	
    /**
     * 方法级覆盖：关闭认证
     * @auth false
     */	
    public function publicData(Request $request): Response
    {
        return new Response(json_encode([
            'ok' => true,
            'action' => 'publicInfo',
            'note' => 'explicitly public by #[Auth(required:false)]',
        ]), 200, ['Content-Type' => 'application/json']);
    }	
}
