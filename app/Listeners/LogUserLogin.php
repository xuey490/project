<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Events\UserLoginEvent;

use Framework\Event\Attribute\EventListener;

class LogUserLogin
{

    // 4️⃣ 直接在方法上加注解
    // 对应原来的 UserLoggedIn::class => ['method' => 'handleLoggedIn', 'priority' => 100]
    #[EventListener(priority: 100)]
    public function handleLoggedIn(UserLoggedIn $event): void
    {
        dump( "✅ [用户日志事件注解版] handleLoggedIn triggered User: {$event->userId}\r\n<br>");
        app('log')->info("用户日志: ID={$event->userId}, IP={$event->ip} ");
    }
    
    // 对应原来的 UserLoginEvent::class => ['method' => 'handleUserLogin', 'priority' => 200]
    // 也可以显式指定事件类：#[EventListener(event: UserLoginEvent::class, priority: 200)]
    #[EventListener(priority: 200)]
    public function handleUserLogin(UserLoginEvent $event): void
    {
        dump( "✅ [用户登录事件注解版] handleUserLogin triggered User: {$event->user->id}，权重：200\r\n<br>");
        app('log')->info("用户登录: ID={$event->user->id}, IP={$event->ip}");
    }
	
}