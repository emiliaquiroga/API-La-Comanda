<?php
//php -S localhost:666 -t app
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
require_once '../app/db/ManipularDatos.php';
require_once '../app/controller/Bartender.php';
require_once '../app/controller/UsuariosController.php';
require_once '../app/controller/PedidosController.php';
require_once '../app/controller/ProductosController.php';
require_once '../app/controller/MesaController.php';
require_once '../app/controller/PedidosController.php';

// Load ENV
//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
//$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();
$conexion = ManipularDatos::obtenerInstancia()->obtenerConexion();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();


$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/CrearUsuario', function (Request $request, Response $response, array $args) use($conexion) {
    $controlador = new UsuarioController($conexion);
    return $controlador->AsignarUsuario($request, $response, $args);
});
$app->get('/ListarUsuarios', function (Request $request, Response $response, array $args) use($conexion) {
    $controlador = new UsuarioController($conexion);
    return $controlador->listarUsuario($request, $response, $args);
});

$app->post('/CrearProducto', function (Request $request, Response $response, array $args) use($conexion) {
    $controlador = new ProductosController($conexion);
    return $controlador->AsignarProducto($request, $response, $args);
});
$app->get('/ListarProductos', function (Request $request, Response $response, array $args) use($conexion) {
    $controlador = new ProductosController($conexion);
    return $controlador->listarProducto($request, $response, $args);
});

$app->post('/AbrirMesa', function(Request $request, Response $response, array $args) use($conexion){
    $controlador = new MesaController($conexion);
    return $controlador->AbrirMesa($request, $response, $args);
});

$app->get('/ListarMesas', function (Request $request, Response $response, array $args) use($conexion){
    $controlador = new MesaController($conexion);
    return $controlador->listarMesas($request, $response, $args);
});

$app->post('/AltaPedido', function (Request $request, Response $response, array $args) use($conexion){
    $controlador = new PedidosController($conexion);
    return $controlador->altaPedido($request, $response, $args);
});

$app->get('/ListarPedidos', function (Request $request, Response $response, array $args) use($conexion){
    $controlador = new PedidosController($conexion);
    return $controlador->listarPedidos($request, $response, $args);
});

$app->run();


