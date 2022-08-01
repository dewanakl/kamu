<?php

namespace Middleware;

use Closure;
use Core\Request;

final class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        header('Access-Control-Allow-Origin: ' . BASEURL);
        header('Access-Control-Max-Age: 3600');
        header('X-Content-Type-Options: nosniff');
        header('X-XSS-Protection: 1; mode=block');

        return $next($request);
    }
}
