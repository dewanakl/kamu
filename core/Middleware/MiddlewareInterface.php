<?php

namespace Core\Middleware;

use Closure;
use Core\Http\Request;

interface MiddlewareInterface
{
    /**
     * Handle request yang masuk
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next);
}
