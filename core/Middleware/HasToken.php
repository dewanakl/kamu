<?php

namespace Core\Middleware;

trait HasToken
{
    /**
     * Cek token dan ajax yang masuk
     *
     * @param string $token
     * @param bool $ajax
     * @return void
     */
    protected function checkToken(string $token, bool $ajax = false): void
    {
        if (!hash_equals(session()->get('token'), $token)) {
            session()->unset('token');
            respond()->httpCode(400);

            if (!$ajax) {
                pageExpired();
            }

            respond()->terminate(respond()->json(['token' => false], 400));
        }

        if (!$ajax) {
            session()->unset('token');
        }
    }
}
