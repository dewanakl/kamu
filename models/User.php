<?php

namespace Models;

use Core\Model;

final class User extends Model
{
    protected $table = 'users';

    protected $primaryKey = 'id';

    protected $dates = [
        'created_at',
        'updated_at',
    ];
}
