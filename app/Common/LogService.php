<?php

declare(strict_types=1);

// App/Services/LogService.php
namespace App\Common;

class LogService {
    public function info(string $msg) {
        echo "[LOG]: $msg <br>";
    }
}