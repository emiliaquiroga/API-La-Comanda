<?php
//php -S localhost:666 -t app
require __DIR__ . '/../vendor/autoload.php';
require_once '../app/db/ManipularDatos.php';
require_once '../app/controller/UsuariosController.php';
require_once '../app/controller/PedidosController.php';
require_once '../app/controller/ProductosController.php';
require_once '../app/controller/MesaController.php';
require_once '../app/controller/PedidosController.php';
require '../app/middleware/MiddlewareEstadoMesa.php';
require '../app/middleware/MiddlewareEstadoDetallePedido.php';
require_once '../app/middleware/MiddlewareAutenticacion.php';
require_once '../app/middleware/MiddlewareLogIn.php';
require_once '../app/middleware/MiddlewareVerificarTipoUsuario.php';
require_once '../app/utils/AutentificadorJWT.php';
require_once '../app/middleware/MiddlewareVerificarTipoUsuario.php';
require_once '../app/controller/LogInController.php';
require_once '../app/controller/EncuestaController.php';

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

// Load ENV
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load(); // Load environment variables

// Instantiate App
$app = AppFactory::create();
$conexion = ManipularDatos::obtenerInstancia()->obtenerConexion();


$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

$app->get('[/]', function (Request $request, Response $response) {    
$payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
$response->getBody()->write($payload);
return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/CrearUsuario', function (Request $request, Response $response, array $args)  {
$controlador = new UsuarioController();
return $controlador->AsignarUsuario($request, $response, $args);
});

$app->get('/ListarUsuarios', function (Request $request, Response $response, array $args)  {
$controlador = new UsuarioController();
return $controlador->listarUsuario($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio');

$app->put('/ModificarUsuario',function (Request $request, Response $response, array $args)  {
    $controlador = new UsuarioController();
    return $controlador->ModificarUno($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio'); 

$app->delete('/BorrarUsuario', function (Request $request, Response $response, array $args)  {
    $controlador = new UsuarioController();
    return $controlador->BorrarUno($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio');

$app->post('/CrearProducto', function (Request $request, Response $response, array $args)  {
$controlador = new ProductosController();
return $controlador->AsignarProducto($request, $response, $args);
});

$app->get('/ListarProductos', function (Request $request, Response $response, array $args) {
$controlador = new ProductosController();
return $controlador->listarProducto($request, $response, $args);
});

$app->post('/AbrirMesa', function(Request $request, Response $response, array $args) {
$controlador = new MesaController();
return $controlador->AbrirMesa($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarMozo');

$app->get('/ListarMesas', function (Request $request, Response $response, array $args) {
$controlador = new MesaController();
return $controlador->listarMesas($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

$app->post('/AltaPedido', function (Request $request, Response $response, array $args) {
$controlador = new PedidosController();
return $controlador->darAltaPedido($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarMozo');

$app->get('/ListarPedidos', function (Request $request, Response $response, array $args){
$controlador = new PedidosController();
return $controlador->listarPedidos($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocioOMozo'); 

$app->get('/BuscarPorCodigo', function(Request $request, Response $response, array $args){
$controlador = new PedidosController();
return $controlador->obtenerTiempoEstimadoMaximo($request, $response, $args);
});

$app ->get('/ListarPedidosPendientes', function(Request $request, Response $response, array $args){
$controlador = new PedidosController();
return $controlador->listarPedidosPendientes($request, $response, $args);
});

$app->get('/pedidos/pendientes', function(Request $request, Response $response, array $args){
    $controlador = new PedidosController();
    return $controlador->listarPedidosPendientesPorUsuario($request, $response, $args);
})->add(new MiddlewareAutenticacion()); 

$app->get('/ListarPendientePorTipoDeProducto', function(Request $request, Response $response, array $args){
    $controlador = new PedidosController();
    return $controlador->listarPedidosPendientes($request, $response, $args);
})->add(new MiddlewareEstadoDetallePedido());   //-----------------------------------chequear mañana

$app->get('/ListarPedidosListosParaServir', function (Request $request, Response $response, array $args){
    $controlador = new PedidosController();
    return $controlador->listarPedidosListosParaServir($request, $response);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocioOMozo')->add(new MiddlewareAutenticacion());


$app->put('/ModificarEstadoMesa', function (Request $request, Response $response, array $args) {
$controlador = new MesaController();
return $controlador->modificarEstadoMesa($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarMozo')->add(new MiddlewareEstadoMesa())->add(new MiddlewareAutenticacion());

$app->put('/ModificarDetallePedido', function (Request $request, Response $response, array $args) {
$controlador = new PedidosController();
return $controlador->modificarDetallePedido($request, $response, $args);
})->add(new MiddlewareEstadoDetallePedido())->add(new MiddlewareAutenticacion());

$app->put('/ModificarEstadoPedido', function (Request $request, Response $response, array $args) {
    $controlador = new PedidosController();
    return $controlador->modificarEstadoPedidoGeneral($request, $response, $args);
    })->add(new MiddlewareEstadoDetallePedido())->add(new MiddlewareAutenticacion());

$app->post('/cobrarCuenta', function (Request $request, Response $response, array $args){
    $controlador = new PedidosController();
    return $controlador->cobrarCuenta($request, $response);
})->add(\MiddlewareVerificarTipoUsuario::class.':ValidarMozo')->add(new MiddlewareAutenticacion()); 

$app->put('/cerrarMesa', function (Request $request, Response $response, array $args){
    $controlador = new MesaController();
    return $controlador->modificarEstadoMesa($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());


$app->post('/CargarEncuesta', function (Request $request, Response $response, array $args){
    $controlador = new EncuestaController();
    return $controlador->Cargar($request, $response, $args);
});

$app->get('/Encuesta/MejoresComentarios', function (Request $request, Response $response, array $args){
    $controlador = new EncuestaController();
    return $controlador->TraerMejoresComentarios($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

$app->get('/Encuesta/PeoresComentarios', function (Request $request, Response $response, array $args){
    $controlador = new EncuestaController();
    return $controlador->TraerPeoresComentarios($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

$app->get('/Mesas/MasUsada', function (Request $request, Response $response, array $args){
    $controlador = new MesaController();
    return $controlador->obtenerMasUsada($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

$app->get('/Mesas/MenosUsada', function (Request $request, Response $response, array $args){
    $controlador = new MesaController();
    return $controlador->obtenerMenosUsada($request, $response, $args);
})->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());


$app->post('/CargarProductosCSV', function(Request $request, Response $response, array $args){
    $controlador = new ProductosController();
    return $controlador->cargarArchivosCSV($request, $response, $args);
    }); 
    
$app->get('/ExportarProductosCSV', function(Request $request, Response $response, array $args){
    $controlador = new ProductosController();
    return $controlador->exportarArchivosCSV($request, $response, $args);
    });

$app->get('/DescargarPDF', function(Request $request, Response $response, array $args){
    $controlador = new ProductosController();
    return $controlador->exportarArchivosPDF($request, $response, $args);
    });

$app->get('/Estadisticas30Dias', function (Request $request, Response $response, array $args){
    $controlador = new PedidosController();
    return $controlador->PromedioIngresos30Dias($request, $response, $args);
});




$app->group('/jwt', function (RouteCollectorProxy $group){
$group->post('/crearToken', function(Request $request, Response $response, $args){
    $parametros = $request->getParsedBody();
    $usuario = $parametros['usuario'];
    $tipo_usuario =$parametros['tipo_usuario'];


    $datos = array('usuario' => $usuario, 'tipo_usuario' => $tipo_usuario);
    $token = AutentificadorJWT::CrearToken($datos);
    $payload = json_encode(array('jwt' => $token));
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
});
$group->get('/devolverPayLoad', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

        try {
        $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
    } catch (Exception $e) {
        $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json');
    });
    $group->get('/devolverDatos', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
        $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
    } catch (Exception $e) {
        $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json');
    });

    $group->get('/verificarToken', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    $esValido = false;
    try {
        AutentificadorJWT::verificarToken($token);
        $esValido = true;
    } catch (Exception $e) {
        $payload = json_encode(array('error' => $e->getMessage()));
    }

    if ($esValido) {
        $payload = json_encode(array('valid' => $esValido));
    }

    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json');
    });
});

$app->group('/auth', function (RouteCollectorProxy $group) {

    $group->post('/login', function (Request $request, Response $response) {    
        $controller = new LogInController();
        return $controller->iniciarSesion($request, $response);
    })->add(new MiddlewareAutenticacion())->add(new MiddlewareLogIn());
});

$app->run();
