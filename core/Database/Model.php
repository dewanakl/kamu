<?php

namespace Core\Database;

use Core\Facades\App;
use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Representasi model database
 * 
 * TODO : isi ini
 * @method static \Core\Database\BaseModel where(string $column, mixed $value, string $statment = '=', string $agr = 'AND')
 * @see \Core\Database\BaseModel
 *
 * @class Model
 * @package Core\Database
 */
abstract class Model
{
    /**
     * Ambil properti dari child class
     *
     * @param string $class
     * @param string $name
     * @return mixed
     * 
     * @throws Exception
     */
    protected static function getPropertyChild(string $class, string $name): mixed
    {
        $result = null;

        try {
            $reflect = new ReflectionClass($class);
            $property = $reflect->getProperty($name);
            $property->setAccessible(true);
            $result = $property->getValue($reflect->newInstance());
        } catch (ReflectionException $e) {
            throw new Exception($e->getMessage());
        }

        return $result;
    }

    /**
     * Eksekusi method pada basemodel
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    private static function call(string $method, array $parameters): mixed
    {
        $app = App::get();

        $base = $app->make(BaseModel::class);
        $base->table(self::getPropertyChild(get_called_class(), 'table'));
        $base->dates(self::getPropertyChild(get_called_class(), 'dates'));
        $base->primaryKey(self::getPropertyChild(get_called_class(), 'primaryKey'));

        return $app->invoke(BaseModel::class, $method, $parameters);
    }

    /**
     * Panggil method secara static
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return self::call($method, $parameters);
    }

    /**
     * Panggil method secara object
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return self::call($method, $parameters);
    }
}
