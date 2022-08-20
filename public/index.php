<?php

define('START_TIME', microtime(true));

require_once __DIR__ . '/../app/Kernel.php';

/** 
 * Create kernel this web application then
 * Make service object
 * Run route in service
 * 
 * it's simple
 */

Kernel::web()
    ->make(\Core\Facades\Service::class)
    ->run(\Core\Routing\Route::router());
