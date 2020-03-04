<?php

namespace App\Models\ORM;

use App\Models\TokenJWT;
use App\Models\Password;
use App\Models\ORM\Pedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '/foto.php';
include_once __DIR__ . '../../modelAPI/password.php';
include_once __DIR__ . '../../modelAPI/tokenJWT.php';

define("PENDIENTE", "pendiente");
define("EN_PREPARACION", "en preparacion");
define("LISTO_PARA_SERVIR", "listo para servir");
define("CANCELADO", "cancelado");

class PedidoController
{
    public function NewOrder($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['nombreCliente']) && !empty($_FILES['foto']['name'])) {
            if (!empty($newRequest['entrada']) || !empty($newRequest['platoPrincipal']) || !empty($newRequest['barraCerveza']) || !empty($newRequest['barraTrago']) || !empty($newRequest['postre'])) {
                try {
                    $login = TokenJWT::GetData($newRequest['token']);
                    $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                    if ($user->tipo == MOZO) {
                        if (!is_null($getTable = Mesa::where('estado', '=', MESA_CERRADA)->first())) {
                            if (!is_null($pathPhoto = Foto::GetPhoto())) {
                                $newOrder = new Pedido;
                                $newOrder->idMesa = $getTable->id;
                                $newOrder->nombreCliente = $newRequest['nombreCliente'];
                                $newOrder->entrada = $newRequest['entrada'];
                                $newOrder->platoPrincipal = $newRequest['platoPrincipal'];
                                $newOrder->barraCerveza = $newRequest['barraCerveza'];
                                $newOrder->barraTrago = $newRequest['barraTrago'];
                                $newOrder->postre = $newRequest['postre'];
                                $newOrder->estado = PENDIENTE;
                                $newOrder->foto = $pathPhoto;
                                $newOrder->usuario = $user->usuario;
                                $newOrder->save();
                                MesaController::NewTableRestaurant($newOrder->idMesa, $newOrder->id);
                                CocinaController::NewPlate($newOrder->id, $newOrder->entrada, $newOrder->platoPrincipal);
								BebidaController::NewDrink($newOrder->id, $newRequest['barraCerveza'], $newRequest['barraTrago']);
                                PostreController::NewDessert($newOrder->id, $newOrder->postre);
                                $newResponse = $response->getBody()->write("Nuevo pedido cargado con exito." . PHP_EOL . self::GetOrderData($newOrder), 200);
                            }
                            else
                                $newResponse = $response->withJson("El formato de imagen no es valido o excede el tamaño permitido.", 200);
                        }
                        else
                            $newResponse = $response->withJson("No hay mesas disponibles.", 200);
                    }
                    else
                        $newResponse = $response->withJson("No tiene privilegios para realizar esta accion.", 200);
                }
                catch (\Exception $e) {
                    $newResponse = $response->withJson($e->getMessage(), 200);
                }
            }
            else
                $newResponse = $response->withJson("El pedido no puede estar vacio.", 200);
        }
        else
            $newResponse = $response->withJson("Los campos 'Nombre de cliente' y 'foto' deben ser obligatorios.", 200);

        return $newResponse;
    }

    public function GetOrderData($order) {
        return $data = PHP_EOL . "------------ DATOS DEL PEDIDO ------------"
            . PHP_EOL . "ID: " . $order->id
            . PHP_EOL . "Mesa: " . $order->idMesa
            . PHP_EOL . "Entrada: " . $order->entrada
            . PHP_EOL . "Plato Principal: " . $order->platoPrincipal
            . PHP_EOL . "Bebida: " . $order->barraCerveza . " " . $order->barraTrago
            . PHP_EOL . "Postre: " . $order->postre
            . PHP_EOL . "Estado del pedido: " . $order->estado;
    }
}

?>