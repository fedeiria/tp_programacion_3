<?php

namespace App\Models\ORM;

use App\Models\ORM\Login;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '/login.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

class LoginController
{
    public function SaveUserLogin($userData) {
        $date = date("Y-m-d H:i:s");
        $saveLogin = new Login;
        $saveLogin->id = $userData->id;
        $saveLogin->usuario = $userData->usuario;
        $saveLogin->nombre = $userData->nombre;
        $saveLogin->tipo = $userData->tipo;
        $saveLogin->login =  $date;
        $saveLogin->save();
    }
}

?>