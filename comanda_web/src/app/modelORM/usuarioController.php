<?php

namespace App\Models\ORM;

use App\Models\TokenJWT;
use App\Models\Password;
use App\Models\ORM\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '/loginController.php';
include_once __DIR__ . '../../modelAPI/password.php';
include_once __DIR__ . '../../modelAPI/tokenJWT.php';

define("MOZO", "mozo");
define("COCINERO", "cocinero");
define("CANDYBAR", "candybar");
define("BARTENDER", "bartender");
define("CERVECERO", "cervecero");
define("ADMINISTRADOR", "administrador");
define("LIMITE_USUARIOS_ADMINISTRADORES", "3");

class UsuarioController
{
    public function NewAdmin($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['usuario']) && !empty($newRequest['clave']) && !empty($newRequest['nombre'])) {
            $adminCount = Usuario::where('tipo', '=', ADMINISTRADOR)->count();

            if ($adminCount != LIMITE_USUARIOS_ADMINISTRADORES) {
                if (is_null($findUser = Usuario::where('usuario', '=', $newRequest['usuario'])->first())) {
                    $newAdmin = new Usuario;
                    $newAdmin->usuario = $newRequest['usuario'];
                    $newAdmin->clave = Password::Hash($newRequest['clave']);
                    $newAdmin->nombre = $newRequest['nombre'];
                    $newAdmin->tipo = ADMINISTRADOR;
                    $newAdmin->save();
                    $newResponse = $response->withJson("Usuario administrador creado con exito.", 200);
                }
                else
                    $newResponse = $response->withJson("El nombre de usuario ya existe.", 200);
            }
            else
                $newResponse = $response->withJson("Se llego al limite maximo de administradores.", 200);
        }
        else 
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }

    public function NewUser($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['usuario']) && !empty($newRequest['clave']) && !empty($newRequest['nombre']) && !empty($newRequest['puesto'])) {

            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();

                if ($user->tipo == ADMINISTRADOR) {
                    if (is_null($findUser = Usuario::where('usuario', '=', $newRequest['usuario'])->first())) {
                        $newUser = new Usuario;
                        $newUser->usuario = $newRequest['usuario'];
                        $newUser->clave = Password::Hash($newRequest['clave']);
                        $newUser->nombre = $newRequest['nombre'];
                        $newUser->tipo = $newRequest['puesto'];
                        $newUser->estado = 1;
                        $newUser->save();
                        $newResponse = $response->withJson("Usuario '" . $newRequest['puesto'] . "' creado con exito.", 200);
                    }
                    else
                        $newResponse = $response->withJson("El nombre de usuario ya existe.", 200);
                }
                else 
                    $newResponse = $response->withJson("No tiene privilegios para realizar esta accion.", 200);
            }
            catch (\Exception $e) {
                $newResponse = $response->withJson($e->getMessage(), 200);
            }
        }
        else 
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }

    public function ModifyUser($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['usuario']) && !empty($newRequest['clave']) && !empty($newRequest['nombre']) && !empty($newRequest['puesto'])) {

            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();

                if ($user->tipo == ADMINISTRADOR) {
                    if (!is_null($modifyUser = Usuario::where('usuario', '=', $newRequest['usuario'])->first())) {
                        $modifyUser->clave = Password::Hash($newRequest['clave']);
                        $modifyUser->nombre = $newRequest['nombre'];
                        $modifyUser->tipo = $newRequest['puesto'];
                        $modifyUser->save();
                        $newResponse = $response->withJson("Usuario '" . $newRequest['usuario'] . "' modificado con exito.", 200);
                    }
                    else
                        $newResponse = $response->withJson("El nombre de usuario no existe o es incorrecto.", 200);
                }
                else 
                    $newResponse = $response->withJson("No tiene privilegios para realizar esta accion.", 200);
            }
            catch (\Exception $e) {
                $newResponse = $response->withJson($e->getMessage(), 200);
            }
        }
        else 
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }

    public function EnableUser($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['usuario'])) {

            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();

                if ($user->tipo == ADMINISTRADOR) {
                    if (!is_null($enableUser = Usuario::where('usuario', '=', $newRequest['usuario'])->first())) {
                        if ($enableUser->estado == 1) {
                            $newResponse = $response->withJson("No se puede realizar la accion. El usuario '" . $newRequest['usuario'] . "' ya se encuentra habilitado.", 200);
                        }
                        else {
                            $enableUser->estado = 1;
                            $enableUser->save();
                            $newResponse = $response->withJson("Usuario '" . $newRequest['usuario'] . "' habilitado con exito.", 200);
                        }
                    }
                    else
                        $newResponse = $response->withJson("El nombre de usuario no existe o es incorrecto.", 200);
                }
                else 
                    $newResponse = $response->withJson("No tiene privilegios para realizar esta accion.", 200);
            }
            catch (\Exception $e) {
                $newResponse = $response->withJson($e->getMessage(), 200);
            }
        }
        else 
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }

    public function DisableUser($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['usuario'])) {

            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();

                if ($user->tipo == ADMINISTRADOR) {
                    if (!is_null($disableUser = Usuario::where('usuario', '=', $newRequest['usuario'])->first())) {
                        if ($disableUser->estado == 0) {
                            $newResponse = $response->withJson("No se puede realizar la accion. El usuario '" . $newRequest['usuario'] . "' ya se encuentra suspendido.", 200);
                        }
                        else {
                            $disableUser->estado = 0;
                            $disableUser->save();
                            $newResponse = $response->withJson("Usuario '" . $newRequest['usuario'] . "' suspendido con exito.", 200);
                        }
                    }
                    else
                        $newResponse = $response->withJson("El nombre de usuario no existe o es incorrecto.", 200);
                }
                else 
                    $newResponse = $response->withJson("No tiene privilegios para realizar esta accion.", 200);
            }
            catch (\Exception $e) {
                $newResponse = $response->withJson($e->getMessage(), 200);
            }
        }
        else 
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }

    public function Login($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['usuario']) && !empty($newRequest['clave'])) {
            if ($findUser = Usuario::where('usuario', '=', $newRequest['usuario'])->first()) {
                if (Password::Verify($newRequest['clave'], $findUser['clave'])) {
                    if ($findUser->estado == 1) {
                        LoginController::SaveUserLogin($findUser);
                        $newResponse = $response->withJson(TokenJWT::NewToken($newRequest), 200);
                    }
                    else
                        $newResponse = $response->withJson("Acceso denegado. Usuario '" . $newRequest['usuario'] . "' suspendido.", 200);
                }
                else
                    $newResponse = $response->withJson("Contraseña incorrecta.", 200);
            }
            else
                $newResponse = $response->withJson("El usuario no existe.", 200);
        }
        else 
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }
}

?>