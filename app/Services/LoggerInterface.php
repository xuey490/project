<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Services;

interface LoggerInterface 
{
    public function log(string $msg);
}