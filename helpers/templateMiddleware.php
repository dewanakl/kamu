<?php

/**
 * Template untuk membuat file middleware dengan saya console
 *
 * @return string
 */

return '<?php

namespace Middleware;

use Closure;
use Core\Request;

final class NAME implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next)
    {
        //

        return $next($request);
    }
}
';
