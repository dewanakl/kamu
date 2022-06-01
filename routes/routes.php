<?php

use Controllers\WelcomeController;
use Core\Route;

/**
 * Make something great with this app
 * keep simple yahh
 */

Route::get('/', [WelcomeController::class, 'index']);
