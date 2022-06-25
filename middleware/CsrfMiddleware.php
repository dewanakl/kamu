<?php

namespace Middleware;

use Closure;
use Core\Request;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->method() == 'POST' && (!$request->ajax())) {
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
                respond()->terminate(respond()->json((['token' => 'false'])));
            }
        }

        if (!$ajax) {
            session()->unset('token');
        }
    }
}
