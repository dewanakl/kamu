<?php

namespace App\Models;

use Core\Model\Model;

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
}
