<?php

namespace App\Middleware;

use Closure;
use Core\Auth\Auth;
use Core\Http\Request;
use Core\Middleware\MiddlewareInterface;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        return respond()->to('/login');
    }
}
