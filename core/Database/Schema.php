<?php

namespace Core\Database;

use Closure;

/**
 * Helper class untuk skema tabel
 *
 * @class Schema
 * @package \Core\Database
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
        $table = app(Table::class);
        $table->table($name);
        app()->resolve($attribute);

        app(DataBase::class)->exec($table->create());
    }

    /**
     * Ubah attribute tabelnya
     *
     * @param string $name
     * @param Closure $attribute
     * @return void
     */
    public static function table(string $name, Closure $attribute): void
    {
        $table = app(Table::class);
        $table->table($name);
        app()->resolve($attribute);

        $export = $table->export();
        if ($export) {
            app(DataBase::class)->exec($export);
        }
    }

    /**
     * Hapus tabel
     *
     * @param string $name
     * @return void
     */
    public static function drop(string $name): void
    {
        app(DataBase::class)->exec('DROP TABLE IF EXISTS ' . $name . ';');
    }

    /**
     * Rename tabelnya
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    public static function rename(string $from, string $to): void
    {
        app(DataBase::class)->exec('ALTER TABLE ' . $from . ' RENAME TO ' . $to . ';');
    }
}
