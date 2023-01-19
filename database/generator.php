<?php

use Core\Database\Generator;
use App\Models\User;

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
            'nama' => 'User',
            'email' => 'user@example.com',
            'password' => password_hash('12345678', PASSWORD_BCRYPT)
        ]);
    }
};
