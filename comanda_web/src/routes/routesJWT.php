<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\TokenJWT;

include_once __DIR__ . '/../app/modelAPI/tokenJWT.php';

return function (App $app) {
$container = $app->getContainer();

$app->group('/JWT', function () {
    $this->get('/', function ($request, $response, $args) {

    $token = null;
    $tokenArray = $request->getHeader('token');

    if ($tokenArray)
        $token = $tokenArray[0];
            
        if ($token)
            $token = ", tu token es =" . $token;
        else
            $token = " no pasaste el token";
        
        return $response->getBody()->write($token);
    });

    $this->get('/crearToken/', function (Request $request, Response $response) {
        $data = array('usuario' => 'test', 'perfil' => 'Administrator', 'alias' => "Slack");
        $token = TokenJWT::CreateToken($data); 
        $newResponse = $response->withJson($token, 200);
        return $newResponse;
    });

    $this->get('/devolverPayLoad/', function (Request $request, Response $response) { 
        $data = array('usuario' => 'test', 'perfil' => 'Administrator', 'alias' => "Slack");
        $token = TokenJWT::CreateToken($data); 
        $payload = TokenJWT::GetPayload($token);
        $newResponse = $response->withJson($payload, 200); 
        return $newResponse;
    });

    $this->get('/devolverDatos/', function (Request $request, Response $response) {
        $data = array('usuario' => 'test', 'perfil' => 'Administrator', 'alias' => "Slack");
        $token = TokenJWT::CreateToken($data);
        $payload = TokenJWT::GetPayload($token);
        $newResponse = $response->withJson($payload, 200); 
        return $newResponse;
    });

    $this->get('/verificarTokenNuevo/', function (Request $request, Response $response) { 
        $datos = array('usuario' => 'test', 'perfil' => 'Administrator', 'alias' => "Slack");
        $token = TokenJWT::CreateToken($data);

        $valid = false;

        try {
          TokenJWT::VerifyToken($token);
          $valid = true;
        }
        catch (Exception $e) {
          // guardar en un log
          echo $e;
        }

        if( $valid) {
            // hago la operacion del metodo 
            echo "ok";
        }

        return $response;
    });

    $this->get('/verificarTokenViejo/', function (Request $request, Response $response) {

        $valid = false;

        try {
          TokenJWT::VerifyToken("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0OTczMTM0NTEsImV4cCI6MTQ5NzMxMzUxMSwiYXVkIjoiMTU3NDQzNzc4MzUzNGEzMDNjYzExY2YzNGI0OTc1ODAxMTNkMDBiOSIsImRhdGEiOnsibm9tYnJlIjoicm9nZWxpbyIsImFwZWxsaWRvIjoiYWd1YSIsImVkYWQiOjQwfSwiYXBwIjoiQVBJIFJFU1QgQ0QgMjAxNyJ9.DZ1LC0BTl5YKHWr7NjWY6r2EDKvVBeOTZiNEv4CXaN0");
          $valid = true;
          
        } catch (Exception $e) {      
          // guardar en un log
          echo $e;
        }

        if( $valid) {
            // hago la operacion del metodo
            echo "ok";
        }

        return $response;
    });

    $this->get('/verificarTokenError/', function (Request $request, Response $response) {    
        
        $valid = false;
        // cambio un caracter de un token valido
        try {
          TokenJWT::VerifyToken("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE0OTczMTM0NTEsImV4cCI6MTQ5NzMxMzUxMSwiYXVkIjoiMTU3NDQzNzc4MzUzNGEzMDNjYzExY2YzNGI0OTc1ODAxMTNkMDBiOSIsImRhdGEiOnsibm9tYnJlIjoicm9nZWxpbyIsImFwZWxsaWRvIjoiYWd1YSIsImVkYWQiOjQwfSwiYXBwIjoiQVBJIFJFU1QgQ0QgMjAxNyJ9.DZ1LC0BTl5YKHWr7NjWY6r2EDKvVBeOTZiNEv4CXaN");
          $valid = true;
          
        } catch (Exception $e) {      
          // guardar en un log
          echo $e;
        }

        if ($valid) {
            // hago la operacion del  metodo
            echo "ok";
        }

        return $response;
    });

    $this->post('/', function (Request $request, Response $response) {    
          
        $tokenArray = $request->getHeader('token');
        $token = $tokenArray[0];
        
        if ($token)
            $response->getBody()->write("El token es: " . $token);
        else
            $response->getBody()->write("No pasaste token en el header");

        return $response;
    });
  });
};