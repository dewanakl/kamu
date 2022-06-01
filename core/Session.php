<?php

namespace Core;

class Session
{
    function __construct()
    {
        if (!session_id()) {
            session_start();
        }

        if (is_null($this->get('token'))) {
            $this->set('token', bin2hex(random_bytes(32)));
        }
    }

    public function get($name = null, $defaultValue = null): mixed
    {
        if ($name === null) {
            return $_SESSION;
        }

        return $this->__get($name) ?? $defaultValue;
    }

    public function set($name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    public function unset($name)
    {
        unset($_SESSION[$name]);
    }

    public function __get($name)
    {
        return $this->__isset($name) ? $_SESSION[$name] : null;
    }

    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }
}
