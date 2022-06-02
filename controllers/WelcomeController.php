<?php

namespace Controllers;

use Core\Controller;

class WelcomeController extends Controller
{
    public function index()
    {
        return $this->view('welcome', [
            'data' => 'PHP Framework'
        ]);
    }
}
