<?php

namespace App\Models;

use Core\Model\Model;
use Core\Valid\Hash;

final class User extends Model
{
    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $typeKey = 'int';

    protected $fillable = [
        'nama',
        'email',
        'password'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected function fakes()
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->email(),
            'password' => Hash::make(fake()->text(8)),
        ];
    }
}
