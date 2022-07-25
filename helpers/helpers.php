<?php

use Core\App;
use Core\Auth;
use Core\Render;
use Core\Request;
use Core\Respond;
use Core\Route;
use Core\Session;

if (!function_exists('app')) {
    /**
     * Helper method untuk membuat objek secara tunggal
     * 
     * @param ?string $class
     * @return object
     */
    function app(?string $class = null): object
    {
        if ($class) {
            return App::get()->singleton($class);
        }

        return App::get();
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
     * Helper method untuk membuat objek auth
     * 
     * @return Auth
     */
    function auth(): Auth
    {
        return app(Auth::class);
    }
}

if (!function_exists('show')) {
    /**
     * Tampikan hasil dari template html
     * 
     * @param string $view
     * @param array $param
     * @param bool $echo
     * @return mixed
     */
    function show(string $view, array $param = [], bool $echo = true): mixed
    {
        $template = app()->make(Render::class, array($view));
        $template->setData($param);
        $template->show();

        if (!$echo) {
            return $template;
        }

        ob_end_clean();
        echo $template;
        return null;
    }
}

if (!function_exists('e')) {
    /**
     * Tampikan hasil secara aman
     * 
     * @param ?string $var
     * @return string
     */
    function e(?string $var): string
    {
        $var = is_null($var) ? '' : $var;
        return htmlspecialchars($var, ENT_QUOTES);
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
        header('Content-Type: text/html');
        show('../helpers/errors/dd', [
            'param' => $param
        ]);
        exit;
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
        header("HTTP/1.1 403 Forbidden");
        show('../helpers/errors/error', [
            'pesan' => 'Forbidden 403'
        ]);
        exit;
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
        header("HTTP/1.1 404 Not Found");
        show('../helpers/errors/error', [
            'pesan' => 'Not Found 404'
        ]);
        exit;
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
        header("HTTP/1.1 405 Method Not Allowed");
        show('../helpers/errors/error', [
            'pesan' => 'Method Not Allowed 405'
        ]);
        exit;
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
        header("HTTP/1.1 400 Bad Request");
        show('../helpers/errors/error', [
            'pesan' => 'Page Expired !'
        ]);
        exit;
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
        header("HTTP/1.1 503 Service Unavailable");
        show('../helpers/errors/error', [
            'pesan' => 'Service Unavailable !'
        ]);
        exit;
    }
}

if (!function_exists('extend')) {
    /**
     * Sambungkan beberapa html
     * 
     * @param string $path
     * @param array $data
     * @return Render
     */
    function extend(string $path, array $data = []): Render
    {
        return show($path, $data, false);
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
        return session()->get('token');
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
        $regex = '([a-zA-Z0-9_]+(?:-[a-zA-Z0-9]+)*)';
        $param = Route::getPath($param);

        foreach ($keys as $key) {
            $pos = strpos($param, $regex);
            $param = ($pos !== false) ? substr_replace($param, $key, $pos, strlen($regex)) : $param;
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
        return e($old[$param] ?? null);
    }
}

if (!function_exists('error')) {
    /**
     * Dapatkan pesan error dari request yang salah
     * 
     * @param ?string $key
     * @param ?string $optional
     * @return mixed
     */
    function error(?string $key = null, ?string $optional = null): mixed
    {
        $error = session()->get('error');

        if (is_null($key)) {
            return $error;
        }

        $result = $error[$key] ?? null;

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
     * @param ?string $optional
     * @param bool $notcontains
     * @return mixed
     */
    function routeIs(string $param, ?string $optional = null, bool $notcontains = false): mixed
    {
        $now = app(Request::class)->server('REQUEST_URI');
        $route = ($notcontains) ? $now == $param : str_contains($now, $param);

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
        return (new DateTime('now'))->format($format);
    }
}

if (!function_exists('getPageTime')) {
    /**
     * Dapatkan waktu yang dibutuhkan untuk merender halaman
     * 
     * @return string
     */
    function getPageTime(): string
    {
        $time = floor(number_format(microtime(true) - START_TIME, 3, ''));
        return 'This page was generated in ' . $time . ' ms.';
    }
}
