<?php

namespace Core;

use Exception;
use Models\User;

class Auth
{
    private $user;

    public function check(): bool
    {
        return $this->user() ? true : false;
    }

    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        if (session()->get('user')) {
            $this->user = User::where('id', session()->get('user')->id)->first();
        }

        return $this->user;
    }

    public function logout(): void
    {
        session()->unset('user');
        $this->user = null;
    }

    public function attempt(string $email, string $password): bool
    {
        $credential = User::where('email', $email)->first();

        if ($credential && password_verify($password, $credential->password)) {
            session()->set('user', $credential);
            return true;
        } else {
            session()->set('old', [
                'email' => $email
            ]);
            session()->set('error', [
                'email' => 'Email atau password salah !'
            ]);
            return false;
        }
    }
}
