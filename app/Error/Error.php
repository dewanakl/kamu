<?php

namespace App\Error;

use Core\Support\Error as BaseError;

class Error extends BaseError
{
    /**
     * Tampilkan errornya.
     *
     * @return mixed
     */
    public function render(): mixed
    {
        if (!debug()) {
            /**
             * Jika aplikasi tidak dalam mode debug, maka error tidak ditampilkan secara rinci.
             *
             * Anda dapat menggunakan `$id = request()->getRequestId();` untuk menelusuri error lebih lanjut.
             * Contoh penggunaannya:
             * - Cek log berdasarkan ID tersebut, misalnya di folder: cache/log/kamu.log
             */

            // do something here, like logging the error
            // and return basic error response to the user
        }

        return parent::render();
    }
}
