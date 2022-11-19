<?php

namespace Core\Valid;

/**
 * Validasi sebuah nilai
 * 
 * @class Validator
 * @package Core\Valid
 */
class Validator
{
    /**
     * Data yang akan di validasi
     * 
     * @var array $data
     */
    private $data;

    /**
     * Error tampung disini
     * 
     * @var array $errors
     */
    private $errors;

    /**
     * Init object
     * 
     * @param array $data
     * @param array $rule
     * @return void
     */
    function __construct(array $data, array $rule)
    {
        $this->data = $data;
        $this->validate($rule);
    }

    /**
     * Validasi rule yang masuk
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

            if (is_array($value)) {
                $this->validateFile($param, $value, $rule);
            } else {
                $this->validateRequest($param, $value, $rule);
            }
        }
    }

    /**
     * Validasi rule request yang masuk
     * 
     * @param string $param
     * @param mixed $value
     * @param string $rules
     * @return void
     */
    private function validateRequest(string $param, mixed $value, string $rule): void
    {
        switch (true) {
            case $rule == 'required':
                if (!$this->__isset($param) || empty(trim(strval($value)))) {
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

            case $rule == 'dns':
                $domain = explode('@', $value);
                if (!checkdnsrr($domain[1])) {
                    $this->setError($param, 'ilegal atau tidak sah !');
                }
                break;

            case $rule == 'url':
                if (filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->__set($param, filter_var($value, FILTER_SANITIZE_URL));
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

            case $rule == 'bool':
                $this->__set($param, boolval($value));
                break;

            case $rule == 'slug':
                $this->__set($param, preg_replace('/[^\w-]/', '', $value));
                break;

            case $rule == 'hash':
                $this->__set($param, Hash::make($value));
                break;

            case $rule == 'trim':
                $this->__set($param, trim($value));
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

                $model = 'Models\\' . (empty($command[1]) ? 'User' : ucfirst($command[1]));
                $column = $command[2] ?? $param;

                $user = app($model)->find($value, $column);
                if ($user->$column) {
                    $this->setError($param, 'sudah ada !');
                }
                break;
        }
    }

    /**
     * Validasi rule file yang masuk
     * 
     * @param string $param
     * @param mixed $value
     * @param string $rules
     * @return void
     */
    private function validateFile(string $param, mixed $value, string $rule): void
    {
        $error = array(
            0 => false,
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => false,
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        );

        $err = $error[$value['error']];
        if ($err) {
            @unlink($value['tmp_name']);
            $this->setError($param, $err);
        } else {
            switch (true) {
                case $rule == 'required':
                    if ($value['error'] == 4 || $value['size'] == 0 || empty($value['name'])) {
                        @unlink($value['tmp_name']);
                        $this->setError($param, 'dibutuhkan !');
                    }
                    break;

                case str_contains($rule, 'min'):
                    $min = explode(':', $rule)[1] * 1024;
                    if ($value['size'] < $min) {
                        @unlink($value['tmp_name']);
                        $this->setError($param, 'ukuran minimal', formatBytes($min));
                    }
                    break;

                case str_contains($rule, 'max'):
                    $max = explode(':', $rule)[1] * 1024;
                    if ($value['size'] > $max) {
                        @unlink($value['tmp_name']);
                        $this->setError($param, 'ukuran maximal', formatBytes($max));
                    }
                    break;

                case str_contains($rule, 'mimetypes'):
                    $mime = explode(':', $rule)[1];
                    if (!in_array($value['type'], explode(',', $mime))) {
                        @unlink($value['tmp_name']);
                        $this->setError($param, 'diperbolehkan', $mime);
                    }
                    break;

                case str_contains($rule, 'mimes'):
                    $mime = explode(':', $rule)[1];
                    if (!in_array(pathinfo($value['full_path'], PATHINFO_EXTENSION), explode(',', $mime))) {
                        @unlink($value['tmp_name']);
                        $this->setError($param, 'diperbolehkan', $mime);
                    }
                    break;
            }
        }
    }

    /**
     * Set error to array errors
     *
     * @param string $param
     * @param string $alert
     * @param mixed $optional
     * @return void
     */
    private function setError(string $param, string $alert, mixed $optional = null): void
    {
        if (empty($this->errors[$param])) {
            $this->errors[$param] = "$param $alert" . ($optional ? " " . strval($optional) : null);
        }
    }

    /**
     * Buat validasinya
     * 
     * @param array $data
     * @param array $rules
     * @return Validator
     */
    public static function make(array $data, array $rule): self
    {
        return new self($data, $rule);
    }

    /**
     * Tambahkan validasi lagi jika perlu
     * 
     * @param array $rules
     * @return void
     */
    public function validate(array $rules): void
    {
        foreach ($rules as $param => $rule) {
            $this->validateRule($param, $rule);
        }
    }

    /**
     * Cek apakah gagal ?
     * 
     * @return bool
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Ambil data gagal validasi
     * 
     * @return array
     */
    public function failed(): array
    {
        return $this->fails() ? $this->errors : [];
    }

    /**
     * Ambil data gagal validasi hanya nilainya
     * 
     * @return array
     */
    public function messages(): array
    {
        return array_values($this->failed());
    }

    /**
     * Set error manual
     * 
     * @param array $error
     * @return void
     */
    public function throw(array $error): void
    {
        $this->errors = array_merge($this->failed(), $error);
    }

    /**
     * Ambil yang sudah di validasi
     * 
     * @return array
     */
    public function validated(): array
    {
        return $this->get();
    }

    /**
     * Ambil sebagian dari validasi
     * 
     * @param array $only
     * @return array
     */
    public function only(array $only): array
    {
        $temp = [];
        foreach ($only as $ol) {
            $temp[$ol] = $this->__get($ol);
        }

        return $temp;
    }

    /**
     * Ambil kecuali dari validasi
     * 
     * @param array $except
     * @return array
     */
    public function except(array $except): array
    {
        $temp = [];
        foreach ($this->get() as $key => $value) {
            if (!in_array($key, $except)) {
                $temp[$key] = $value;
            }
        }

        return $temp;
    }

    /**
     * Ambil nilai dari data ini
     *
     * @param ?string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get(?string $name = null, mixed $defaultValue = null): mixed
    {
        if ($name === null) {
            return $this->data;
        }

        return $this->data[$name] ?? $defaultValue;
    }

    /**
     * Ambil nilai dari data
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->__isset($name) ? $this->data[$name] : null;
    }

    /**
     * Isi nilai data
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * Cek nilai dari data
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }
}
