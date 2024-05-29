<?php

use App\Controllers\WelcomeController;
use Core\Routing\Route;

/**
 * Tadi itu web, nah yang ini khusus untuk api.
 * keep simple yeah.
 */

Route::get('/', WelcomeController::class);
