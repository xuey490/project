<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Services;

class FileLogger implements LoggerInterface 
{
    public function log(string $msg) { echo "[Log]: $msg" . PHP_EOL; }
}