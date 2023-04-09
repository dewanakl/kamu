<?php

namespace App\Middleware;

use Closure;
use Core\Http\Request;
use Core\Middleware\MiddlewareInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        header('Access-Control-Allow-Origin: ' . baseurl());
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization');

        if ($request->method() != 'OPTIONS') {
            return $next($request);
        }

        http_response_code(204);
        header('HTTP/1.1 204 No Content', true, 204);
    }
}
