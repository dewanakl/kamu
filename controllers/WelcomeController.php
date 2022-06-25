<?php

namespace Controllers;

use Core\Controller;

class WelcomeController extends Controller
{
    function __invoke()
    {
        return $this->view('welcome', [
            'data' => 'PHP Framework'
        ]);
    }
}
