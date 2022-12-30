<?php

namespace Core\Database;

use Closure;
use Exception;

/**
 * Helper class DB untuk customizable nama table
 *
 * @class DB
 * @package \Core\Database
 */
final class DB
{
    /**
     * Nama tabelnya apah ?
     *
     * @param string $name
     * @return BaseModel
     */
    public static function table(string $name): BaseModel
    {
        $base = new BaseModel();
        $base->table($name);
        return $base;
    }

    /**
     * Mulai transaksinya
     *
     * @return bool
     */
    public static function beginTransaction(): bool
    {
        return app(DataBase::class)->beginTransaction();
    }

    /**
     * Commit transaksinya
     *
     * @return bool
     */
    public static function commit(): bool
    {
        return app(DataBase::class)->commit();
    }

    /**
     * Kembalikan transaksinya
     *
     * @return bool
     */
    public static function rollBack(): bool
    {
        return app(DataBase::class)->rollBack();
    }

    /**
     * Tampilkan errornya
     *
     * @param mixed $e
     * @return void
     */
    public static function exception(mixed $e): void
    {
        app(DataBase::class)->catchException($e);
    }

    /**
     * DB transaction sederhana
     *
     * @param Closure $fn
     * @return void
     */
    public static function transaction(Closure $fn): void
    {
        try {
            self::beginTransaction();
            $fn();
            self::commit();
        } catch (Exception $e) {
            self::rollBack();
            self::exception($e);
        }
    }
}
