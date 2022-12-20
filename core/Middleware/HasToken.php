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
        if (!hash_equals(session()->get('_token'), $token)) {
            session()->unset('_token');

            if (!$ajax) {
                pageExpired();
            }

            respond()->send(json(['token' => false], 400));
        }

        if (!$ajax) {
            session()->unset('_token');
        }
    }
}
