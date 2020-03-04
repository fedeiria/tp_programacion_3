<?php

namespace App\Models\ORM;

use App\Models\Password;
use App\Models\TokenJWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '../../modelAPI/password.php';
include_once __DIR__ . '../../modelAPI/tokenJWT.php';

class Admin
{
    public function GetLoginUsersBetween($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['fechaInicio']) && !empty($newRequest['fechaFin'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();

                if ($user->tipo == ADMINISTRADOR) {
                    if (!is_null($getUsers = Login::whereBetween('login', [$newRequest['fechaInicio'], $newRequest['fechaFin']])->get())) {
                        foreach ($getUsers as $user)
                            $newResponse = $response->getBody()->write(self::ShowLoginData($user, 200));
                    }
                    else
                        $newResponse = $response->withJson("No se encontraron datos entre las fechas seleccionadas.", 200);
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

    private function ShowLoginData($user) {
        return $data = PHP_EOL . "---------------- LOGIN USUARIOS ----------------"
            . PHP_EOL . "ID: " . $user->id
            . PHP_EOL . "Usuario: " . $user->usuario
            . PHP_EOL . "Nombre: " . $user->nombre
            . PHP_EOL . "Fecha de inicio de sesion: " . $user->login . PHP_EOL;
    }

    public function ViewOrders($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == ADMINISTRADOR) {
                    if (!is_null($orders = Pedido::all())) {
                        foreach ($orders as $order) {
                            $newResponse = $response->getBody()->write(PedidoController::GetOrderData($order) . PHP_EOL, 200);
                        }
                    }
                    else
                        $newResponse = $response->withJson("No hay pedidos cargados.", 200);
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
}

?>