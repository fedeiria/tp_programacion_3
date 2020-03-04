<?php

namespace App\Models\ORM;

use App\Models\TokenJWT;
use App\Models\Password;
use App\Models\ORM\Pedido;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EncuestaController
{
    public function NewSurvey($request, $response, $args) {
        $newRequest = $request->getQueryParams();

        if (isset($newRequest) && !empty($newRequest['idMesa']) && !empty($newRequest['idPedido']) && !empty($newRequest['mesa']) && !empty($newRequest['restaurante']) && !empty($newRequest['mozo']) && !empty($newRequest['cocinero']) && !empty($newRequest['comentarios'])) {
            if ($customerOrder = Pedido::where('id', '=', $newRequest['idPedido'])->where('idMesa', '=', $newRequest['idMesa'])->first()) {
                if (is_null($survey = Encuesta::where('idPedido', '=', $newRequest['idPedido'])->where('idMesa', '=', $newRequest['idMesa'])->first())) {
                    $survey = new Encuesta;
                    $survey->idMesa = $customerOrder->idMesa;
                    $survey->idPedido = $customerOrder->id;
                    $survey->cliente = $customerOrder->nombreCliente;
                    $survey->puntuacionMesa = $newRequest['mesa'];
                    $survey->puntuacionMozo = $newRequest['mozo'];
                    $survey->puntuacionCocinero = $newRequest['cocinero'];
                    $survey->puntuacionRestaurant = $newRequest['restaurante'];
                    $survey->observaciones = $newRequest['comentarios'];
                    $survey->save();
                    $newResponse = $response->withJson("Encuesta enviada con exito. Gracias por responder.", 200);
                }
                else
                    $newResponse = $response->withJson("Usted ya ha completado esta encuesta. Muchas gracias.", 200);
            }
            else
                $newResponse = $response->withJson("Numero de pedido o de mesa incorrecto. Por favor verifique la informacion.", 200);
        }
        else
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }
}

?>