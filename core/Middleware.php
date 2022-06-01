<?php

namespace Core;

use Closure;
use InvalidArgumentException;
use Middleware\MiddlewareInterface;

class Middleware
{
    private $layers;

    function __construct(array $layers = [])
    {
        $this->layers = $layers;
    }

    public function layer(array $layers): Middleware
    {
        if ($layers instanceof MiddlewareInterface) {
            $layers = [$layers];
        }

        if (!is_array($layers)) {
            throw new InvalidArgumentException(get_class($layers) . " is not a middleware.");
        }

        return new static(array_merge($this->layers, $layers));
    }

    public function handle(Request $request, Closure $core): mixed
    {
        $coreFunction = $this->createCoreFunction($core);

        $layers = array_reverse($this->layers);

        $completeOnion = array_reduce($layers, function ($nextLayer, $layer) {
            return $this->createLayer($nextLayer, $layer);
        }, $coreFunction);

        return $completeOnion($request);
    }

    private function createCoreFunction(Closure $core): Closure
    {
        return function ($request) use ($core) {
            return $core($request);
        };
    }

    private function createLayer($nextLayer, $layer): Closure
    {
        return function ($request) use ($nextLayer, $layer) {
            return $layer->handle($request, $nextLayer);
        };
    }
}
