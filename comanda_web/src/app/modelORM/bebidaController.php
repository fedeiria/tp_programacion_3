<?php

namespace App\Models\ORM;

use App\Models\TokenJWT;
use App\Models\Password;
use App\Models\ORM\Bebida;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

include_once __DIR__ . '../../modelAPI/password.php';
include_once __DIR__ . '../../modelAPI/tokenJWT.php';
include_once __DIR__ . '../../modelAPI/IApiController.php';

define ("BEER_CODE", 1000);
define ("DRINK_CODE", 1001);

class BebidaController
{
    public function StartPendingBeer($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido']) && !empty($newRequest['tiempoDePreparacion'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == CERVECERO) {
                    if ($getPendingBeer = Bebida::where('idPedido', '=', $newRequest['idPedido'])->where('idBebida', '=', BEER_CODE)->first()) {
                        $getPendingOrder = Pedido::find($newRequest['idPedido']);
                        if ($getPendingOrder->estado == PENDIENTE) {
                            $getPendingOrder->estado = EN_PREPARACION;
                            $getPendingOrder->iniciadoPor = $user->nombre;
                            $getPendingOrder->initiated_at = new \DateTime();
                            $getPendingBeer->estado = EN_PREPARACION;
                            $getPendingBeer->usuario = $user->usuario;
                            $getPendingBeer->initiated_at = $getPendingOrder->initiated_at;
                            $getPendingBeer->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                            $getPendingBeer->save();
                            $getPendingOrder->save();
                            self::OrderRemainingTime($getPendingOrder->id, $getPendingBeer->tiempoDePreparacion);
                            $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetDrinkData($getPendingBeer), 200);
                        }
                        else if ($getPendingOrder->estado == EN_PREPARACION) {
                            if ($getPendingBeer->estado == PENDIENTE) {
                                $getPendingBeer->usuario = $user->usuario;
                                $getPendingBeer->estado = EN_PREPARACION;
                                $getPendingBeer->initiated_at = new \DateTime();
                                $getPendingBeer->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                                $getPendingBeer->save();
                                $getPendingOrder->save();
                                self::OrderRemainingTime($getPendingOrder->id, $getPendingBeer->tiempoDePreparacion);
                                $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetDrinkData($getPendingBeer), 200);
                            }
                            else if ($getPendingBeer->estado == EN_PREPARACION) {
                                $newResponse = $response->getBody()->write("El pedido ya se encuentra en preparacion." . PHP_EOL . self::GetDrinkData($getPendingBeer), 200);
                            }
                            else
                                $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetDrinkData($getPendingBeer), 200);
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

    public function FinishOrderBeer($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == CERVECERO) {
                    if ($getPendingBeer = Bebida::where('idPedido', '=', $newRequest['idPedido'])->where('idBebida', '=', BEER_CODE)->first()) {
                        if ($getPendingBeer->estado == PENDIENTE) {
                            $newResponse = $response->getBody()->write("No se puede finalizar el pedido porque aun se encuentra 'pendiente'." . PHP_EOL . self::GetDrinkData($getPendingBeer), 200);
                        }
                        else if ($getPendingBeer->estado == EN_PREPARACION) {
                            $getPendingBeer->estado = LISTO_PARA_SERVIR;
                            $getPendingBeer->save();
                            $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetDrinkData($getPendingBeer), 200);
                        }
                        else
                            $newResponse = $response->getBody()->write("El pedido se encuentra finalizado." . PHP_EOL . self::GetDrinkData($getPendingBeer), 200);
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

    public function StartPendingDrink($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido']) && !empty($newRequest['tiempoDePreparacion'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == BARTENDER) {
                    if ($getPendingDrink = Bebida::where('idPedido', '=', $newRequest['idPedido'])->where('idBebida', '=', DRINK_CODE)->first()) {
                        $getPendingOrder = Pedido::find($newRequest['idPedido']);
                        if ($getPendingOrder->estado == PENDIENTE) {
                            $getPendingOrder->estado = EN_PREPARACION;
                            $getPendingOrder->iniciadoPor = $user->nombre;
                            $getPendingOrder->initiated_at = new \DateTime();
                            $getPendingDrink->usuario = $user->usuario;
                            $getPendingDrink->initiated_at = $getPendingOrder->initiated_at;
                            $getPendingDrink->estado = EN_PREPARACION;
                            $getPendingDrink->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                            $getPendingDrink->save();
                            $getPendingOrder->save();
                            self::OrderRemainingTime($getPendingOrder->id, $getPendingDrink->tiempoDePreparacion);
                            $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetDrinkData($getPendingDrink), 200);
                        }
                        else if ($getPendingOrder->estado == EN_PREPARACION) {
                            if ($getPendingDrink->estado == PENDIENTE) {
                                $getPendingDrink->usuario = $user->usuario;
                                $getPendingDrink->estado = EN_PREPARACION;
                                $getPendingDrink->initiated_at = new \DateTime();
                                $getPendingDrink->tiempoDePreparacion = $newRequest['tiempoDePreparacion'];
                                $getPendingDrink->save();
                                $getPendingOrder->save();
                                self::OrderRemainingTime($getPendingOrder->id, $getPendingDrink->tiempoDePreparacion);
                                $newResponse = $response->getBody()->write("Pedido en preparacion." . PHP_EOL . self::GetDrinkData($getPendingDrink), 200);
                            }
                            else if ($getPendingDrink->estado == EN_PREPARACION) {
                                $newResponse = $response->getBody()->write("El pedido ya se encuentra en preparacion." . PHP_EOL . self::GetDrinkData($getPendingDrink), 200);
                            }
                            else
                                $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetDrinkData($getPendingDrink), 200);
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

    public function FinishOrderDrink($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idPedido'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == BARTENDER) {
                    if ($getPendingDrink = Bebida::where('idPedido', '=', $newRequest['idPedido'])->where('idBebida', '=', DRINK_CODE)->first()) {
                        if ($getPendingDrink->estado == PENDIENTE) {
                            $newResponse = $response->getBody()->write("No se puede finalizar el pedido porque aun se encuentra 'pendiente'." . PHP_EOL . self::GetDrinkData($getPendingDrink), 200);
                        }
                        else if ($getPendingDrink->estado == EN_PREPARACION) {
                            $getPendingDrink->estado = LISTO_PARA_SERVIR;
                            $getPendingDrink->save();
                            $newResponse = $response->getBody()->write("Pedido listo para servir." . PHP_EOL . self::GetDrinkData($getPendingDrink), 200);
                        }
                        else
                            $newResponse = $response->getBody()->write("El pedido se encuentra finalizado." . PHP_EOL . self::GetDrinkData($getPendingDrink), 200);
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

    public function ViewPendingDrinks($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
                $flag = false;

                if ($user->tipo == CERVECERO) {
                    $getPendingBeer = Bebida::where('idBebida', '=', BEER_CODE)->get();

                    foreach($getPendingBeer as $beer) {
                        if ($beer->estado == PENDIENTE) {
                            $newResponse = $response->getBody()->write(self::GetBeerData($beer) . PHP_EOL, 200);
                            $flag = true;
                        }
                    }

                    if (!$flag) {
                        $newResponse = $response->withJson("No hay pedidos pendientes.", 200);
                    }
                }
                else if ($user->tipo == BARTENDER) {
                    $getPendingDrink = Bebida::where('idBebida', '=', DRINK_CODE)->get();

                    foreach($getPendingDrink as $drink) {
                        if ($drink->estado == PENDIENTE) {
                            $newResponse = $response->getBody()->write(self::GetDrinkData($drink) . PHP_EOL, 200);
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

    public function NewDrink($id, $beer, $drink) {
        if (!empty($beer)) {
            $newBeer = new Bebida;
            $newBeer->idPedido = $id;
            $newBeer->idBebida = BEER_CODE;
            $newBeer->pedido = $beer;
            $newBeer->estado = PENDIENTE;
            $newBeer->save();
        }
        if (!empty($drink)) {
            $newDrink = new Bebida;
            $newDrink->idPedido = $id;
            $newDrink->idBebida = DRINK_CODE;
            $newDrink->pedido = $drink;
            $newDrink->estado = PENDIENTE;
            $newDrink->save();
        }
    }

    public function OrderRemainingTime($orderId, $orderTime) {
        $order = Pedido::where('id', '=', $orderId)->first();
        $order->tiempoDePreparacion += $orderTime;
        $order->save();
    }

    private function GetBeerData($order) {
        return $data = PHP_EOL . "------------ DATOS DEL PEDIDO ------------"
        . PHP_EOL . "ID: " . $order->idPedido
        . PHP_EOL . "Pedido: " . $order->pedido 
        . PHP_EOL . "Estado del pedido: " . $order->estado
        . PHP_EOL . "Tiempo de preparacion: " . $order->tiempoDePreparacion . " minutos"
        . PHP_EOL . "Preparado por: " . $order->usuario;
    }

    private function GetDrinkData($order) {
        return $data = PHP_EOL . "------------ DATOS DEL PEDIDO ------------"
        . PHP_EOL . "ID: " . $order->idPedido
        . PHP_EOL . "Pedido: " . $order->pedido 
        . PHP_EOL . "Estado del pedido: " . $order->estado
        . PHP_EOL . "Tiempo de preparacion: " . $order->tiempoDePreparacion . " minutos"
        . PHP_EOL . "Preparado por: " . $order->usuario;
    }
}