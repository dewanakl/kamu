<?php

namespace Core\Support;

use Core\Valid\Hash;

/**
 * Saya console untuk mempermudah develop app
 *
 * @class Console
 * @package Core\Support
 */
class Console
{
    /**
     * Perintah untuk eksekusi
     * 
     * @var string|null $command
     */
    private $command;

    /**
     * Perintah untuk eksekusi
     * 
     * @var string $command
     */
    private $options;

    /**
     * Waktu yang dibutuhkan
     * 
     * @var int $timenow
     */
    private $timenow;

    /**
     * Apakah versi cmd dibawah 10 ?
     * 
     * @var bool $version
     */
    private $version;

    /**
     * Buat objek console
     *
     * @param array $argv
     * @return void
     */
    function __construct(array $argv)
    {
        $this->timenow = START_TIME;
        $this->version = intval(php_uname('r')) >= 10 || !str_contains(php_uname('s'), 'Windows');

        if (PHP_MAJOR_VERSION < 8) {
            $this->exception('Minimal PHP 8 !');
        }

        array_shift($argv);
        $this->command = $argv[0] ?? null;
        $this->options = $argv[1] ?? null;

        print($this->createColor('green', "Kamu PHP Framework v1.0\n"));
        print($this->createColor('yellow', "Saya Console v1.0\n\n"));
    }

    /**
     * Print spasi ketika selesai
     *
     * @return void
     */
    function __destruct()
    {
        print(PHP_EOL);
    }

    /**
     * Buat warna untuk string
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function createColor(string $name, string $value): string
    {
        if (!$this->version) {
            return $value;
        }

        $colors = [
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'cyan' => "\033[36m",
            'red' => "\033[31m"
        ];

        foreach ($colors as $key => $val) {
            if ($key == $name) {
                return $val . $value . "\033[37m";
            }
        }

        return $value;
    }

    /**
     * Tampilkan pesan khusus error
     *
     * @param string $message
     * @param bool $fail
     * @param ?string $options
     * @return void
     */
    private function exception(string $message, bool $fail = true, ?string $options = null): void
    {
        if ($fail) {
            exit($this->createColor('red', $message . "\n"));
        }

        if ($options) {
            print($this->createColor('green', "\n$options\n"));
        }
    }

    /**
     * Kalkulasi waktu yang dibutuhkan
     *
     * @return string
     */
    private function executeTime(): string
    {
        $now = microtime(true);
        $result = floor(number_format($now - $this->timenow, 3, ''));
        $this->timenow = $now;

        return $this->createColor('cyan', '(' . $result . ' ms)');
    }

    /**
     * Migrasi ke database
     *
     * @param bool $up
     * @return void
     */
    private function migrasi(bool $up = true): void
    {
        $baseFile = __DIR__ . '/../../database/schema/';

        $files = scandir($baseFile, ($up) ? 0 : 1);
        $files = array_diff($files, array('..', '.'));

        foreach ($files as $file) {
            $arg = require $baseFile . $file;
            ($up) ? $arg->up() : $arg->down();
            $info = ($up) ? $this->createColor('green', ' Migrasi ') : $this->createColor('yellow', ' Migrasi kembali ');
            print("\n" . $file . $info . $this->executeTime());
        }
    }

    /**
     * Isi nilai ke database
     *
     * @return void
     */
    private function generator(): void
    {
        $arg = require_once __DIR__ . '/../../database/generator.php';
        $arg->run();
        print("\nGenerator" . $this->createColor('green', ' berhasil ') . $this->executeTime());
    }

    /**
     * Buat file migrasi
     *
     * @param ?string $name
     * @return void
     */
    private function createMigrasi(?string $name): void
    {
        $this->exception('Butuh Nama file !', !$name);
        $data = require_once __DIR__ . '/../../helpers/templates/templateMigrasi.php';
        $data = str_replace('NAME', $name, $data);
        $result = file_put_contents(__DIR__ . '/../../database/schema/' . strtotime('now') . '_' . $name . '.php', $data);
        $this->exception('Gagal membuat migrasi', !$result, 'Berhasil membuat migrasi ' . $name);
    }

    /**
     * Buat file middleware
     *
     * @param ?string $name
     * @return void
     */
    private function createMiddleware(?string $name): void
    {
        $this->exception('Butuh Nama file !', !$name);
        $data = require_once __DIR__ . '/../../helpers/templates/templateMiddleware.php';
        $data = str_replace('NAME', $name, $data);
        $result = file_put_contents(__DIR__ . '/../../middleware/' . $name . '.php', $data);
        $this->exception('Gagal membuat middleware', !$result, 'Berhasil membuat middleware ' . $name);
    }

    /**
     * Buat file controller
     *
     * @param ?string $name
     * @return void
     */
    private function createController(?string $name): void
    {
        $this->exception('Butuh Nama file !', !$name);
        $data = require_once __DIR__ . '/../../helpers/templates/templateController.php';
        $data = str_replace('NAME', $name, $data);
        $result = file_put_contents(__DIR__ . '/../../controllers/' . $name . '.php', $data);
        $this->exception('Gagal membuat controller', !$result, 'Berhasil membuat controller ' . $name);
    }

    /**
     * Buat file model
     *
     * @param ?string $name
     * @return void
     */
    private function createModel(?string $name): void
    {
        $this->exception('Butuh Nama file !', !$name);
        $data = require_once __DIR__ . '/../../helpers/templates/templateModel.php';
        $data = str_replace('NAME', $name, $data);
        $data = str_replace('NAMe', strtolower($name), $data);
        $result = file_put_contents(__DIR__ . '/../../models/' . $name . '.php', $data);
        $this->exception('Gagal membuat model', !$result, 'Berhasil membuat model ' . $name);
    }

    /**
     * Tampilkan list menu yang ada
     *
     * @return void
     */
    private function listMenu(): void
    {
        $menus = [
            [
                'command' => 'coba',
                'description' => 'Jalankan php dengan virtual server'
            ],
            [
                'command' => 'key',
                'description' => 'Amankan aplikasi ini dengan kunci random'
            ],
            [
                'command' => 'migrasi',
                'description' => 'Bikin tabel didatabase kamu [--gen]'
            ],
            [
                'command' => 'migrasi:kembali',
                'description' => 'Kembalikan seperti awal databasenya'
            ],
            [
                'command' => 'migrasi:segar',
                'description' => 'Kembalikan seperti awal dan isi ulang [--gen]'
            ],
            [
                'command' => 'generator',
                'description' => 'Isi nilai ke databasenya'
            ],
            [
                'command' => 'bikin:migrasi',
                'description' => 'Bikin file migrasi [nama file]'
            ],
            [
                'command' => 'bikin:middleware',
                'description' => 'Bikin file middleware [nama file]'
            ],
            [
                'command' => 'bikin:controller',
                'description' => 'Bikin file controller [nama file]'
            ],
            [
                'command' => 'bikin:model',
                'description' => 'Bikin file model [nama file]'
            ],
        ];

        print("Penggunaan:\n perintah [options]\n\n");
        $mask = $this->createColor('cyan', "%-20s") . " %-30s\n";

        foreach ($menus as $value) {
            printf($mask, $value['command'], $value['description']);
        }
    }

    /**
     * Create key to env file
     *
     * @return void
     */
    private function createKey(): void
    {
        $env = __DIR__ . '/../../.env';
        if (!file_exists($env)) {
            $this->exception('env tidak ada !');
        }

        $lines = file($env, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $id => $line) {
            if (str_contains($line, 'APP_KEY=')) {
                $lines[$id] = 'APP_KEY=' . Hash::rand(8) . ':' . Hash::rand(8);
                break;
            }
        }

        $myfile = fopen($env, 'w');
        fwrite($myfile, join("\n", $lines));
        fclose($myfile);

        print("\nAplikasi aman !" . $this->createColor('green', ' berhasil ') . $this->executeTime());
    }

    /**
     * Jalankan console
     *
     * @return int
     */
    public function run(): int
    {
        switch ($this->command) {
            case 'coba':
                $location = ($this->options) ? $this->options : 'localhost';
                shell_exec("php -S $location:8000 -t public");
                break;
            case 'key':
                $this->createKey();
                break;
            case 'migrasi':
                $this->migrasi();
                if ($this->options == '--gen') {
                    $this->generator();
                }
                break;
            case 'generator':
                $this->generator();
                break;
            case 'migrasi:kembali':
                $this->migrasi(false);
                break;
            case 'migrasi:segar':
                $this->migrasi(false);
                $this->migrasi();
                if ($this->options == '--gen') {
                    $this->generator();
                }
                break;
            case 'bikin:migrasi':
                $this->createMigrasi($this->options);
                break;
            case 'bikin:middleware':
                $this->createMiddleware($this->options);
                break;
            case 'bikin:controller':
                $this->createController($this->options);
                break;
            case 'bikin:model':
                $this->createModel($this->options);
                break;
            default:
                $this->listMenu();
                break;
        }

        return 0;
    }
}
