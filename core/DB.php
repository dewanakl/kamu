<?php

namespace Core;

/**
 * Helper class DB untuk custome nama table
 *
 * @class DB
 * @package Core
 */
final class DB
{
    /**
     * Simpan jadi objek tunggal
     * 
     * @var BaseModel $base
     */
    private static $base;

    /**
     * Nama tabelnya apah ?
     *
     * @param string $name
     * @return BaseModel
     */
    public static function table(string $name): BaseModel
    {
        if (!(self::$base instanceof BaseModel)) {
            self::$base = App::get()->make(BaseModel::class);
        }

        self::$base->table($name);
        return self::$base;
    }

    /**
     * Mulai transaksinya
     *
     * @return bool
     */
    public static function beginTransaction(): bool
    {
        self::$base = App::get()->make(BaseModel::class);
        return self::$base->startTransaction();
    }

    /**
     * Akhiri transaksinya
     *
     * @return bool
     */
    public static function endTransaction(): bool
    {
        return self::$base->endTransaction();
    }
}
