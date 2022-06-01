<?php

namespace Core;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Application
{
    private $objectPool = [];

    public function singleton(string $name, array $param = []): object
    {
        if (empty($this->objectPool[$name])) {
            $this->objectPool[$name] = $this->getConstructor($name, $param);
        }

        return $this->objectPool[$name];
    }

    public function make(string $name, array $param = []): object
    {
        $this->objectPool[$name] = $this->getConstructor($name, $param);

        return $this->objectPool[$name];
    }

    public function invoke(string $name, string $method, array $value = []): mixed
    {
        $name = $this->singleton($name);

        $reflector = new ReflectionClass($name);
        $params = $this->getDependencies($reflector->getMethod($method)->getParameters(), $value);

        try {
            $reflectionMethod = new ReflectionMethod($name, $method);
            return $reflectionMethod->invokeArgs($name, $params);
        } catch (ReflectionException $e) {
            throw new Exception('Error calling method ' . $method . ' - ' . $e->getMessage());
        }
    }

    private function getConstructor(string $name, array $param = []): object
    {
        $reflector = new ReflectionClass($name);

        $constructor = $reflector->getConstructor();
        $args = is_null($constructor) ? null : $constructor->getParameters();

        return $reflector->newInstanceArgs($this->getDependencies($args, $param));
    }

    private function getDependencies(?array $parameters = null, array $value = []): array
    {
        $args = [];
        $param = 0;

        if (!$parameters) {
            return $args;
        }

        foreach ($parameters as $parameter) {
            if ($parameter->getType() && !$parameter->getType()->isBuiltin()) {
                $args[] = $this->singleton($parameter->getType()->getName());
            } else {
                $args[] = $value[$param] ?? $parameter->getDefaultValue();
                $param++;
            }
        }

        return $args;
    }
}
