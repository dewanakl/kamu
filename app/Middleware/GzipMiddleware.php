<?php

namespace App\Middleware;

use Closure;
use Core\Http\Request;
use Core\Http\Respond;
use Core\Http\Stream;
use Core\Middleware\MiddlewareInterface;
use Generator;

final class GzipMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Stream|Respond|Generator
    {
        $response = $next($request);

        if ($response instanceof Stream || $response instanceof Generator) {
            return $response;
        }

        if ($response instanceof Respond && $response->getCode() < 400 && $response->getCode() >= 300) {
            return $response;
        }

        $response = respond()->transform($response);

        if (env('GZIP', 'true') != 'true') {
            return $response;
        }

        if (!str_contains($request->server->get('HTTP_ACCEPT_ENCODING', ''), 'gzip')) {
            return $response;
        }

        if (!$response->getContent()) {
            return $response;
        }

        $compressed = gzencode($response->getContent(), 3);

        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);

        $varyList = ['Accept-Encoding'];

        if ($response->headers->has('Vary')) {
            $existing = preg_split('/\s*,\s*/', $response->headers->get('Vary'), -1, PREG_SPLIT_NO_EMPTY);
            $varyList = array_merge($existing, $varyList);
        }

        $varyList = array_unique(array_map('trim', $varyList));
        $response->headers->set('Vary', implode(', ', $varyList));

        $response->headers->set('Content-Encoding', 'gzip');

        return $response;
    }
}
