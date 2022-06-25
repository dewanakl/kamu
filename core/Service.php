<?php

namespace Core;

use InvalidArgumentException;
use Middleware\CorsMiddleware;
use Middleware\CsrfMiddleware;
use Middleware\MiddlewareInterface;

/**
 * Class untuk menjalankan middleware dan controller
 *
 * @class Service
 * @package Core
 */
class Service
{
    /**
     * Objek request disini
     * 
     * @var Request $request
     */
    private Request $request;

    /**
     * Objek respond disini
     * 
     * @var Respond $respond
     */
    private Respond $respond;

    /**
     * Buat objek service
     *
     * @param Request $request
     * @param Respond $respond
     * @return void
     */
    function __construct(Request $request, Respond $respond)
    {
        $this->request = $request;
        $this->respond = $respond;
    }

    /**
     * Eksekusi middlewarenya
     *
     * @param array $route
     * @return void
     */
    private function invokeMiddleware(array $route): void
    {
        $app = App::get();
        $middlewarePool = [];

        $middlewarePool[] = $app->make(CorsMiddleware::class);
        $middlewarePool[] = $app->make(CsrfMiddleware::class);

        foreach ($route['middleware'] as $middleware) {
            $layer = $app->make($middleware);

            if (!$layer instanceof MiddlewareInterface) {
                throw new InvalidArgumentException(get_class($layer) . ' bukan middleware');
            }

            $middlewarePool[] = $layer;
        }

        $middleware = $app->make(Middleware::class, array($middlewarePool));
        $middleware->handle($this->request);
    }

    /**
     * Eksekusi controllernya
     *
     * @param array $route
     * @param array $variables
     * @return void
     */
    private function invokeController(array $route, array $variables): void
    {
        $controller = $route['controller'];
        $method = $route['function'];
        array_shift($variables);

        $this->respond->send(App::get()->invoke($controller, $method, $variables));
    }

    /**
     * Jalankan servicenya
     *
     * @param Router $router
     * @param string $path
     * @return void
     */
    public function run(Router $router, string $path = '/'): void
    {
        $method = $this->request->method();
        $path = $this->request->server('PATH_INFO') ?? $path;

        $routeMatch = false;
        $methodMatch = false;

        foreach ($router->routes() as $route) {
            $pattern = '#^' . $route['path'] . '$#';
            if (preg_match($pattern, $path, $variables)) {
                $routeMatch = true;
                if ($method == $route['method']) {
                    $methodMatch = true;

                    $this->invokeMiddleware($route);
                    $this->invokeController($route, $variables);
                    break;
                }
            }
        }

        if ($routeMatch && !$methodMatch) {
            notAllowed();
        } else if (!$routeMatch) {
            notFound();
        }
    }
}
