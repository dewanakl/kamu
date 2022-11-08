<?php

define('START_TIME', microtime(true));

require_once __DIR__ . '/../app/Kernel.php';

/** 
 * Create this web application kernel then
 * Create a service object and run it
 * 
 * it's simple
 */

Kernel::web()
    ->make(\Core\Facades\Service::class)
    ->run();
