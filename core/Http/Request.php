<?php

namespace Core\Http;

use Core\File\File;
use Core\Valid\Validator;

/**
 * Request yang masuk
 *
 * @class Request
 * @package Core\Http
 */
class Request
{
    /**
     * Data dari global request
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
     * Object validator
     * 
     * @var Validator $validator
     */
    private $validator;

    /**
     * Init objek
     * 
     * @return void
     */
    function __construct()
    {
        $inputRaw = json_decode(file_get_contents('php://input'), true);
        $this->requestData = array_merge($_REQUEST, $_FILES, $inputRaw ?? []);
        $this->serverData = $_SERVER;
    }

    /**
     * Cek apakah ada error
     * 
     * @return void
     */
    private function fails(): void
    {
        if ($this->validator->fails()) {
            session()->set('old', $this->all());
            session()->set('error', $this->validator->failed());
            respond()->redirect(session()->get('_oldroute', '/'));
        }
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
     * Ambil nilai dari request server ini
     *
     * @param ?string $name
     * @return mixed
     */
    public function server(?string $name = null, mixed $defaultValue = null): mixed
    {
        if ($name === null) {
            return $this->serverData;
        }

        return $this->serverData[$name] ?? $defaultValue;
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
     * Dapatkan ipnya
     *
     * @return string
     */
    public function ip(): string
    {
        if ($this->server('HTTP_CLIENT_IP')) {
            return $this->server('HTTP_CLIENT_IP');
        }

        if ($this->server('HTTP_X_FORWARDED_FOR')) {
            $ipList = explode(',', $this->server('HTTP_X_FORWARDED_FOR'));
            foreach ($ipList as $ip) {
                if (!empty($ip)) {
                    return $ip;
                }
            }
        }

        if ($this->server('HTTP_X_FORWARDED')) {
            return $this->server('HTTP_X_FORWARDED');
        }

        if ($this->server('HTTP_X_CLUSTER_CLIENT_IP')) {
            return $this->server('HTTP_X_CLUSTER_CLIENT_IP');
        }

        if ($this->server('HTTP_FORWARDED_FOR')) {
            return $this->server('HTTP_FORWARDED_FOR');
        }

        if ($this->server('HTTP_FORWARDED')) {
            return $this->server('HTTP_FORWARDED');
        }

        if ($this->server('REMOTE_ADDR')) {
            return $this->server('REMOTE_ADDR');
        }
    }

    /**
     * Cek apakah ajax ?
     *
     * @return string|false
     */
    public function ajax(): string|false
    {
        if ($this->server('CONTENT_TYPE') && $this->server('HTTP_COOKIE') && $this->server('HTTP_TOKEN')) {
            return $this->server('HTTP_TOKEN');
        }

        return false;
    }

    /**
     * Tampilkan error secara manual
     *
     * @param array $error
     * @return void
     */
    public function throw(array $error = []): void
    {
        $this->validator->throw($error);
        $this->fails();
    }

    /**
     * Validasi request yang masuk
     *
     * @param array $params
     * @return array
     */
    public function validate(array $params = []): array
    {
        $key = array_keys($params);

        $this->validator = Validator::make($this->only($key), $params);
        $this->fails();

        foreach ($key as $k) {
            $this->__set($k, $this->validator->get($k));
        }

        return $this->only($key);
    }

    /**
     * Ambil file yang masuk
     *
     * @param string $name
     * @return File
     */
    public function file(string $name): File
    {
        $file = new File($this);
        $file->getFromRequest($name);
        return $file;
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
     * Ambil sebagian dari request
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
     * Ambil kecuali dari request
     * 
     * @param array $except
     * @return array
     */
    public function except(array $except): array
    {
        $temp = [];
        foreach ($this->all() as $key => $value) {
            if (!in_array($key, $except)) {
                $temp[$key] = $value;
            }
        }

        return $temp;
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
