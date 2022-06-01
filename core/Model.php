<?php

namespace Core;

use ReflectionClass;

class Model
{
    private static $query;
    private static $param;

    protected static $db;
    protected $database;

    function __construct()
    {
        if (!self::$db instanceof DataBase) {
            self::$db = App::get()->singleton(DataBase::class);
            $this->database = self::$db;
        }
    }

    private static function getPropertyChild(string $prop): mixed
    {
        $reflect = new ReflectionClass(get_called_class());

        $property = $reflect->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($reflect->newInstance());
    }

    // public function morph()
    // {
    //     $class = self::reflect();
    //     $instance = $class->newInstance();

    //     $fillable = self::propAccessible($class, 'fillable');
    //     $attributes = self::propAccessible($class, 'attributes');
    //     $validAttributes = self::prepareFillable($fillable->getValue($instance), $object);

    //     // fill attributes & set primary key
    //     $attributes->setValue($instance, $validAttributes);

    //     $instance->initialize();

    //     return $instance;
    // }

    public static function raw($query, array $data = [], bool $all = true, bool $execute = false): mixed
    {
        self::$db->query($query);

        foreach ($data as $key => $val) {
            self::$db->bind(":" . $key, $val);
        }

        if ($execute) {
            return self::$db->execute();
        }

        self::$query = null;
        self::$param = null;

        return ($all) ? self::$db->getAll() : self::$db->get();
    }

    public static function all(int $limit = 0, int $offset = 0): Model
    {
        $table = self::getPropertyChild('table');

        if ($limit == 0 && $offset == 0) {
            $query = sprintf('SELECT * FROM %s', $table);
        } else {
            $query = sprintf('SELECT * FROM %s LIMIT %d OFFSET %d', $table, intval($offset), intval($limit));
        }

        self::$query = $query;
        self::$param = [];
        return new self;
    }

    public static function where(string $column, mixed $value, string $statment = '=', string $agr = 'AND'): Model
    {
        if (!self::$query && !self::$param) {
            $table = self::getPropertyChild('table');

            self::$query = sprintf('SELECT * FROM %s', $table);
        }

        if (str_contains(strtolower(self::$query), 'where')) {
            self::$query = self::$query . " " . $agr . " " . $column . " $statment :" . (string) $column;
        } else {
            self::$query = self::$query . " WHERE " . $column . " $statment :" . (string) $column;
        }

        self::$param[(string) $column] = $value;

        return new self;
    }

    public static function create(array $data = []): mixed
    {
        $table = self::getPropertyChild('table');

        $fillabel = [];
        $value = [];
        foreach ($data as $key => $val) {
            $fillabel[] = $key;
            $value[] = ":" . $key;
        }

        $query = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ',  $fillabel),
            implode(', ',  $value)
        );

        return static::raw($query, $data, true, true);
    }

    public function andWhere(string $column, mixed $value, string $statment = '='): Model
    {
        return self::where($column, $value, $statment);
    }

    public function orWhere(string $column, mixed $value, string $statment = '='): Model
    {
        return self::where($column, $value, $statment, 'OR');
    }

    public function toSql(): string
    {
        return self::$query;
    }

    public function get(): mixed
    {
        return static::raw(self::$query, self::$param);
    }

    public function first(): mixed
    {
        return static::raw(self::$query, self::$param, false);
    }

    public function firstOrFail(): mixed
    {
        $result = $this->first();

        if (!$result) {
            notFound();
        }

        return $result;
    }

    public function delete(): mixed
    {
        return static::raw(str_replace("SELECT *", "DELETE", self::$query), self::$param, false);
    }

    public function update(array $data = []): mixed
    {
        self::$query = str_replace("SELECT * FROM", "UPDATE", self::$query);

        $query = "SET";
        foreach ($data as $key => $val) {
            $query .= " " . $key . " = :" . $key;
            (next($data) == true) ? $query .= "," : $query .= " WHERE";
        }

        return static::raw(
            str_replace("WHERE", $query, self::$query),
            array_merge($data, self::$param),
            true,
            true
        );
    }
}
