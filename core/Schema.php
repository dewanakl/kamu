<?php

namespace Core;

use Closure;

/**
 * Helper class untuk skema tabel
 *
 * @class Schema
 * @package Core
 */
final class Schema
{
    /**
     * Bikin tabel baru
     *
     * @param string $name
     * @param Closure $attribute
     * @return void
     */
    public static function create(string $name, Closure $attribute): void
    {
        $table = App::get()->singleton(Table::class);
        $table->table($name);
        $attribute($table);

        App::get()->singleton(DataBase::class)->exec($table->export());
    }

    /**
     * Hapus tabel
     *
     * @param string $name
     * @return void
     */
    public static function drop(string $name): void
    {
        App::get()->singleton(DataBase::class)->exec('DROP TABLE IF EXISTS ' . $name . ';');
    }
}
