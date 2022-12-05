<?php

namespace Core\Database;

use Exception;
use ReflectionClass;
use ReflectionException;

/**
 * Representasi model database
 * 
 * @method static \Core\Database\BaseModel where(string $column, mixed $value, string $statment = '=', string $agr = 'AND')
 * @method static \Core\Database\BaseModel join(string $table, string $column, string $refers, string $param = '=', string $type = 'INNER')
 * @method static \Core\Database\BaseModel leftJoin(string $table, string $column, string $refers, string $param = '=')
 * @method static \Core\Database\BaseModel rightJoin(string $table, string $column, string $refers, string $param = '=')
 * @method static \Core\Database\BaseModel fullJoin(string $table, string $column, string $refers, string $param = '=')
 * @method static \Core\Database\BaseModel orderBy(string $name, string $order = 'ASC')
 * @method static \Core\Database\BaseModel groupBy(string ...$param)
 * @method static \Core\Database\BaseModel limit(int $param)
 * @method static \Core\Database\BaseModel offset(int $param)
 * @method static \Core\Database\BaseModel select(string|array ...$param)
 * @method static \Core\Database\BaseModel counts(string $name = '*')
 * @method static \Core\Database\BaseModel max(string $name)
 * @method static \Core\Database\BaseModel min(string $name)
 * @method static \Core\Database\BaseModel avg(string $name)
 * @method static \Core\Database\BaseModel sum(string $name)
 * @method static \Core\Database\BaseModel get()
 * @method static \Core\Database\BaseModel first()
 * @method static \Core\Database\BaseModel all()
 * @method static \Core\Database\BaseModel id(mixed $id, mixed $where = null)
 * @method static \Core\Database\BaseModel find(mixed $id, mixed $where = null)
 * @method static mixed findOrFail(mixed $id, mixed $where = null)
 * @method static bool destroy(int $id)
 * @method static mixed create(array $data)
 * @method static bool update(array $data)
 * @method static bool delete()
 * 
 * @see \Core\Database\BaseModel
 *
 * @class Model
 * @package \Core\Database
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
        $base = app()->make(BaseModel::class);
        $base->table(self::getPropertyChild(get_called_class(), 'table'));
        $base->dates(self::getPropertyChild(get_called_class(), 'dates'));
        $base->primaryKey(self::getPropertyChild(get_called_class(), 'primaryKey'));

        return app()->invoke($base, $method, $parameters);
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
