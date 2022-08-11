<?php

namespace Core\Facades;

use Core\Http\Request;
use Core\Http\Respond;
use Core\Middleware\Middleware;
use Core\Middleware\MiddlewareInterface;
use Core\Routing\Router;
use InvalidArgumentException;
use Middleware\CorsMiddleware;
use Middleware\CsrfMiddleware;

/**
 * Class untuk menjalankan middleware dan controller
 *
 * @class Service
 * @package Core\Facades
 */
class Service
{
    /**
     * Objek request disini
     * 
     * @var Request $request
     */
    private $request;

    /**
     * Objek respond disini
     * 
     * @var Respond $respond
     */
    private $respond;

    /**
     * Objek app disini
     * 
     * @var Application $app
     */
    private $app;

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
        $this->app = App::get();
    }

    /**
     * Eksekusi middlewarenya
     *
     * @param array $route
     * @return void
     * 
     * @throws InvalidArgumentException
     */
    private function invokeMiddleware(array $route): void
    {
        $middlewarePool = [];

        $middlewarePool[] = $this->app->make(CorsMiddleware::class);
        $middlewarePool[] = $this->app->make(CsrfMiddleware::class);

        foreach ($route['middleware'] as $middleware) {
            $layer = $this->app->make($middleware);

            if (!($layer instanceof MiddlewareInterface)) {
                throw new InvalidArgumentException(get_class($layer) . ' bukan middleware');
            }

            $middlewarePool[] = $layer;
        }

        $middleware = $this->app->make(Middleware::class, array($middlewarePool));
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

        if (is_null($controller)) {
            $controller = $method;
            $method = '__invoke';
        }

        $this->respond->send($this->app->invoke($controller, $method, $variables));
    }

    /**
     * Routenya salah atau methodnya
     *
     * @param bool $route
     * @param bool $method
     * @return void
     */
    private function outOfRoute(bool $route, bool $method): void
    {
        if ($route && !$method) {
            if ($this->request->ajax()) {
                $this->respond->terminate($this->respond->json([
                    'error' => 'Method Not Allowed 405'
                ], 405));
            }

            notAllowed();
        } else if (!$route) {
            if ($this->request->ajax()) {
                $this->respond->terminate($this->respond->json([
                    'error' => 'Not Found 404'
                ], 404));
            }

            notFound();
        }
    }

    /**
     * Jalankan servicenya
     *
     * @param Router $router
     * @return void
     */
    public function run(Router $router): void
    {
        $path = urldecode(parse_url($this->request->server('REQUEST_URI'), PHP_URL_PATH));
        $method = strtoupper($this->request->method()) == 'POST'
            ? strtoupper($this->request->get('_method', 'POST'))
            : strtoupper($this->request->method());

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

        $this->outOfRoute($routeMatch, $methodMatch);
    }
}
