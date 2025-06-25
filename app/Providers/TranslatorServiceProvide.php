<?php

namespace App\Providers;

use Core\Facades\Provider;
use Core\Valid\Trans;

class TranslatorServiceProvide extends Provider
{
    private array $allowedLanguages = ['id', 'en'];

    /**
     * Jalankan sewaktu aplikasi dinyalakan.
     *
     * @return void
     */
    public function booting()
    {
        $requestLang = strtolower(request()->get('lang', 'id'));

        if (!in_array($requestLang, $this->allowedLanguages, true)) {
            $requestLang = 'id';
        }

        Trans::setLanguage($requestLang);
    }
}
