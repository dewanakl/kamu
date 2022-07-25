<?php

/**
 * Daftarkan file class secara otomatis
 * agar tidak ribet
 */

spl_autoload_register(function (string $name) {
    $name = str_replace('\\', '/', $name);
    $classPath = dirname(__DIR__) . '/' . lcfirst($name) . '.php';

    if (!file_exists($classPath)) {
        throw new Exception('Class: ' . $name . ' tidak ada !');
    }

    require_once $classPath;
});
