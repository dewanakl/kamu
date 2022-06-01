<?php

namespace Middleware;

use Closure;
use Core\Request;

final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            response('/');
        }

        return $next($request);
    }
}
