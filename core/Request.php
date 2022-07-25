<?php

namespace Core;

/**
 * Request yang masuk
 *
 * @class Request
 * @package Core
 */
class Request
{
    /**
     * Data dari global var request
     * 
     * @var array $requestData
     */
    private $requestData;

    /**
     * Data dari global var server
     * 
     * @var array $serverData
     */
    private $serverData;

    /**
     * Data dari global var files
     * 
     * @var array $fileData
     */
    private $fileData;

    /**
     * Error tampung disini
     * 
     * @var array $errors
     */
    private array $errors = [];

    /**
     * Throw error lewat json
     * 
     * @var bool $json
     */
    private $json;

    /**
     * Init objek
     * 
     * @return void
     */
    function __construct()
    {
        $this->requestData = $_REQUEST;
        $this->serverData = $_SERVER;
        $this->fileData = $_FILES;
        $inputRaw = json_decode(file_get_contents('php://input'), true);

        $this->requestData = array_merge($this->requestData, $inputRaw ?? []);
    }

    /**
     * Cek apakah ada error
     * 
     * @return void
     */
    private function isError(): void
    {
        if (empty($this->errors)) {
            return;
        }

        if ($this->ajax() || $this->json) {
            respond()->terminate(respond()->json([
                'error' => $this->errors
            ], 400));
        }

        session()->set('old', $this->get());
        session()->set('error', $this->errors);
        respond()->redirect(session()->get('oldRoute', '/'));
    }

    /**
     * Validasi rule request yang masuk
     * 
     * @param string $param
     * @param array $rules
     * @return void
     */
    private function validateRule(string $param, array $rules): void
    {
        foreach ($rules as $rule) {
            if (!empty($this->errors[$param])) {
                continue;
            }

            $value = $this->__get($param);

            switch (true) {
                case $rule == 'required':
                    if (!$this->__isset($param) || empty(trim($value))) {
                        $this->setError($param, 'dibutuhkan !');
                    }
                    break;

                case $rule == 'email':
                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->__set($param, filter_var($value, FILTER_SANITIZE_EMAIL));
                    } else {
                        $this->setError($param, 'ilegal atau tidak sah !');
                    }
                    break;

                case $rule == 'int':
                    if (is_numeric($value)) {
                        $this->__set($param, intval($value));
                    } else {
                        $this->setError($param, 'harus angka !');
                    }
                    break;

                case $rule == 'float':
                    if (is_numeric($value)) {
                        $this->__set($param, floatval($value));
                    } else {
                        $this->setError($param, 'harus desimal !');
                    }
                    break;

                case $rule == 'str':
                    $this->__set($param, strval($value));
                    break;

                case $rule == 'hash':
                    $this->__set($param, password_hash($value, PASSWORD_BCRYPT));
                    break;

                case str_contains($rule, 'min'):
                    $min = explode(':', $rule)[1];
                    if (strlen($value) < $min) {
                        $this->setError($param, 'panjang minimal', $min);
                    }
                    break;

                case str_contains($rule, 'max'):
                    $max = explode(':', $rule)[1];
                    if (strlen($value) > $max) {
                        $this->setError($param, 'panjang maximal', $max);
                    }
                    break;

                case str_contains($rule, 'sama'):
                    $target = explode(':', $rule)[1];
                    if ($this->__get($target) != $value) {
                        $this->setError($param, 'tidak sama dengan', $target);
                    }
                    break;

                case str_contains($rule, 'unik'):
                    $command = explode(':', $rule);
                    $model = '\Models\\' . (empty($command[1]) ? 'User' : ucfirst($command[1]));
                    $column = $command[2] ?? $param;

                    $user = app($model)->find($value, $column);
                    if ($user->$column) {
                        $this->setError($param, 'sudah ada !');
                    }
                    break;

                default:
                    $this->__set($param, trim($value));
                    break;
            }
        }
    }

    /**
     * Set error to array errors
     *
     * @param string $param
     * @param string $alert
     * @param string|int $optional
     * @return void
     */
    private function setError(string $param, string $alert, string|int $optional = null): void
    {
        if (empty($this->errors[$param])) {
            $this->errors[$param] = "$param $alert" . ($optional ? " $optional" : null);
        }
    }

    /**
     * Output error json
     *
     * @return self
     */
    public function json(): self
    {
        $this->json = true;
        return $this;
    }

    /**
     * Apakah untuk json ?
     * 
     * @return bool
     */
    public function renderJson(): bool
    {
        return $this->json ?? false;
    }

    /**
     * Ambil nilai dari request server ini
     *
     * @param string $name
     * @return mixed
     */
    public function server(?string $name = null): mixed
    {
        if (!$name) {
            return $this->serverData;
        }

        return $this->serverData[$name] ?? null;
    }

    /**
     * Http method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->server('REQUEST_METHOD');
    }

    /**
     * Cek apakah ajax ?
     *
     * @return string|false
     */
    public function ajax(): string|false
    {
        if ($this->server('HTTP_CONTENT_TYPE') && $this->server('HTTP_COOKIE') && $this->server('HTTP_TOKEN')) {
            return $this->server('HTTP_TOKEN');
        }

        return false;
    }

    /**
     * Validasi request yang masuk
     *
     * @param array $params
     * @return array
     */
    public function validate(array $params = []): array
    {
        foreach ($params as $param => $rules) {
            $this->validateRule($param, $rules);
            $params[$param] = $this->get($param);
        }

        $this->isError();
        return $params;
    }

    /**
     * Ambil nilai dari request file ini
     *
     * @param ?string $name
     * @return array|object
     */
    public function file(?string $name = null): array|object
    {
        if (!$name) {
            return $this->fileData;
        }

        return (object) $this->fileData[$name];
    }

    /**
     * Tampilkan error secara manual
     *
     * @param array $error
     * @return void
     */
    public function throwError(array $error = []): void
    {
        $this->errors = array_merge($this->errors, $error);
        $this->isError();
    }

    /**
     * Ambil nilai dari request ini
     *
     * @param ?string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get(?string $name = null, mixed $defaultValue = null): mixed
    {
        if ($name === null) {
            return $this->requestData;
        }

        return $this->requestData[$name] ?? $defaultValue;
    }

    /**
     * Ambil semua nilai dari request ini
     *
     * @return array
     */
    public function all(): array
    {
        return $this->get();
    }

    /**
     * Ambil nilai dari request ini
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->__isset($name) ? $this->requestData[$name] : null;
    }

    /**
     * Isi nilai ke request ini
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->requestData[$name] = $value;
    }

    /**
     * Cek nilai dari request ini
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->requestData[$name]);
    }
}
