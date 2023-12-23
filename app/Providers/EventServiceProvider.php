<?php

namespace App\Providers;

use Core\Events\Dispatch;
use Core\Events\Listener;
use Core\Facades\Provider;

class EventServiceProvider extends Provider
{
    /**
     * Petakan event untuk pendengar pada aplikasi.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // \App\Events\Registered::class => [
        //     \App\Listeners\SendEmailNotification::class,
        // ],
    ];

    /**
     * Registrasi apa aja disini.
     *
     * @return void
     */
    public function registrasi()
    {
        $this->app->bind(Dispatch::class, function (): Dispatch {
            return new Dispatch(new Listener($this->listen));
        });
    }
}
