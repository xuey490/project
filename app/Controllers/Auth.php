<?php
namespace App\Controllers;

use App\Events\UserLoginEvent;
use App\Events\UserLoggedIn;
use Psr\EventDispatcher\EventDispatcherInterface; // 引入PSR标准接口 方式 A：依赖注入 (推荐 ✅)
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Auth
{
    // 通过构造函数自动注入分发器
    public function __construct(
        private EventDispatcherInterface $dispatcher //方式 A：依赖注入 (推荐 ✅)
    ) {}
	
    public function login(Request $request):Response
    {
		
		
        // 假设用户已验证成功
        $user = (object)['id' => 1, 'name' => 'Alice'];


        // 1. 获取事件分发器（从容器）
        $dispatcher = app(\Framework\Event\Dispatcher::class);

        // 2. 创建事件对象
        $event = new UserLoginEvent($user, $request->getClientIp() ?? '');

        // 3. 分发事件！
        $dispatcher->dispatch($event);  //该事件UserLoginEvent 触发的监视器

		dump( '<br/>------------------这是事件分割线------------------------<br/>');


		// 获取分发器
        $dispatcher = \Framework\Container\Container::getInstance()
            ->get(\Framework\Event\Dispatcher::class);
		
		// 创建事件
        $event = new \App\Events\UserLoggedIn(
            userId: 100,
            ip: $request->getClientIp(),
            userAgent: $request->headers->get('User-Agent') ?? '',
            request: $request
        );


        $dispatcher->dispatch( $event );

		
		
		return new Response('Login successfully');

		
		
    }
	
	//方式 A：依赖注入 (推荐 ✅)
    public function check(Request $request): Response
    {
		$dispatcher = app(\Framework\Event\Dispatcher::class);

        echo "正在处理登录逻辑...<br>";

        // 2. 实例化事件对象
        $event = new UserLoggedIn(
            userId: 100,
            ip: $request->getClientIp()?? '127.0.0.1',
            userAgent: $request->headers->get('User-Agent') ?? '',
            request: $request
		);

        // 3. ✅ 触发事件！
        // Dispatcher 会去查找所有关注 UserLoggedIn 的监听器（含注解注册的）并执行
        $dispatcher->dispatch($event);

        echo "登录流程结束。<br>";
		
		return new Response('Check successfully');
    }
	
	
    public function checkauth(Request $request): Response
    {
        $user = (object)['id' => 1, 'name' => 'Alice'];
        
        // 1. 创建事件
        $event = new UserLoginEvent(
			$user, 
			'192.168.1.1'		
		);

        // 2. ✅ 获取分发器并触发
        // 假设 app() 可以获取容器实例，并且你已经注册了 Dispatcher
        app(\Framework\Event\Dispatcher::class)->dispatch($event);
        
        // 或者如果你封装了全局函数 event()
        // event($event); 
		
		return new Response('checkauth successfully');
    }
	
}