<?php

use Core\Facades\App;
use Core\Auth\AuthManager;
use Core\View\Render;
use Core\Http\Request;
use Core\Http\Respond;
use Core\Routing\Route;
use Core\Http\Session;
use Core\View\View;

if (!function_exists('app')) {
    /**
     * Helper method untuk membuat objek secara tunggal
     * 
     * @param mixed $class
     * @return object
     */
    function app(mixed $class = null): object
    {
        $app = App::get();

        if ($class) {
            return $app->singleton($class);
        }

        return $app;
    }
}

if (!function_exists('session')) {
    /**
     * Helper method untuk membuat objek session
     * 
     * @return Session
     */
    function session(): Session
    {
        return app(Session::class);
    }
}

if (!function_exists('respond')) {
    /**
     * Helper method untuk membuat objek respond
     * 
     * @return Respond
     */
    function respond(): Respond
    {
        return app(Respond::class);
    }
}

if (!function_exists('auth')) {
    /**
     * Helper method untuk membuat objek AuthManager
     * 
     * @return AuthManager
     */
    function auth(): AuthManager
    {
        return app(AuthManager::class);
    }
}

if (!function_exists('render')) {
    /**
     * Baca dari view serta masih bentuk object
     * 
     * @param string $path
     * @param array $data
     * @return Render
     */
    function render(string $path, array $data = []): Render
    {
        $template = new Render($path);
        $template->setData($data);
        $template->show();

        return $template;
    }
}

if (!function_exists('clear_ob')) {
    /**
     * Hapus cache ob
     * 
     * @return void
     */
    function clear_ob(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
}

if (!function_exists('json')) {
    /**
     * Ubah ke json
     *
     * @param mixed $data
     * @param int $statusCode
     * @return string|false
     */
    function json(mixed $data, int $statusCode = 200): string|false
    {
        http_response_code($statusCode);
        header('Content-Type: application/json', true, $statusCode);
        return json_encode($data);
    }
}

if (!function_exists('e')) {
    /**
     * Tampikan hasil secara aman
     * 
     * @param mixed $var
     * @return string
     */
    function e(mixed $var): string
    {
        $var = is_null($var) ? '' : strval($var);
        return htmlspecialchars($var);
    }
}

if (!function_exists('trace')) {
    /**
     * Lacak erornya
     * 
     * @param mixed $error
     * @return void
     */
    function trace(mixed $error): void
    {
        clear_ob();
        header('Content-Type: text/html');
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        respond()->send(render('../helpers/errors/trace', ['error' => $error]));
    }
}

if (!function_exists('dd')) {
    /**
     * Tampikan hasil debugging
     * 
     * @param mixed $param
     * @return void
     */
    function dd(mixed ...$param): void
    {
        clear_ob();
        header('Content-Type: text/html');
        respond()->send(render('../helpers/errors/dd', ['param' => $param]));
    }
}

if (!function_exists('abort')) {
    /**
     * Tampikan hasil error 403
     * 
     * @return void
     */
    function abort(): void
    {
        clear_ob();
        header('Content-Type: text/html');
        header('HTTP/1.1 403 Forbidden', true, 403);
        respond()->send(render('../helpers/errors/error', ['pesan' => 'Forbidden 403']));
    }
}

if (!function_exists('notFound')) {
    /**
     * Tampikan hasil error 404
     * 
     * @return void
     */
    function notFound(): void
    {
        clear_ob();
        header('Content-Type: text/html');
        header('HTTP/1.1 404 Not Found', true, 404);
        respond()->send(render('../helpers/errors/error', ['pesan' => 'Not Found 404']));
    }
}

if (!function_exists('notAllowed')) {
    /**
     * Tampikan hasil error 405
     * 
     * @return void
     */
    function notAllowed(): void
    {
        clear_ob();
        header('Content-Type: text/html');
        header('HTTP/1.1 405 Method Not Allowed', true, 405);
        respond()->send(render('../helpers/errors/error', ['pesan' => 'Method Not Allowed 405']));
    }
}

if (!function_exists('pageExpired')) {
    /**
     * Tampikan hasil error 400
     * 
     * @return void
     */
    function pageExpired(): void
    {
        clear_ob();
        header('Content-Type: text/html');
        header('HTTP/1.1 400 Bad Request', true, 400);
        respond()->send(render('../helpers/errors/error', ['pesan' => 'Page Expired !']));
    }
}

if (!function_exists('unavailable')) {
    /**
     * Tampikan hasil error 503
     * 
     * @return void
     */
    function unavailable(): void
    {
        clear_ob();
        header('Content-Type: text/html');
        header('HTTP/1.1 503 Service Unavailable', true, 503);
        respond()->send(render('../helpers/errors/error', ['pesan' => 'Service Unavailable !']));
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Ambil csrf token dari session
     * 
     * @return string
     */
    function csrf_token(): string
    {
        return session()->get('_token');
    }
}

if (!function_exists('csrf')) {
    /**
     * Jadikan html form input
     * 
     * @return string
     */
    function csrf(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">' . PHP_EOL;
    }
}

if (!function_exists('method')) {
    /**
     * Method untuk html
     * 
     * @return string
     */
    function method(string $type): string
    {
        return '<input type="hidden" name="_method" value="' . strtoupper($type) . '">' . PHP_EOL;
    }
}

if (!function_exists('flash')) {
    /**
     * Ambil pesan dari session
     * 
     * @param string $key
     * @return mixed
     */
    function flash(string $key): mixed
    {
        $result = session()->get($key);
        session()->unset($key);
        return $result;
    }
}

if (!function_exists('asset')) {
    /**
     * Gabungkan dengan base url
     * 
     * @param string $param
     * @return string
     */
    function asset(string $param): string
    {
        if (substr($param, 0, 1) != '/') {
            $param = '/' . $param;
        }

        return BASEURL . $param;
    }
}

if (!function_exists('getPathFromRoute')) {
    /**
     * Ambil url dalam route dengan nama
     *
     * @param string $name
     * @return string
     * 
     * @throws Exception
     */
    function getPathFromRoute(string $name): string
    {
        foreach (Route::router()->routes() as $route) {
            if ($route['name'] == $name) {
                return $route['path'];
            }
        }

        throw new Exception('Route "' . $name . '" tidak ditemukan');
    }
}

if (!function_exists('route')) {
    /**
     * Dapatkan url dari route name dan masukan value
     * 
     * @param string $param
     * @param mixed $keys
     * @return string
     * 
     * @throws Exception
     */
    function route(string $param, mixed ...$keys): string
    {
        $regex = '([\w-]*)';
        $param = getPathFromRoute($param);

        foreach ($keys as $key) {
            $pos = strpos($param, $regex);
            $param = ($pos !== false) ? substr_replace($param, strval($key), $pos, strlen($regex)) : $param;
        }

        if (str_contains($param, $regex)) {
            throw new Exception('Key kurang atau tidak ada di suatu fungsi route');
        }

        return asset($param);
    }
}

if (!function_exists('old')) {
    /**
     * Dapatkan nilai yang lama dari sebuah request
     * 
     * @param string $param
     * @return mixed
     */
    function old(string $param): mixed
    {
        $old = session()->get('old');
        return e(@$old[$param]);
    }
}

if (!function_exists('error')) {
    /**
     * Dapatkan pesan error dari request yang salah
     * 
     * @param mixed $key
     * @param mixed $optional
     * @return mixed
     */
    function error(mixed $key = null, mixed $optional = null): mixed
    {
        $error = session()->get('error');

        if (is_null($key)) {
            return $error;
        }

        $result = @$error[$key];

        if ($result && $optional) {
            return $optional;
        }

        return $result;
    }
}

if (!function_exists('routeIs')) {
    /**
     * Cek apakah routenya sudah sesuai
     * 
     * @param string $param
     * @param mixed $optional
     * @param bool $notcontains
     * @return mixed
     */
    function routeIs(string $param, mixed $optional = null, bool $notcontains = false): mixed
    {
        $now = app(Request::class)->server('REQUEST_URI');
        $route = $notcontains ? $now == $param : str_contains($now, $param);

        if ($route && $optional) {
            return $optional;
        }

        return $route;
    }
}

if (!function_exists('env')) {
    /**
     * Dapatkan nilai dari env
     * 
     * @param string $key
     * @param mixed $optional
     * @return mixed
     */
    function env(string $key, mixed $optional = null): mixed
    {
        $key = $_ENV[$key] ?? $optional;

        if ($key == 'null') {
            return $optional;
        }

        return $key;
    }
}

if (!function_exists('now')) {
    /**
     * Dapatkan waktu sekarang Y-m-d H:i:s
     * 
     * @param string $format
     * @return string
     */
    function now(string $format = 'Y-m-d H:i:s'): string
    {
        return (new DateTime())->format($format);
    }
}

if (!function_exists('parents')) {
    /**
     * Set parent html
     * 
     * @param string $name
     * @param array $variables
     * @return void
     */
    function parents(string $name, array $variables = []): void
    {
        app(View::class)->parents($name, $variables);
    }
}

if (!function_exists('section')) {
    /**
     * Bagian awal dari html
     * 
     * @param string $name
     * @return void
     */
    function section(string $name): void
    {
        app(View::class)->section($name);
    }
}

if (!function_exists('content')) {
    /**
     * Tampilkan bagian dari html
     * 
     * @param string $name
     * @return string|false|null
     */
    function content(string $name): string|false|null
    {
        return app(View::class)->content($name);
    }
}

if (!function_exists('endsection')) {
    /**
     * Bagian akhir dari html
     * 
     * @param string $name
     * @return void
     */
    function endsection(string $name): void
    {
        app(View::class)->endsection($name);
    }
}

if (!function_exists('including')) {
    /**
     * Masukan html opsional
     * 
     * @param string $name
     * @return Render
     */
    function including(string $name): Render
    {
        return app(View::class)->including($name);
    }
}

if (!function_exists('formatBytes')) {
    /**
     * Dapatkan format ukuran bytes yang mudah dibaca
     * 
     * @param int $size
     * @param int $precision
     * @return string
     */
    function formatBytes(int $size, int $precision = 2): string
    {
        $base = log($size, 1024);
        $suffixes = ['Byte', 'Kb', 'Mb', 'Gb', 'Tb'];

        return strval(round(pow(1024, $base - floor($base)), $precision)) . $suffixes[floor($base)];
    }
}

if (!function_exists('diffTime')) {
    /**
     * Dapatkan selisih waktu dalam ms
     * 
     * @param float $start
     * @param float $end
     * @return int
     */
    function diffTime(float $start, float $end): int
    {
        return intval(floor(floatval(number_format($end - $start, 3, '', ''))));
    }
}

if (!function_exists('getPageTime')) {
    /**
     * Dapatkan waktu yang dibutuhkan untuk merender halaman dalam (ms)
     * 
     * @return int
     */
    function getPageTime(): int
    {
        return diffTime(START_TIME, microtime(true));
    }
}
