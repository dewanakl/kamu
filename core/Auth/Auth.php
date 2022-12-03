<?php

namespace Core\Auth;

use Core\Facades\App;

/**
 * Helper class Autentikasi
 * 
 * TODO : isi ini
 * @method static bool check()
 * @see \Core\Auth\AuthManager
 *
 * @class Auth
 * @package Core\Auth
 */
final class Auth
{
    /**
     * Eksekusi method pada AuthManager
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    private static function call(string $method, array $parameters): mixed
    {
        return App::get()->invoke(AuthManager::class, $method, $parameters);
    }

    /**
     * Panggil method secara static
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return self::call($method, $parameters);
    }

    /**
     * Panggil method secara object
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return self::call($method, $parameters);
    }
}
