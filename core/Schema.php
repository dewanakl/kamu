<?php

namespace Core;

use Closure;

class Schema
{
    public static function create(string $name, Closure $attribute): void
    {
        $table = App::get()->singleton(Table::class);
        $table->table($name);
        $attribute($table);

        App::get()->singleton(DataBase::class)->exec($table->export());
    }

    public static function drop(string $name): void
    {
        App::get()->singleton(DataBase::class)->exec('DROP TABLE IF EXISTS ' . $name . ';');
    }
}
