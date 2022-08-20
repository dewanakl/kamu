<?php

namespace Middleware;

use Closure;
use Core\Http\Request;
use Core\Middleware\HasToken;
use Core\Middleware\MiddlewareInterface;

final class CsrfMiddleware implements MiddlewareInterface
{
    use HasToken;

    public function handle(Request $request, Closure $next)
    {
        if ($request->method() != 'GET' && (!$request->ajax())) {
            $this->checkToken($request->get('_token', ''));
        }

        if ($request->ajax()) {
            $this->checkToken($request->ajax(), true);
        }

        return $next($request);
    }
}
