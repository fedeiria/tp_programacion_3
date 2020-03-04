<?php

namespace App\Models\ORM;

use App\Models\TokenJWT;
use App\Models\Password;
use App\Models\ORM\Postre;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '../../modelAPI/password.php';
include_once __DIR__ . '../../modelAPI/tokenJWT.php';
include_once __DIR__ . '../../modelAPI/IApiController.php';

class PostreController
{
    public function StartPendingDessert($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido']) && !empty($newRequest['tiempoDePreparacion'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == CANDYBAR) {
                    if ($getPendingDessert = Postre::where('id', '=', $newRequest['idPedido'])->first()) {
                        $getPendingOrder = Pedido::find($newRequest['idPedido']);
                        if ($getPendingOrder->estado == PENDIENTE) {
                            $getPendingOrder->estado = EN_PREPARACION;
                            $getPendingOrder->iniciadoPor = $user->nombre;
                            $getPendingOrder->initiated_at = new \DateTime();
                            $getPendingDessert->estado = EN_PREPARACION;
                            $getPendingDessert->usuario = $user->usuario;
                            $getPendingDessert->initiated_at = $getPendingOrder->initiated_at;
                            $getPendingDessert->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                            $getPendingDessert->save();
                            $getPendingOrder->save();
                            self::OrderRemainingTime($getPendingOrder->id, $getPendingDessert->tiempoDePreparacion);
                            $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetDessertData($getPendingDessert), 200);
                        }
                        else if ($getPendingOrder->estado == EN_PREPARACION) {
                            if ($getPendingDessert->estado == PENDIENTE) {
                                $getPendingDessert->usuario = $user->usuario;
                                $getPendingDessert->estado = EN_PREPARACION;
                                $getPendingDessert->initiated_at = new \DateTime();
                                $getPendingDessert->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                                $getPendingDessert->save();
                                $getPendingOrder->save();
                                self::OrderRemainingTime($getPendingOrder->id, $getPendingDessert->tiempoDePreparacion);
                                $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetDessertData($getPendingDessert), 200);
                            }
                            else if ($getPendingDessert->estado == EN_PREPARACION) {
                                $newResponse = $response->getBody()->write("El pedido ya se encuentra en preparacion." . PHP_EOL . self::GetDessertData($getPendingDessert), 200);
                            }
                            else
                                $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetDessertData($getPendingDessert), 200);
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

    public function FinishOrderDessert($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == CANDYBAR) {
                    if ($getPendingDessert = Postre::where('id', '=', $newRequest['idPedido'])->first()) {
                        if ($getPendingDessert->estado == PENDIENTE) {
                            $newResponse = $response->getBody()->write("No se puede finalizar el pedido porque aun se encuentra 'pendiente'." . PHP_EOL . self::GetDessertData($getPendingDessert), 200);
                        }
                        else if ($getPendingDessert->estado == EN_PREPARACION) {
                            $getPendingDessert->estado = LISTO_PARA_SERVIR;
                            $getPendingDessert->save();
                            $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetDessertData($getPendingDessert), 200);
                        }
                        else
                            $newResponse = $response->getBody()->write("El pedido se encuentra finalizado." . PHP_EOL . self::GetDessertData($getPendingDessert), 200);
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

    public function ViewPendingDesserts($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
                $flag = false;

                if ($user->tipo == CANDYBAR) {
                    $getPendingDesserts = Postre::all();

                    foreach($getPendingDesserts as $dessert) {
                        if ($dessert->estado == PENDIENTE) {
                            $newResponse = $response->getBody()->write(self::GetDessertData($dessert) . PHP_EOL, 200);
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

    public static function NewDessert($id, $pedido) {
        if (!empty($pedido)) {
            $newDessert = new Postre;
            $newDessert->id = $id;
            $newDessert->pedido = $pedido;
            $newDessert->estado = PENDIENTE;
            $newDessert->save();
        }
    }

    public function OrderRemainingTime($orderId, $orderTime) {
        $order = Pedido::where('id', '=', $orderId)->first();
        $order->tiempoDePreparacion += $orderTime;
        $order->save();
    }

    private function GetDessertData($order) {
        return $data = PHP_EOL . "------------ DATOS DEL PEDIDO ------------"
        . PHP_EOL . "ID: " . $order->id
        . PHP_EOL . "Pedido: " . $order->pedido
        . PHP_EOL . "Estado del pedido: " . $order->estado
        . PHP_EOL . "Tiempo de preparacion: " . $order->tiempoDePreparacion . " minutos"
        . PHP_EOL . "Preparado por: " . $order->usuario;
    }
}