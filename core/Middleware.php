<?php

namespace Core;

use Closure;

/**
 * Middleware - cek dahulu sebelum ke controller
 *
 * @class Middleware
 * @package Core
 */
class Middleware
{
    /**
     * Kumpulan objek middleware ada disini
     * 
     * @var array $layers
     */
    private $layers;

    /**
     * Buat objek middleware
     *
     * @param array $layers
     * @return void
     */
    function __construct(array $layers = [])
    {
        $this->layers = array_reverse($layers);
    }

    /**
     * Handle semua dari layer middleware
     *
     * @param Request $request
     * @return void
     */
    public function handle(Request $request): void
    {
        $completeOnion = array_reduce(
            $this->layers,
            fn ($nextLayer, $layer) => $this->createLayer($nextLayer, $layer),
            fn ($next) => $next
        );

        $completeOnion($request);
    }

    /**
     * Buat lapisan perlayer untuk eksekusi
     *
     * @param mixed $nextLayer
     * @param mixed $layer
     * @return Closure
     */
    private function createLayer(mixed $nextLayer, mixed $layer): Closure
    {
        return function ($request) use ($nextLayer, $layer) {
            return $layer->handle($request, $nextLayer);
        };
    }
}
