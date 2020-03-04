<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Models\ORM\mesa;
use App\Models\ORM\admin;
use App\Models\ORM\cocina;
use App\Models\ORM\pedido;
use App\Models\ORM\postre;
use App\Models\ORM\bebida;
use App\Models\ORM\cliente;
use App\Models\ORM\usuario;
use App\Models\ORM\encuesta;
use App\Models\ORM\mesaController;
use App\Models\ORM\cocinaController;
use App\Models\ORM\pedidoController;
use App\Models\ORM\postreController;
use App\Models\ORM\bebidaController;
use App\Models\ORM\clienteController;
use App\Models\ORM\usuarioController;
use App\Models\ORM\encuestaController;

include_once __DIR__ . '/../../src/app/modelORM/mesa.php';
include_once __DIR__ . '/../../src/app/modelORM/admin.php';
include_once __DIR__ . '/../../src/app/modelORM/cocina.php';
include_once __DIR__ . '/../../src/app/modelORM/pedido.php';
include_once __DIR__ . '/../../src/app/modelORM/postre.php';
include_once __DIR__ . '/../../src/app/modelORM/bebida.php';
include_once __DIR__ . '/../../src/app/modelORM/cliente.php';
include_once __DIR__ . '/../../src/app/modelORM/usuario.php';
include_once __DIR__ . '/../../src/app/modelORM/encuesta.php';
include_once __DIR__ . '/../../src/app/modelORM/mesaController.php';
include_once __DIR__ . '/../../src/app/modelORM/loginController.php';
include_once __DIR__ . '/../../src/app/modelORM/cocinaController.php';
include_once __DIR__ . '/../../src/app/modelORM/pedidoController.php';
include_once __DIR__ . '/../../src/app/modelORM/postreController.php';
include_once __DIR__ . '/../../src/app/modelORM/bebidaController.php';
include_once __DIR__ . '/../../src/app/modelORM/usuarioController.php';
include_once __DIR__ . '/../../src/app/modelORM/encuestaController.php';

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/welcome/', function ($request, $response, $args) {
        return $response->getBody()->write("Welcome to APIRest with Slim Framework");
    });

    $app->group('/getLoginByDate', function () {
        $this->post('/', admin::class . ':GetLoginUsersBetween');
    });

    $app->group('/viewOrders', function () {
        $this->post('/', admin::class . ':ViewOrders');
    });

    $app->group('/newAdmin', function () {
        $this->post('/', usuarioController::class . ':NewAdmin');
    });

    $app->group('/newUser', function () {
        $this->post('/', usuarioController::class . ':NewUser');
    });

    $app->group('/modifyUser', function () {
        $this->post('/', usuarioController::class . ':ModifyUser');
    });

    $app->group('/enableUser', function () {
        $this->post('/', usuarioController::class . ':EnableUser');
    });

    $app->group('/disableUser', function () {
        $this->post('/', usuarioController::class . ':DisableUser');
    });

    $app->group('/login', function () {
        $this->post('/', usuarioController::class . ':Login');
    });

    $app->group('/newOrder', function () {
        $this->post('/', pedidoController::class . ':NewOrder');
    });

    $app->group('/pendingPlates', function () {
        $this->post('/', cocinaController::class . ':ViewPendingPlate');
    });

    $app->group('/startPendingPlate', function () {
        $this->post('/', cocinaController::class . ':StartPendingPlate');
    });

    $app->group('/finishPlate', function () {
        $this->post('/', cocinaController::class . ':FinishOrderPlate');
    });

    $app->group('/pendingDrinks', function () {
        $this->post('/', bebidaController::class . ':ViewPendingDrinks');
    });

    $app->group('/startPendingBeer', function () {
        $this->post('/', bebidaController::class . ':StartPendingBeer');
    });

    $app->group('/startPendingDrink', function () {
        $this->post('/', bebidaController::class . ':StartPendingDrink');
    });

    $app->group('/finishBeer', function () {
        $this->post('/', bebidaController::class . ':FinishOrderBeer');
    });

    $app->group('/finishDrink', function () {
        $this->post('/', bebidaController::class . ':FinishOrderDrink');
    });

    $app->group('/pendingDesserts', function () {
        $this->post('/', postreController::class . ':ViewPendingDesserts');
    });

    $app->group('/startPendingDessert', function () {
        $this->post('/', postreController::class . ':StartPendingDessert');
    });

    $app->group('/finishDessert', function () {
        $this->post('/', postreController::class . ':FinishOrderDessert');
    });

    $app->group('/customerEating', function () {
        $this->post('/', mesaController::class . ':ChangeTableStatusToCustomerEating');
    });

    $app->group('/customerPaying', function () {
        $this->post('/', mesaController::class . ':ChangeTableStatusToCustomerPaying');
    });

    $app->group('/closingTable', function () {
        $this->post('/', mesaController::class . ':ChangeTableStatusToClose');
    });

    $app->group('/cancelOrder', function () {
        $this->post('/', mesaController::class . ':CancelOrder');
    });

    $app->group('/customer', function () {
        $this->get('/', cliente::class . ':ViewOrder');
    });

    $app->group('/survey', function () {
        $this->get('/', encuestaController::class . ':NewSurvey');
    });

    $app->group('/viewTableState', function () {
        $this->get('/', mesaController::class . ':GetTableRestaurantData');
    });
}

?>