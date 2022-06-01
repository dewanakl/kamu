<?php

use Core\App;
use Core\Application;
use Core\Request;
use Core\Session;
use Core\Route;

require_once __DIR__ . '/../app/app.php';

/** 
 * Create container this application then
 * Make request and session object
 * Run in route
 * 
 * it's simple
 */

$app = App::new(new Application());

$app->make(Request::class);
$app->make(Session::class);

Route::run($app);
