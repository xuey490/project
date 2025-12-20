<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Events\UserLoginEvent;
use Framework\Event\Attribute\EventListener;

class UserAttributeListener
{
    // 方式 1: 显式指定事件和优先级
    #[EventListener(event: UserLoginEvent::class, priority: 999)]
    public function onUserLogin(UserLoginEvent $event): void
	//public function onUserLogin(UserLoginEvent $event): void
    {
		
        echo "🚀 UserLoginEvent {$event->user->name}注解监听器触发! Priority 999<br>";
    }

    // 方式 2: 自动推断事件类型 (推荐)
    #[EventListener(priority: 1000)]
    public function sendWelcomeEmail(UserLoggedIn $event): void
    {
        echo "📧 UserLoggedIn 发送邮件... 1000<br>";
    }
}