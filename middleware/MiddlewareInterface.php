<?php

namespace Middleware;

use Closure;
use Core\Request;

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
