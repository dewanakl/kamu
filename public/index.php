<?php

use Core\Facades\App;
use Core\Facades\Application;
use Core\Facades\Service;
use Core\Routing\Route;

define('START_TIME', microtime(true));

require_once __DIR__ . '/../app/app.php';

/** 
 * Create container this application then
 * Make service object
 * Run route in service object
 * 
 * it's simple
 */

$app = App::new(new Application());

$service = $app->make(Service::class);
$service->run(Route::router());
