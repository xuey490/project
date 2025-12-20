<?php

namespace App\Events;

use Framework\Event\EventInterface;
use Symfony\Component\HttpFoundation\Request;

class UserLoggedIn implements EventInterface
{
    public function __construct(
        public readonly int $userId,
        public readonly string $ip,
        public readonly ?string $userAgent,
        public readonly ?Request $request
    ) {
    }
}