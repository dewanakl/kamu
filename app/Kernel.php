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
     * Object kernel
     * 
     * @var self $self
     */
    private static $self;

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
        $lines = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }

    /**
     * Apakah https ?
     * 
     * @return bool
     */
    public function getHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443 || @$_ENV['HTTPS'] == 'true';
    }

    /**
     * Dapatkan baseurl
     * 
     * @return string
     */
    public function getBaseurl(): string
    {
        return @$_ENV['BASEURL'] ? rtrim($_ENV['BASEURL'], '/') : (HTTPS ? 'https://' : 'http://') . trim($_SERVER['HTTP_HOST']);
    }

    /**
     * Tangani errornya
     * 
     * @return void
     */
    public function errorHandler(): void
    {
        error_reporting(DEBUG ? E_ALL : 0);

        set_exception_handler(function (mixed $error) {
            if (DEBUG) {
                trace($error);
            }

            unavailable();
        });
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
     * @throws Exception
     */
    public static function web(): object
    {
        if (!(static::$self instanceof self)) {
            static::$self = new self();
        }

        define('HTTPS', static::$self->getHttps());
        define('BASEURL', static::$self->getBaseurl());
        define('DEBUG', @$_ENV['DEBUG'] == 'true');

        static::$self->helper();
        static::$self->errorHandler();
        $app = static::$self->app();

        if (!env('APP_KEY')) {
            throw new \Exception('App Key gk ada !');
        }

        return $app;
    }

    /**
     * Kernel for console
     * 
     * @return object
     */
    public static function console(): object
    {
        if (!(static::$self instanceof self)) {
            static::$self = new self();
        }

        static::$self->helper();
        return static::$self->app();
    }
}
