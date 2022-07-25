<?php

use Core\App;
use Core\Application;
use Core\Route;
use Core\Service;

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
