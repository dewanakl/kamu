<?php

namespace Core\Routing;

/**
 * Helper class untuk routing url
 *
 * @class Route
 * @package Core\Routing
 */
final class Route
{
    /**
     * Simpan url route get
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public static function get(string $path, array|string $action): Router
    {
        return static::router()->get($path, $action);
    }

    /**
     * Simpan url route post
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public static function post(string $path, array|string $action): Router
    {
        return static::router()->post($path, $action);
    }

    /**
     * Simpan url route put
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public static function put(string $path, array|string $action): Router
    {
        return static::router()->put($path, $action);
    }

    /**
     * Simpan url route delete
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public static function delete(string $path, array|string $action): Router
    {
        return static::router()->delete($path, $action);
    }

    /**
     * Tambahkan middleware dalam url route
     *
     * @param array|string $middlewares
     * @return Router
     */
    public static function middleware(array|string $middlewares): Router
    {
        return static::router()->middleware($middlewares);
    }

    /**
     * Tambahkan url lagi dalam route
     *
     * @param string $prefix
     * @return Router
     */
    public static function prefix(string $prefix): Router
    {
        return static::router()->prefix($prefix);
    }

    /**
     * Tambahkan controller dalam route
     *
     * @param string $name
     * @return Router
     */
    public static function controller(string $name): Router
    {
        return static::router()->controller($name);
    }

    /**
     * Ambil url dalam route dengan nama
     *
     * @param string $name
     * @return string
     */
    public static function getPath(string $name): string
    {
        return static::router()->getPath($name);
    }

    /**
     * Ambil objek router
     *
     * @return Router
     */
    public static function router(): Router
    {
        return app(Router::class);
    }
}
