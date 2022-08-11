<?php

use Core\Database\Generator;
use Models\User;

return new class implements Generator
{
    /**
     * Generate nilai database
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'nama' => 'admin',
            'email' => 'admin@admin.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT)
        ]);
    }
};
