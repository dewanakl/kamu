<?php

namespace Core\Facades;

use App\Providers\AppServiceProvider;
use App\Providers\RouteServiceProvider;
use Closure;
use Core\Http\Request;
use Core\Http\Respond;
use Core\Middleware\Middleware;
use Core\Routing\Route;
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
        $this->bootingProviders();
    }

    /**
     * Eksekusi provider
     * 
     * @param Closure $fn
     * @return void
     */
    private function providers(Closure $fn): void
    {
        $services = [
            AppServiceProvider::class,
            RouteServiceProvider::class,
        ];

        foreach ($services as $service) {
            $fn($service);
        }
    }

    /**
     * Eksekusi booting provider
     *
     * @return void
     */
    private function bootingProviders(): void
    {
        $this->providers(function ($service) {
            App::get()->make($service)->booting();
        });
    }

    /**
     * Eksekusi register provider
     *
     * @return void
     */
    private function registerProvider(): void
    {
        $this->providers(function ($service) {
            App::get()->clean($service)->registrasi();
        });
    }

    /**
     * Eksekusi middlewarenya
     *
     * @param array $route
     * @return void
     */
    private function invokeMiddleware(array $route): void
    {
        $middlewares = [
            CorsMiddleware::class,
            CsrfMiddleware::class
        ];

        $middlewarePool = array_map(fn ($middleware) => new $middleware(), array_merge($middlewares, $route['middleware']));

        $middleware = new Middleware($middlewarePool);
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

        $this->respond->send(App::get()->invoke($controller, $method, $variables));
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
                $this->respond->terminate(json([
                    'error' => 'Method Not Allowed 405'
                ], 405));
            }

            notAllowed();
        } else if (!$route) {
            if ($this->request->ajax()) {
                $this->respond->terminate(json([
                    'error' => 'Not Found 404'
                ], 404));
            }

            notFound();
        }
    }

    /**
     * Jalankan servicenya
     *
     * @return void
     */
    public function run(): void
    {
        $path = urldecode(parse_url($this->request->server('REQUEST_URI'), PHP_URL_PATH));
        $method = strtoupper(strtoupper($this->request->method()) == 'POST'
            ? $this->request->get('_method', 'POST')
            : $this->request->method());

        $routeMatch = false;
        $methodMatch = false;

        $routes = Route::router()->routes();
        foreach ($routes as $route) {
            $pattern = '#^' . $route['path'] . '$#';
            $variables = null;
            if (preg_match($pattern, $path, $variables)) {
                $routeMatch = true;
                if ($method == $route['method']) {
                    $methodMatch = true;

                    $this->invokeMiddleware($route);
                    $this->registerProvider();
                    $this->invokeController($route, $variables);
                    break;
                }
            }
        }

        $this->outOfRoute($routeMatch, $methodMatch);
    }
}
