<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Framework\Event\Attribute\EventListener;

class SendWelcomeEmail
{
	
	#[EventListener(priority: 101)]
    public function onUserRegistered(UserLoggedIn $event): void
    {

		dump( "[EMAIL] 已向用户 {$event->userId} 发送欢迎邮件。： 101\n<br>");
    }
	
	#[EventListener(priority: 102)]
    public function handleUserLogin(UserLoggedIn $event): void
    {

		dump( "EventListener handleUserLogin succesfully ： 102<br>");
    }	
	
	
}
