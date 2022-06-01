<?php

namespace Middleware;

use Closure;
use Core\Request;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            response('/login');
        }

        return $next($request);
    }
}
