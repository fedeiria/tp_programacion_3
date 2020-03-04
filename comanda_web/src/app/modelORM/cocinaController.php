<?php

namespace App\Models\ORM;

use App\Models\TokenJWT;
use App\Models\Password;
use App\Models\ORM\Cocina;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '../../modelAPI/password.php';
include_once __DIR__ . '../../modelAPI/tokenJWT.php';
include_once __DIR__ . '../../modelAPI/IApiController.php';

class CocinaController
{
    public function StartPendingPlate($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido']) && !empty($newRequest['tiempoDePreparacion'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == COCINERO) {
                    if (!is_null($getPendingPlate = Cocina::where('id', '=', $newRequest['idPedido'])->first())) {
                        $getPendingOrder = Pedido::find($newRequest['idPedido']);
                        if ($getPendingOrder->estado == PENDIENTE) {
                            $getPendingOrder->estado = EN_PREPARACION;
                            $getPendingOrder->iniciadoPor = $user->nombre;
                            $getPendingOrder->initiated_at = new \DateTime();
                            $getPendingPlate->estadoPedido = EN_PREPARACION;
                            $getPendingPlate->usuario = $user->usuario;
                            $getPendingPlate->initiated_at = $getPendingOrder->initiated_at;
                            $getPendingPlate->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                            $getPendingPlate->save();
                            $getPendingOrder->save();
                            self::OrderRemainingTime($getPendingOrder->id, $getPendingPlate->tiempoDePreparacion);
                            $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetPlateData($getPendingPlate), 200);
                        }
                        else if ($getPendingOrder->estado == EN_PREPARACION) {
                            if ($getPendingPlate->estadoPedido == PENDIENTE) {
                                $getPendingPlate->usuario = $user->usuario;
                                $getPendingPlate->estadoPedido = EN_PREPARACION;
                                $getPendingPlate->initiated_at = new \DateTime();
                                $getPendingPlate->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                                $getPendingPlate->save();
                                $getPendingOrder->save();
                                self::OrderRemainingTime($getPendingOrder->id, $getPendingPlate->tiempoDePreparacion);
                                $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetPlateData($getPendingPlate), 200);
                            }
                            else if ($getPendingPlate->estadoPedido == EN_PREPARACION) {
                                $newResponse = $response->getBody()->write("El pedido ya se encuentra en preparacion." . PHP_EOL . self::GetPlateData($getPendingPlate), 200);
                            }
                            else
                                $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetPlateData($getPendingPlate), 200);
                        }
                        else
                            $newResponse = $response->getBody()->write("El pedido se encuentra finalizado." . PHP_EOL . PedidoController::GetOrderData($getPendingOrder), 200);
                    }
                    else
                        $newResponse = $response->withJson("No se encontro el numero de pedido.", 200);
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

    public function FinishOrderPlate($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == COCINERO) {
                    if ($getPendingPlate = Cocina::where('id', '=', $newRequest['idPedido'])->first()) {
                        if ($getPendingPlate->estadoPedido == PENDIENTE) {
                            $newResponse = $response->getBody()->write("No se puede finalizar el pedido porque aun se encuentra 'pendiente'." . PHP_EOL . self::GetPlateData($getPendingPlate), 200);
                        }
                        else if ($getPendingPlate->estadoPedido == EN_PREPARACION) {
                            $getPendingPlate->estadoPedido = LISTO_PARA_SERVIR;
                            $getPendingPlate->save();
                            $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetPlateData($getPendingPlate), 200);
                        }
                        else
                            $newResponse = $response->getBody()->write("El pedido se encuentra finalizado." . PHP_EOL . self::GetPlateData($getPendingPlate), 200);
                    }
                    else
                        $newResponse = $response->withJson("No se encontro el numero de pedido.", 200);
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

    public function ViewPendingPlate($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
                $flag = false;

                if ($user->tipo == COCINERO) {
                    $getPendingPlates = Cocina::all();

                    foreach($getPendingPlates as $plate) {
                        if ($plate->estadoPedido == PENDIENTE) {
                            $newResponse = $response->getBody()->write(self::GetPlateData($plate) . PHP_EOL, 200);
                            $flag = true;
                        }
                    }

                    if (!$flag) {
                        $newResponse = $response->withJson("No hay pedidos pendientes.", 200);
                    }
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

    public static function NewPlate($id, $entrada, $principal) {
        if (!empty($entrada) || !empty($principal)) {
            $newPlate = new Cocina;
            $newPlate->id = $id;
            $newPlate->entrada = $entrada;
            $newPlate->principal = $principal;
            $newPlate->estadoPedido = PENDIENTE;
            $newPlate->save();
        }
    }

    public function OrderRemainingTime($orderId, $orderTime) {
        $order = Pedido::where('id', '=', $orderId)->first();
        $order->tiempoDePreparacion += $orderTime;
        $order->save();
    }

    private function GetPlateData($order) {
        return $data = PHP_EOL . "------------ DATOS DEL PEDIDO ------------"
        . PHP_EOL . "ID Pedido: " . $order->id
        . PHP_EOL . "Entrada: " . $order->entrada
        . PHP_EOL . "Plato Principal: " . $order->principal
        . PHP_EOL . "Estado del pedido: " . $order->estadoPedido
        . PHP_EOL . "Tiempo de preparacion: " . $order->tiempoDePreparacion . " minutos"
        . PHP_EOL . "Preparado por: " . $order->usuario;
    }
}