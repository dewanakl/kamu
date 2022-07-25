<?php

namespace Core;

use Exception;
use Models\User;

/**
 * Autentikasi user dengan model database
 *
 * @class Auth
 * @package Core
 */
class Auth
{
    /**
     * Object model
     * 
     * @var BaseModel $user
     */
    private $user;

    /**
     * Check usernya
     * 
     * @return bool
     */
    public function check(): bool
    {
        return !empty($this->user());
    }

    /**
     * Dapatkan obejek usernya
     * 
     * @return BaseModel|null
     */
    public function user(): BaseModel|null
    {
        if (!empty($this->user)) {
            return $this->user;
        }

        $user = session()->get('_user');
        if (!empty($user)) {
            $this->user = unserialize(base64_decode($user))->refresh();
        }

        return $this->user;
    }

    /**
     * Logoutkan usernya
     * 
     * @return void
     */
    public function logout(): void
    {
        $this->user = null;
        session()->unset('_user');
    }

    /**
     * Loginkan usernya dengan object
     * 
     * @param object $user
     * @return void
     * 
     * @throws Exception
     */
    public function login(object $user): void
    {
        if (!($user instanceof BaseModel)) {
            throw new Exception('Class ' . get_class($user) . ' bukan BaseModel !');
        }

        $this->logout();
        $this->user = $user;
        session()->set('_user', base64_encode(serialize($user)));
    }

    /**
     * Loginkan usernya
     * 
     * @param array $credential
     * @param ?string $model
     * @return bool
     */
    public function attempt(array $credential, ?string $model = null): bool
    {
        $data = array_keys($credential);

        $first = $data[0];
        $last = $data[1];

        if (!$model) {
            $model = User::class;
        }

        $this->user = app($model)->find($credential[$first], $first);
        $password = password_verify($credential[$last], $this->user->$last);

        if ($this->user->id && $password) {
            session()->set('_user', base64_encode(serialize($this->user)));
            return true;
        }

        session()->set('old', [$first => $credential[$first]]);
        session()->set('error', [$first => "$first atau $last salah !"]);

        $this->logout();
        return false;
    }
}
