<?php

/**
 * Kamu PHP Framework
 * for educational purposes
 * 
 * @author dewanakl
 * @see https://github.com/dewanakl/Kamu
 */

require_once 'env.php';

define('HTTPS', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://');
define('BASEURL', @$_ENV['BASEURL'] ? rtrim($_ENV['BASEURL'], '/') : HTTPS . $_SERVER['HTTP_HOST']);
define('DEBUG', (@$_ENV['DEBUG'] == 'true') ? true : false);

error_reporting(DEBUG ? E_ALL : 0);
date_default_timezone_set(@$_ENV['TIMEZONE'] ?? 'Asia/Jakarta');

session_name(@$_ENV['APP_NAME'] ?? 'Kamu');
session_set_cookie_params([
    'lifetime' => intval(@$_ENV['COOKIE_LIFETIME'] ?? 86400),
    'path' => '/',
    'secure' => (HTTPS == 'https://') ? true : false,
    'httponly' => true,
    'samesite' => 'strict',
]);

require_once 'bootstrap.php';
require_once __DIR__ . '/../routes/routes.php';
require_once __DIR__ . '/../helpers/helpers.php';

set_exception_handler(fn ($error) => show('errors/trace', [
    'error' => $error
]));
