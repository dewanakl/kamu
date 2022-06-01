<?php

namespace Core;

use Closure;
use Middleware\CorsMiddleware;
use Middleware\CsrftokenMiddleware;

class Route
{
    private static array $routes = array();
    private static string $urinow;

    private static function add(string $method, string $path, array|string $action, array $middlewares): void
    {
        if (is_array($action)) {
            $controller = $action[0];
            $function = $action[1];
        } else {
            $controller = null;
            $function = $action;
        }

        $path = preg_replace('/{(\w+)}/', '([a-z0-9_]+(?:-[a-z0-9]+)*)', $path);

        array_push(self::$routes, [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'function' => $function,
            'middleware' => $middlewares
        ]);
    }

    private static function routeNow(): array
    {
        return self::$routes;
    }

    private static function invokeMiddleware(Application $app, array $route): void
    {
        $middlewarePool = [];

        $middlewarePool[] = $app->singleton(CorsMiddleware::class);
        $middlewarePool[] = $app->singleton(CsrftokenMiddleware::class);

        foreach ($route['middleware'] as $middleware) {
            $middlewarePool[] = $app->singleton($middleware);
        }

        $app->singleton(Middleware::class)->layer($middlewarePool)
            ->handle($app->singleton(Request::class), fn ($request) => $request);
    }

    private static function invokeController(Application $app, array $route, array $variables): void
    {
        $controller = $route['controller'];
        $method = $route['function'];
        array_shift($variables);

        $result = $app->invoke($controller, $method, $variables);

        if (is_string($result) || $result instanceof Render) {
            $app->singleton(Session::class)->set('oldRoute', self::$urinow);
            $app->singleton(Session::class)->unset('old');
            $app->singleton(Session::class)->unset('error');

            echo $result;
        }

        exit;
    }

    public static function get(string $path, array|string $action, array $middlewares = []): void
    {
        static::add('GET', $path, $action, $middlewares);
    }

    public static function post(string $path, array|string $action, array $middlewares = []): void
    {
        static::add('POST', $path, $action, $middlewares);
    }

    public static function middleware(array|string $middlewares, Closure $next): void
    {
        if (!is_array($middlewares)) {
            $middlewares = array($middlewares);
        }

        $routes = static::routeNow();
        $next();

        foreach (static::routeNow() as $route) {
            if (!in_array($route, $routes)) {
                $id = array_search($route, self::$routes);
                foreach (array_reverse($middlewares) as $middleware) {
                    array_unshift(self::$routes[$id]['middleware'], $middleware);
                }
            }
        }
    }

    public static function prefix(string $prefix, Closure $next): void
    {
        $routes = static::routeNow();
        $next();

        foreach (static::routeNow() as $route) {
            if (!in_array($route, $routes)) {
                $id = array_search($route, self::$routes);
                $old = self::$routes[$id]['path'];

                $prefix = preg_replace('/{(\w+)}/', '([a-z0-9_]+(?:-[a-z0-9]+)*)', $prefix);
                $result = ($old != '/') ? $prefix . $old : $prefix;
                self::$routes[$id]['path'] = $result;
            }
        }
    }

    public static function controller(string $name, Closure $next): void
    {
        $routes = static::routeNow();
        $next();

        foreach (static::routeNow() as $route) {
            if (!in_array($route, $routes)) {
                $id = array_search($route, self::$routes);
                $old = self::$routes[$id]['controller'];

                $result = is_null($old) ? $name : $old;
                self::$routes[$id]['controller'] = $result;
            }
        }
    }

    public static function run(Application $app, string $path = '/'): void
    {
        $request = $app->singleton(Request::class);

        $method = $request->method();
        $path = $request->server('PATH_INFO') ?? $path;

        $isRouteMatch = false;
        $isMethodMatch = false;

        foreach (static::routeNow() as $route) {
            $pattern = '#^' . $route['path'] . '$#';
            if (preg_match($pattern, $path, $variables)) {
                $isRouteMatch = true;
                if ($method == $route['method']) {
                    $isMethodMatch = true;
                    self::$urinow = $request->server('REQUEST_URI');

                    static::invokeMiddleware($app, $route);
                    static::invokeController($app, $route, $variables);
                }
            }
        }

        if ($isRouteMatch && !$isMethodMatch) {
            notAllowed();
        } else {
            notFound();
        }
    }
}
