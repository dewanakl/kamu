<?php

namespace Core;

class Request
{
    private $requestData;
    private $serverData;
    private $fileData;

    private $isFile;

    private $errors = [];

    function __construct()
    {
        $this->requestData = $_REQUEST;
        $this->serverData = $_SERVER;
        $this->fileData = $_FILES;
    }

    private function isError()
    {
        if (empty($this->errors)) {
            return null;
        }

        $olds = [];
        foreach ($this->get() as $key => $val) {
            $olds[$key] = $val;
        }
        session()->set('old', $olds);

        $errors = [];
        foreach ($this->errors as $key => $val) {
            $errors[$key] = $val;
        }
        session()->set('error', $errors);

        respond()->redirect(session()->get('oldRoute', '/'));
    }

    /**
     * kurang file
     */
    private function validateRule($param, $rules): mixed
    {
        foreach ($rules as $rule) {
            switch (true) {
                case $rule == 'file':
                    $this->isFile = true;
                    break;
                case $rule == 'required':
                    if (!$this->__isset($param) || empty(trim($this->requestData[$param]))) {
                        $this->setError($param, 'dibutuhkan !');
                    }
                    break;
                case $rule == 'int':
                    if (!is_numeric($this->get($param, null))) {
                        $this->setError($param, 'harus angka !');
                    } else {
                        $this->__set($param, intval($this->__get($param)));
                    }
                    break;
                case $rule == 'str':
                    $this->__set($param, strval($this->__get($param)));
                    break;
                case $rule == 'hash':
                    $this->__set($param, password_hash($this->__get($param), PASSWORD_BCRYPT));
                    break;
                case str_contains($rule, 'min'):
                    $min = explode(':', $rule)[1];
                    if (strlen($this->get($param)) < $min) {
                        $this->setError($param, 'minimal', $min);
                    }
                    break;
                case str_contains($rule, 'min'):
                    $min = explode(':', $rule)[1];
                    if (strlen($this->get($param)) < $min) {
                        $this->setError($param, 'minimal', $min);
                    }
                    break;
                case str_contains($rule, 'max'):
                    $max = explode(':', $rule)[1];
                    if (strlen($this->get($param)) > $max) {
                        $this->setError($param, 'maximal', $max);
                    }
                    break;
                case str_contains($rule, 'sama'):
                    $target = explode(':', $rule)[1];
                    if ($this->get($target) != $this->get($param)) {
                        $this->setError($param, 'tidak sama dengan', $target);
                    }
                    break;
                default:
                    $this->__set($param, trim($this->__get($param)));
                    break;
            }
        }

        return $this->get($param);
    }

    private function setError(string $param, string $alert, string|int $optional = null): void
    {
        if (empty($this->errors[$param])) {
            $this->errors[$param] = "$param $alert" . ($optional ? " $optional" : null);
        }
    }

    public function server(?string $name = null): mixed
    {
        if (!$name) {
            return $this->serverData;
        }

        return $this->serverData[$name] ?? null;
    }

    public function method(): string
    {
        return $this->server('REQUEST_METHOD');
    }

    public function validate($params = []): array
    {
        foreach ($params as $param => $rules) {
            $rule = $this->validateRule($param, $rules);
            $this->__set($param, $rule);
            $params[$param] = $rule;
        }

        $this->isError();
        return $params;
    }

    /**
     * belum
     */
    public function file(?string $name = null)
    {
        if (!$name) {
            return $this->fileData;
        }

        return (object) $this->fileData[$name];
    }

    public function throwError(array $error): void
    {
        foreach ($error as $key => $value) {
            $this->errors[$key] = $value;
        }
        $this->isError();
    }

    public function ajax(): string|false
    {
        if ($this->server('HTTP_CONTENT_TYPE') && $this->server('HTTP_COOKIE') && $this->server('HTTP_TOKEN')) {
            return $this->server('HTTP_TOKEN');
        }

        return false;
    }

    public function get($name = null, $defaultValue = null): mixed
    {
        if ($name === null) {
            return $this->requestData;
        }

        return $this->requestData[$name] ?? $defaultValue;
    }

    public function __get($name)
    {
        return $this->__isset($name) ? $this->requestData[$name] : null;
    }

    public function __set($name, $value): void
    {
        $this->requestData[$name] = $value;
    }

    public function __isset($name): bool
    {
        return isset($this->requestData[$name]);
    }
}
