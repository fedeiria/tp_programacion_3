<?php

namespace App\Models\ORM;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

date_default_timezone_set('America/Argentina/Buenos_Aires');

class Cliente
{
    public function ViewOrder($request, $response, $args) {
        $newRequest = $request->getQueryParams();

        if (isset($newRequest) && !empty($newRequest['idPedido']) && !empty($newRequest['idMesa'])) {
            if ($getOrder = Pedido::where('id', '=', $newRequest['idPedido'])->where('idMesa', '=', $newRequest['idMesa'])->first()) {
                if ($getOrder->estado != PENDIENTE)
                    $newResponse = $response->getBody()->write(self::GetClientOrderData($getOrder), 200);
                else
                    $newResponse = $response->withJson("Su pedido se encuentra pendiente de preparacion.", 200);
            }
            else
                $newResponse = $response->withJson("Los datos del pedido y/o la mesa no son correctos. Corrobore la informacion.", 200);
        }
        else
            $newResponse = $response->withJson("Debe completar todos los campos.", 200);

        return $newResponse;
    }

    private function GetClientOrderData($order) {
        return $data = "------------ ESTADO DEL PEDIDO ------------"
        . PHP_EOL . "Nombre: " . $order->nombreCliente
        . PHP_EOL . "Estado del pedido: " . $order->estado
        . PHP_EOL . "Tiempo de preparacion: " . $order->tiempoDePreparacion . " minutos"
        . PHP_EOL . self::OrderTime($order->initiated_at, $order->updated_at, $order->tiempoDePreparacion, $order->estado)
        . PHP_EOL . "Atendido por: " . $order->usuario;
    }

    private function OrderTime($orderStart, $orderUpdated, $preparationTime, $orderState) {
        // objeto hora actual
        $now = new \DateTime();

        // calculo la diferencia entre la hora del pedido y la hora actual
        $timeInterval = $orderUpdated->diff($now);
        $timeInterval->format('%H:%I:%S');

        // convierto a timestamp el timeInterval
        $remaining = strtotime($timeInterval->format('%H:%I:%S'));

        // sumo el tiempo estimado + el tiempo de ingreso del pedido y lo convierto a timestamp
        $sumEstimatedTime = strtotime('+' . (string)$preparationTime . 'minutes', strtotime($orderUpdated));

        // este "tiempo estimado de preparacion" es el que muestro cuando el pedido esta "finalizado"
        $endEstimatedTime = strtotime('+' . (string)$preparationTime . 'minutes', strtotime($orderStart));

        // doy formato de fecha a los timestamp 'remaining', 'endEstimatedTime' y 'sumEstimatedTime'
        $timeElapsed = date('H:i:s', $remaining);
        $estimatedEndTime = date('H:i:s', $endEstimatedTime);
        $estimatedOrderTime = date('H:i:s', $sumEstimatedTime);
        
        // objeto hora actual
        $sumRemainingOrder = new \DateTime();

        // paso el tiempo estimado de timestamp a tipo fecha
        $sumRemainingOrder->setTimestamp($sumEstimatedTime);

        // calculo la diferencia entre el tiempo estimado del pedido y la hora actual
        $timeInterval2 = $sumRemainingOrder->diff($now);

        // calculo la diferencia entre la hora en que se inicio el pedido y la hora en que se finalizo
        $timeInterval3 = new \DateTime($orderStart);
        $timeInterval4 = new \DateTime($orderUpdated);
        $difference = $timeInterval4->diff($timeInterval3);
        $formatTime = strtotime($difference->format('%H:%I:%S'));
        $totalTime = date('H:i:s', $formatTime);

        if ($orderState == PENDIENTE) {
            return "Hora estimada de finalizacion del pedido: -"
            . PHP_EOL . "Tiempo transcurrido: -"
            . PHP_EOL . "Tiempo restante: -";
        }
        else if ($orderState == LISTO_PARA_SERVIR) {
            return "Hora estimada de finalizacion del pedido: " . $estimatedEndTime
            . PHP_EOL . "Tiempo transcurrido: " . $totalTime
            . PHP_EOL . "Tiempo restante: 00:00:00";
        }
        else {
            return "Hora estimada de finalizacion del pedido: " . $estimatedOrderTime
            . PHP_EOL . "Tiempo transcurrido: " . $timeElapsed
            . PHP_EOL . "Tiempo restante: " . $timeInterval2->format('%H:%I:%S');
        }
    }
}

?>