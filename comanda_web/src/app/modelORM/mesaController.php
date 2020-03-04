<?php

namespace App\Models\ORM;

use App\Models\TokenJWT;
use App\Models\Password;
use App\Models\ORM\Mesa;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

define("CLIENTE_EN_ESPERA", "con cliente esperando pedido");
define("CLIENTE_COMIENDO", "con cliente comiendo");
define("CLIENTE_PAGO", "con cliente pagando");
define("MESA_CERRADA", "cerrada");

class MesaController
{
    public static function NewTableRestaurant($idMesa, $idPedido) {
        $findFreeTable = Mesa::find($idMesa);
        $findFreeTable->idPedido = $idPedido;
        $findFreeTable->estado = CLIENTE_EN_ESPERA;
        $findFreeTable->save();
    }

    public function GetTableRestaurantData($request, $response, $args) {
        $newRequest = $request->getQueryParams();

        if (isset($newRequest) && !empty($newRequest['idMesa'])) {
            if (!is_null($findTable = Mesa::where('id', '=', $newRequest['idMesa'])->first())) {
                $newResponse = $response->getBody()->write("------------ DATOS DE LA MESA ------------"
                . PHP_EOL . "ID: " . $findTable->id
                . PHP_EOL . "Mesa: " . $findTable->nombre
                . PHP_EOL . "Pedido: " . $findTable->idPedido
                . PHP_EOL . "Estado: " . $findTable->estado);
            }
            else
                $newResponse = $response->getBody()->write("El numero de mesa no existe.");
        }
        else
            $newResponse = $response->withJson("El campo no puede estar vacio.", 200);

        return $newResponse;
    }

    public function ChangeTableStatusToCustomerEating($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idMesa']) && !empty($newRequest['idPedido'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == MOZO) {
                    if (!is_null($findTable = Mesa::where('id', '=', $newRequest['idMesa'])->where('idPedido', '=', $newRequest['idPedido'])->first())) {
                        if ($findTable->estado == CLIENTE_EN_ESPERA) {
                            if (!self::ChechIfOrderIsReady($newRequest['idPedido'])) {
                                $findTable->estado = CLIENTE_COMIENDO;
                                $findOrder = Pedido::where('id', '=', $newRequest['idPedido'])->first();
                                $findOrder->estado = LISTO_PARA_SERVIR;
                                $findOrder->save();
                                $findTable->save();
                                $newResponse = $response->withJson("Se cambio el estado de la mesa a 'CLIENTE COMIENDO'");
                            }
                            else
                                $newResponse = $response->withJson("No se puede cambiar el estado de la mesa, el pedido aun no esta listo.", 200);
                        }
                        else
                            $newResponse = $response->withJson("No se puede cambiar el estado de la mesa, el mismo es distinto a 'cliente esperando el pedido'.", 200);
                    }
                    else
                        $newResponse = $response->withJson("La mesa y/o el pedido no son correctos. Corrobore la informacion.", 200);
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

    public function ChangeTableStatusToCustomerPaying($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idMesa']) && !empty($newRequest['idPedido']) && !empty($newRequest['importe'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == MOZO) {
                    if (!is_null($findTable = Mesa::where('id', '=', $newRequest['idMesa'])->where('idPedido', '=', $newRequest['idPedido'])->first())) {
                        if ($findTable->estado == CLIENTE_COMIENDO) {
                            $findOrder = Pedido::where('id', '=', $newRequest['idPedido'])->first();
                            $findOrder->importeTotal = $newRequest['importe'];
                            $findTable->estado = CLIENTE_PAGO;
                            $findOrder->save();
                            $findTable->save();
                            $newResponse = $response->withJson("Se cambio el estado de la mesa a 'CLIENTE PAGANDO'");
                        }
                        else
                            $newResponse = $response->withJson("No se puede cambiar el estado de la mesa, el mismo es distinto a 'cliente comiendo'.", 200);
                    }
                    else
                        $newResponse = $response->withJson("La mesa y/o el pedido no son correctos. Corrobore la informacion.", 200);
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

    public function ChangeTableStatusToClose($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idMesa']) && !empty($newRequest['idPedido'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == ADMINISTRADOR) {
                    if (!is_null($findTable = Mesa::where('id', '=', $newRequest['idMesa'])->where('idPedido', '=', $newRequest['idPedido'])->first())) {
                        if ($findTable->estado == CLIENTE_PAGO) {
                            $findTable->estado = MESA_CERRADA;
                            $findTable->save();
                            $newResponse = $response->withJson("Se cambio el estado de la mesa a 'MESA CERRADA'");
                        }
                        else
                            $newResponse = $response->withJson("No se puede realizar la operacion. La mesa se encuentra con alguno de los siguientes estados: CLIENTE ESPERANDO PEDIDO | CLIENTE COMIENDO | MESA CERRADA");
                    }
                    else
                        $newResponse = $response->withJson("La mesa y/o el pedido no son correctos. Corrobore la informacion.", 200);
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

    public function CancelOrder($request, $response, $args) {
        $newRequest = $request->getParsedBody();

        if (isset($newRequest) && !empty($newRequest['token']) && !empty($newRequest['idMesa']) && !empty($newRequest['idPedido'])) {
            try {
                $login = TokenJWT::GetData($newRequest['token']);
                $user = Usuario::where('usuario', '=', $login->usuario)->first();
    
                if ($user->tipo == ADMINISTRADOR || $user->tipo == MOZO) {
                    if (!is_null($findTable = Mesa::where('id', '=', $newRequest['idMesa'])->where('idPedido', '=', $newRequest['idPedido'])->first())) {
                        $cancelOrder = Pedido::find($newRequest['idPedido']);

                        if ($cancelOrder->estado != CANCELADO) {
                            $cancelOrder->estado = CANCELADO;
                            $cancelOrder->save();

                            if (!is_null($cancelDrink = Bebida::where('idPedido', '=', $newRequest['idPedido'])->whereIn('idBebida', [BEER_CODE, DRINK_CODE])->update(['estado' => CANCELADO]))) {
                                ;
                            }
                            if (!is_null($cancelFood = Cocina::find($newRequest['idPedido']))) {
                                $cancelFood->estadoPedido = CANCELADO;
                                $cancelFood->save();
                            }
                            if (!is_null($cancelDessert = Postre::find($newRequest['idPedido']))) {
                                $cancelDessert->estado = CANCELADO;
                                $cancelDessert->save();
                            }
                        
                            $newResponse = $response->withJson("El pedido se ha cancelado.", 200);
                        }
                        else
                            $newResponse = $response->withJson("El pedido ya se encuentra cancelado, no se puede volver a cancelar.", 200);
                    }
                    else
                        $newResponse = $response->withJson("La mesa y/o el pedido no son correctos. Corrobore la informacion.", 200);
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

    private function ChechIfOrderIsReady($orderNumber) {
        $flagOrder = false;
        $flagDrink = false;
        $flagKitchen = false;
        $flagDessert = false;

        if (!is_null($orderDrink = Bebida::where('idPedido', '=', $orderNumber)->first())) {
            if ($orderDrink->estado != LISTO_PARA_SERVIR)
                $flagDrink = true;
        }

        if (!is_null($orderKitchen = Cocina::where('id', '=', $orderNumber)->first())) {
            if ($orderKitchen->estadoPedido != LISTO_PARA_SERVIR)
                $flagKitchen = true;
        }

        if (!is_null($orderDessert = Postre::where('id', '=', $orderNumber)->first())) {
            if ($orderDessert->estado != LISTO_PARA_SERVIR)
                $flagDessert = true;
        }
        
        if ($flagDrink == true || $flagKitchen == true || $flagDessert == true)
            $flag = true;

        return $flagOrder;
    }
}