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
            self::$base = App::get()->singleton(BaseModel::class);
        }

        self::$base->table($name);
        return self::$base;
    }
}
