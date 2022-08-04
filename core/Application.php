<?php

namespace Core;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Aplikasi untuk menampung kumpulan objek yang bisa digunakan kembali
 *
 * @class Application
 * @package Core
 */
class Application
{
    /**
     * Kumpulan objek ada disini gaes
     * 
     * @var array $objectPool
     */
    private $objectPool;

    /**
     * Buat objek application
     *
     * @return void
     */
    function __construct()
    {
        $this->objectPool = [];
    }

    /**
     * Inject pada constructor yang akan di buat objek
     *
     * @param string $name
     * @param array $param
     * @return object
     */
    private function getConstructor(string $name, array $param = []): object
    {
        $reflector = new ReflectionClass($name);

        $constructor = $reflector->getConstructor();
        $args = is_null($constructor) ? null : $constructor->getParameters();

        return $reflector->newInstanceArgs($this->getDependencies($args, $param));
    }

    /**
     * Cek apa aja yang dibutuhkan untuk injek objek atau parameter
     *
     * @param ?array $parameters
     * @param array $value
     * @return array
     */
    private function getDependencies(?array $parameters = null, array $value = []): array
    {
        $args = [];
        $paramid = 0;

        if (!$parameters) {
            return $args;
        }

        foreach ($parameters as $parameter) {
            if ($parameter->getType() && !$parameter->getType()->isBuiltin()) {
                $args[] = $this->singleton($parameter->getType()->getName());
            } else {
                $args[] = $value[$paramid] ?? $parameter->getDefaultValue();
                $paramid++;
            }
        }

        return $args;
    }

    /**
     * Bikin objek dari sebuah class lalu menyimpannya
     *
     * @param string $name
     * @param array $param
     * @return object
     */
    public function singleton(string $name, array $param = []): object
    {
        if (empty($this->objectPool[$name])) {
            $this->objectPool[$name] = $this->getConstructor($name, $param);
        }

        return $this->objectPool[$name];
    }

    /**
     * Bikin objek dari sebuah class lalu gantikan dengan yang lama
     *
     * @param string $name
     * @param array $param
     * @return object
     */
    public function make(string $name, array $param = []): object
    {
        $this->objectPool[$name] = $this->getConstructor($name, $param);

        return $this->objectPool[$name];
    }

    /**
     * Inject objek pada suatu fungsi yang akan di eksekusi
     *
     * @param string $name
     * @param string $method
     * @param array $value
     * @return mixed
     * 
     * @throws Exception
     */
    public function invoke(string $name, string $method, array $value = []): mixed
    {
        $name = $this->singleton($name);

        $reflector = new ReflectionClass($name);
        $params = $this->getDependencies($reflector->getMethod($method)->getParameters(), $value);

        try {
            $reflectionMethod = new ReflectionMethod($name, $method);
            return $reflectionMethod->invokeArgs($name, $params);
        } catch (ReflectionException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
