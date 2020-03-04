<?php

use Slim\App;

return function (App $app) {
    $c = $app->getContainer();

    //http://www.slimframework.com/docs/v3/handlers/error.html
    $c['errorHandler'] = function ($c) {
        return function ($request, $response, $exception) use ($c) {
           
            $datos[] = array();
            $datos["error"] = $exception;
            $datos["mensaje"] = "phpErrorHandler";
            $datos["status"] = "500";
            
            return $c->get('renderer')->render($response, 'error.phtml', $datos);
        };
    };

    $c['notFoundHandler'] = function ($c) {
        return function ($request, $response, $error) use ($c) {

            $datos[] = array();
            $datos["error"] = $error;
            $datos["mensaje"] = "Ruta no existente";
            $datos["status"] = "404";

            return $c->get('renderer')->render($response, 'error.phtml', $datos);
        };
    };

    $c['notAllowedHandler'] = function ($c) {
        return function ($request, $response, $methods) use ($c) {

            $datos[] = array();
            $datos["error"] = "";
            $datos["mensaje"] = "Metodo HTTP no permitido";
            $datos["status"] = "405";

            return $c->get('renderer')->render($response, 'error.phtml', $datos);
        };
    };

    $c['phpErrorHandler'] = function ($c) {
        return function ($request, $response, $error) use ($c) {

            $datos[] = array();
            $datos["error"] = $error;
            $datos["mensaje"] = "Ruta no existente";
            $datos["status"] = "501";

            return $c->get('renderer')->render($response, 'error.phtml', $datos);
        };
    };
};

?>
