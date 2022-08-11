<?php

namespace Core\Auth;

use Core\Database\BaseModel;
use Core\Support\Session;
use Exception;

/**
 * Autentikasi user dengan basemodel
 *
 * @class AuthManager
 * @package Core\Auth
 */
class AuthManager
{
    /**
     * Object basemodel
     * 
     * @var BaseModel $user
     */
    private $user;

    /**
     * Object session
     * 
     * @var Session $session
     */
    private $session;

    /**
     * Init obejct
     * 
     * @return void
     */
    function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Check usernya
     * 
     * @return bool
     */
    public function check(): bool
    {
        $user = $this->user();
        return is_null($user) ? false : !empty($user->failFunction(function () {
            $this->logout();
            return false;
        }));
    }

    /**
     * Dapatkan obejek usernya
     * 
     * @return mixed
     */
    public function user(): mixed
    {
        if (!empty($this->user)) {
            return $this->user;
        }

        $user = $this->session->get('_user');
        if (!empty($user)) {
            $this->user = unserialize($user)->refresh();
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
        $this->session->unset('_user');
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
        $this->session->set('_user', serialize($user));
    }

    /**
     * Loginkan usernya
     * 
     * @param array $credential
     * @param string $model
     * @return bool
     */
    public function attempt(array $credential, string $model = 'Models\User'): bool
    {
        $data = array_keys($credential);
        $first = $data[0];
        $last = $data[1];

        $user = app($model)->find($credential[$first], $first);
        $this->logout();

        if ($user->failFunction(fn () => false)) {
            if (password_verify($credential[$last], $user->$last)) {
                $this->user = $user;
                $this->session->set('_user', serialize($user));
                return true;
            }
        }

        $this->session->set('old', [$first => $credential[$first]]);
        $this->session->set('error', [$first => "$first atau $last salah !"]);
        return false;
    }
}
