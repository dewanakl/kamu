<?php

namespace Core;

class DB
{
    private static $table;
    private $query;
    private $param;

    protected static $db;

    public static function table(string $name)
    {
        if (!self::$db instanceof DataBase) {
            self::$db = App::get()->singleton(DataBase::class);
        }
        self::$table = $name;

        return new self;
    }

    public function all()
    {
        if (empty($this->query)) {
            $this->query = "SELECT * FROM " . self::$table;
        }

        self::$db->query($this->query);
        return self::$db->getAll();
    }
}
