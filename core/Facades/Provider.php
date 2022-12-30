<?php

namespace Core\Facades;

/**
 * Registrasi apa saja sebelum aplikasi berjalan
 *
 * @class Provider
 * @package \Core\Facades
 */
abstract class Provider
{
    /**
     * Object application
     *
     * @var Application $app
     */
    protected $app;

    /**
     * Buat object Provider baru
     *
     * @return void
     */
    function __construct()
    {
        $this->app = App::get();
    }

    /**
     * Registrasi apa aja disini
     *
     * @return void
     */
    public function registrasi()
    {
        //
    }

    /**
     * Jalankan sewaktu aplikasi dinyalakan
     *
     * @return void
     */
    public function booting()
    {
        //
    }
}
