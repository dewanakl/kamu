<?php

/**
 * Daftarkan file class secara otomatis
 * agar tidak ribet
 */

spl_autoload_register(function (string $name) {
    $name = str_replace('\\', '/', $name);
    $classPath = dirname(__DIR__) . '/' . lcfirst($name) . '.php';

    if (file_exists($classPath) && is_readable($classPath)) {
        require_once $classPath;
    } else {
        throw new Exception('Class: ' . $name . ' tidak ada !');
    }
});
