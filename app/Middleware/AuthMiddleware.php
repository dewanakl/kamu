<?php

namespace App\Middleware;

use Closure;
use Core\Http\Request;
use Core\Middleware\MiddlewareInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            return $next($request);
        }

        respond()->redirect('/login');
    }
}
