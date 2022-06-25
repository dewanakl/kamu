<?php

use Models\User;

return new class
{
    /**
     * Generate nilai pada database
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
