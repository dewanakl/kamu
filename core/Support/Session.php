<?php

namespace Core\Support;

/**
 * Class untuk menghandle session
 *
 * @class Session
 * @package Core\Support
 */
class Session
{
    /**
     * Buat objek session
     *
     * @return void
     */
    function __construct()
    {
        if (!session_id()) {
            session_start();
        }

        if (is_null($this->get('token'))) {
            $this->set('token', bin2hex(random_bytes(32)));
        }
    }

    /**
     * Ambil nilai dari sesi ini
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get(string $name = null, mixed $defaultValue = null): mixed
    {
        if ($name === null) {
            return $_SESSION;
        }

        return $this->__get($name) ?? $defaultValue;
    }

    /**
     * Isi nilai ke sesi ini
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Hapus nilai dari sesi ini
     *
     * @param string $name
     * @return void
     */
    public function unset(string $name): void
    {
        unset($_SESSION[$name]);
    }

    /**
     * Ambil nilai dari sesi ini
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->__isset($name) ? $_SESSION[$name] : null;
    }

    /**
     * Cek nilai dari sesi ini
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($_SESSION[$name]);
    }
}
