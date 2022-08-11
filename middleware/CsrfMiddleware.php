<?php

namespace Middleware;

use Closure;
use Core\Http\Request;
use Core\Middleware\MiddlewareInterface;

final class CsrfMiddleware implements MiddlewareInterface
{
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

    /**
     * Cek token dan ajax yang masuk
     *
     * @param string $token
     * @param bool $ajax
     * @return void
     */
    private function checkToken(string $token, bool $ajax = false): void
    {
        if (!hash_equals(session()->get('token'), $token)) {
            session()->unset('token');
            respond()->httpCode(400);

            if (!$ajax) {
                pageExpired();
            } else {
                respond()->terminate(respond()->json(['token' => false], 400));
            }
        }

        if (!$ajax) {
            session()->unset('token');
        }
    }
}
