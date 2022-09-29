<?php

/**
 * Kamu PHP Framework
 * for educational purposes || ready for production
 * 
 * @author dewanakl
 * @see https://github.com/dewanakl/Kamu
 */
class Kernel
{
    /**
     * Object app
     * 
     * @var object $app
     */
    private $app;

    /**
     * Init object
     * 
     * @return void
     */
    function __construct()
    {
        $this->loader();
        $this->app = new \Core\Facades\Application();
        $this->setEnv();
        date_default_timezone_set(@$_ENV['TIMEZONE'] ?? 'Asia/Jakarta');
    }

    /**
     * Load all class
     * 
     * @return bool
     */
    private function loader(): bool
    {
        return spl_autoload_register(function (string $name) {
            $name = lcfirst(str_replace('\\', '/', $name));
            require_once dirname(__DIR__) . '/' . $name . '.php';
        });
    }

    /**
     * Set env from .env file
     * 
     * @return void
     */
    private function setEnv(): void
    {
        $file = __DIR__ . '/../.env';
        $lines = file_exists($file)
            ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            : [];

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }

    /**
     * Import helper
     * 
     * @return void
     */
    public function helper(): void
    {
        require_once __DIR__ . '/../helpers/helpers.php';
    }

    /**
     * Get app
     * 
     * @return object
     */
    public function app(): object
    {
        return \Core\Facades\App::new($this->app);
    }

    /**
     * Kernel for web
     * 
     * @return object
     */
    public static function web(): object
    {
        $self = new self();

        define('HTTPS', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 || @$_ENV['HTTPS']);
        define('BASEURL', @$_ENV['BASEURL'] ? rtrim($_ENV['BASEURL'], '/') : (HTTPS ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
        define('DEBUG', @$_ENV['DEBUG'] == 'true');

        error_reporting(DEBUG ? E_ALL : 0);
        $self->helper();

        set_exception_handler(function (\Throwable $error) {
            header('Content-Type: text/html');
            clear_ob();

            if (!DEBUG) {
                unavailable();
            }

            header('HTTP/1.1 500 Internal Server Error', true, 500);
            echo extend('../helpers/errors/trace', compact('error'));
        });

        if (!env('APP_KEY')) {
            throw new \Exception('App Key gk ada !');
        }

        require_once __DIR__ . '/../routes/routes.php';

        return $self->app();
    }

    /**
     * Kernel for console
     * 
     * @return object
     */
    public static function console(): object
    {
        $self = new self();
        $self->helper();
        return $self->app();
    }
}
