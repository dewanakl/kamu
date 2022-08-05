<?php

namespace Core;

use Closure;
use Exception;

/**
 * Class untuk routing dan mengelompokan url
 *
 * @class Router
 * @package Core
 */
class Router
{
    /**
     * Simpan semua routenya disini
     * 
     * @var array $routes
     */
    private array $routes = [];

    /**
     * Jika ada controller grup
     * 
     * @var string|null $controller
     */
    private $controller = null;

    /**
     * Jika ada prefix grup
     * 
     * @var string|null $prefix
     */
    private $prefix = null;

    /**
     * Untuk middleware group
     * 
     * @var array $middleware
     */
    private array $middleware = [];

    /**
     * Jadikan objek tunggal
     * 
     * @var Router $self
     */
    private static $self;

    /**
     * Buat objek yang tunggal
     *
     * @return Router
     */
    public static function self(): Router
    {
        if (!(self::$self instanceof Router)) {
            self::$self = new self;
        }

        return self::$self;
    }

    /**
     * Simpan urlnya
     *
     * @param string $method
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    private function add(string $method, string $path, array|string $action): Router
    {
        if (is_array($action)) {
            $controller = $action[0];
            $function = $action[1];
        } else {
            $controller = null;
            $function = $action;
        }

        $path = preg_replace('/{(\w+)}/', '([\w-]*)', $path);

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'function' => $function,
            'middleware' => [],
            'name' => null
        ];

        return $this;
    }

    /**
     * Simpan url route get
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public function get(string $path, array|string $action): Router
    {
        return $this->add('GET', $path, $action);
    }

    /**
     * Simpan url route post
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public function post(string $path, array|string $action): Router
    {
        return $this->add('POST', $path, $action);
    }

    /**
     * Simpan url route put
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public function put(string $path, array|string $action): Router
    {
        return $this->add('PUT', $path, $action);
    }

    /**
     * Simpan url route delete
     *
     * @param string $path
     * @param array|string $action
     * @return Router
     */
    public function delete(string $path, array|string $action): Router
    {
        return $this->add('DELETE', $path, $action);
    }

    /**
     * Tambahkan middleware dalam url route
     *
     * @param array|string $middlewares
     * @return Router
     */
    public function middleware(array|string $middlewares): Router
    {
        if (is_string($middlewares)) {
            $middlewares = array($middlewares);
        }

        $this->middleware = $middlewares;
        return $this;
    }

    /**
     * Tambahkan url lagi dalam route
     *
     * @param string $prefix
     * @return Router
     */
    public function prefix(string $prefix): Router
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Tambahkan controller dalam route
     *
     * @param string $name
     * @return Router
     */
    public function controller(string $name): Router
    {
        $this->controller = $name;
        return $this;
    }

    /**
     * Tambahkan nama url
     *
     * @param string $name
     * @return void
     */
    public function name(string $name): void
    {
        $id = count($this->routes) - 1;
        $this->routes[$id]['name'] = $name;
    }

    /**
     * Ambil url yang ada
     *
     * @return array
     */
    public function routes(): array
    {
        return $this->routes;
    }

    /**
     * Ambil url dalam route dengan nama
     *
     * @param string $name
     * @return string
     * 
     * @throws Exception
     */
    public function getPath(string $name): string
    {
        foreach ($this->routes as $route) {
            $id = array_search($route, $this->routes);
            if ($this->routes[$id]['name'] == $name) {
                return $this->routes[$id]['path'];
            }
        }

        throw new Exception('Route "' . $name . '" tidak ditemukan');
    }

    /**
     * Kelompokan routenya
     *
     * @param Closure $group
     * @return void
     */
    public function group(Closure $group): void
    {
        $tempController = $this->controller;
        $tempPrefix = $this->prefix;
        $tempMiddleware = $this->middleware;
        $tempRoutes = $this->routes;

        $this->controller = null;
        $this->prefix = null;
        $this->middleware = [];

        $group();

        foreach ($this->routes as $route) {
            if (!in_array($route, $tempRoutes)) {
                $id = array_search($route, $this->routes);

                if (!is_null($tempController)) {
                    $old = $this->routes[$id]['controller'];
                    $result = is_null($old) ? $tempController : $old;
                    $this->routes[$id]['controller'] = $result;
                }

                if (!is_null($tempPrefix)) {
                    $old = $this->routes[$id]['path'];
                    $prefix = preg_replace('/{(\w+)}/', '([\w-]*)', $tempPrefix);
                    $result = ($old != '/') ? $prefix . $old : $prefix;
                    $this->routes[$id]['path'] = $result;
                }

                if (!empty($tempMiddleware)) {
                    $old = $this->routes[$id]['middleware'];
                    $result = empty($this->middleware) ? $tempMiddleware : $this->middleware;
                    $this->routes[$id]['middleware'] = array_merge($result, $old);
                }
            }
        }

        $this->controller = null;
        $this->prefix = null;
        $this->middleware = [];
    }
}
