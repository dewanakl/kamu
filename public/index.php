<?php

use Core\App;
use Core\Application;
use Core\Request;
use Core\Session;
use Core\Respond;
use Core\Route;
use Core\Service;

define('startTime', microtime(true));

require_once __DIR__ . '/../app/app.php';

/** 
 * Create container this application then
 * Make request, session, and respond object
 * Run route in service object
 * 
 * it's simple
 */

$app = App::new(new Application());

$app->make(Request::class);
$app->make(Session::class);
$app->make(Respond::class);

$app->make(Service::class)->run(Route::router());
