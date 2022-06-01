<?php

namespace Middleware;

use Closure;
use Core\Request;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        header('Access-Control-Allow-Origin: ' . BASEURL);
        header('Access-Control-Allow-Headers: Origin, Content-Type, token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 3600');

        return $next($request);
    }
}
