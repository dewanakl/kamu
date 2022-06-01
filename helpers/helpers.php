<?php

use Core\App;
use Core\Auth;
use Core\Render;
use Core\Request;
use Core\Session;

if (!function_exists('app')) {
    function app(?string $class = null): object
    {
        if ($class) {
            return App::get()->singleton($class);
        }

        return App::get();
    }
}

if (!function_exists('session')) {
    function session(): Session
    {
        return app(Session::class);
    }
}

if (!function_exists('auth')) {
    function auth(): Auth
    {
        return app(Auth::class);
    }
}

if (!function_exists('view')) {
    function view(string $view, array $param = [], bool $echo = true): mixed
    {
        $template = new Render($view);
        $template->setData($param);
        $template->show();

        if (!$echo) {
            return $template;
        }

        echo $template;
        return null;
    }
}

if (!function_exists('e')) {
    function e(?string $var): string
    {
        $var = (is_null($var)) ? '' : $var;
        return htmlspecialchars($var, ENT_QUOTES);
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$param): void
    {
        echo '<pre>';
        var_dump($param);
        echo '</pre>';
        exit;
    }
}

if (!function_exists('abort')) {
    function abort(): void
    {
        header("HTTP/1.1 403 Forbidden");
        view('errors/error', [
            'pesan' => 'Forbidden 403'
        ]);
        exit;
    }
}

if (!function_exists('notFound')) {
    function notFound(): void
    {
        header("HTTP/1.1 404 Not Found");
        view('errors/error', [
            'pesan' => 'Not Found 404'
        ]);
        exit;
    }
}

if (!function_exists('notAllowed')) {
    function notAllowed(): void
    {
        header("HTTP/1.1 405 Method Not Allowed");
        view('errors/error', [
            'pesan' => 'Method Not Allowed 405'
        ]);
        exit;
    }
}

if (!function_exists('pageExpired')) {
    function pageExpired(): void
    {
        header("HTTP/1.1 400 Bad Request");
        view('errors/error', [
            'pesan' => 'Page Expired !'
        ]);
        exit;
    }
}

if (!function_exists('unavailable')) {
    function unavailable(): void
    {
        header("HTTP/1.1 503 Service Unavailable");
        view('errors/error', [
            'pesan' => 'Service Unavailable !'
        ]);
        exit;
    }
}

if (!function_exists('extend')) {
    function extend(string $path, array $data = []): Render
    {
        return view($path, $data, false);
    }
}

if (!function_exists('response')) {
    function response(string $redirect): void
    {
        session()->unset('token');
        header('Location: ' . BASEURL . $redirect, TRUE, 302);
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return session()->get('token');
    }
}

if (!function_exists('csrf')) {
    function csrf(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">' . PHP_EOL;
    }
}

if (!function_exists('flash')) {
    function flash(string $key): mixed
    {
        $result = session()->get($key);
        session()->unset($key);
        return $result;
    }
}

if (!function_exists('resJson')) {
    function resJson(array $data, bool $echo = false): string|false
    {
        header('Content-Type: application/json');
        $result = json_encode($data, JSON_PRETTY_PRINT);

        if ($echo) {
            echo $result;
            exit;
        }

        return $result;
    }
}

if (!function_exists('asset')) {
    function asset(string $param): string
    {
        return BASEURL . $param;
    }
}

if (!function_exists('route')) {
    function route(string $param, mixed $key = null): string
    {
        if ($key) {
            $param = preg_replace("/{(\w+)}/", $key, $param);
        }

        return asset($param);
    }
}

if (!function_exists('old')) {
    function old(string $param)
    {
        $old = session()->get('old');
        return e($old[$param] ?? null);
    }
}

if (!function_exists('error')) {
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
    function env(string $key, mixed $optional = null): mixed
    {
        return $_ENV[$key] ?? $optional;
    }
}

if (!function_exists('getPageTime')) {
    function getPageTime(): string
    {
        $time = floor(number_format(microtime(true) - startTime, 3, ''));
        return 'This page was generated in ' . $time . ' ms.';
    }
}
