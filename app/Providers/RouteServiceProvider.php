<?php

namespace App\Providers;

use Core\Facades\Provider;
use Core\Routing\Router;

class RouteServiceProvider extends Provider
{
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
        $this->app->singleton(Router::class);
        require_once __DIR__ . '/../../routes/routes.php';
    }
}
