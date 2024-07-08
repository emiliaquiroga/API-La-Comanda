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

$app = AppFactory::create();
$conexion = ManipularDatos::obtenerInstancia()->obtenerConexion();


$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();


// Rutas
$app->get('[/]', function (Request $request, Response $response) {
    $payload = json_encode(["mensaje" => "Slim Framework 4 PHP"]);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Usuario
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->post('/crear', function (Request $request, Response $response, array $args) {
        $controlador = new UsuarioController();
        return $controlador->AsignarUsuario($request, $response, $args);
    });

    $group->get('/listar', function (Request $request, Response $response, array $args) {
        $controlador = new UsuarioController();
        return $controlador->listarUsuario($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio');

    $group->put('/modificar', function (Request $request, Response $response, array $args) {
        $controlador = new UsuarioController();
        return $controlador->ModificarUno($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio');

    $group->delete('/borrar', function (Request $request, Response $response, array $args) {
        $controlador = new UsuarioController();
        return $controlador->BorrarUno($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio');
});

// Productos
$app->group('/productos', function (RouteCollectorProxy $group) {
    $group->post('/crear', function (Request $request, Response $response, array $args) {
        $controlador = new ProductosController();
        return $controlador->AsignarProducto($request, $response, $args);
    });

    $group->get('/listar', function (Request $request, Response $response, array $args) {
        $controlador = new ProductosController();
        return $controlador->listarProducto($request, $response, $args);
    });

    $group->post('/cargarCSV', function(Request $request, Response $response, array $args) {
        $controlador = new ProductosController();
        return $controlador->cargarArchivosCSV($request, $response, $args);
    });

    $group->get('/exportarCSV', function(Request $request, Response $response, array $args) {
        $controlador = new ProductosController();
        return $controlador->exportarArchivosCSV($request, $response, $args);
    });

    $group->get('/descargarPDF', function(Request $request, Response $response, array $args) {
        $controlador = new ProductosController();
        return $controlador->exportarArchivosPDF($request, $response, $args);
    });
});

// Pedidos
$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->post('/alta', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->darAltaPedido($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarMozo');

    $group->get('/listar', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->listarPedidos($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocioOMozo');

    $group->get('/buscarPorCodigo', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->obtenerTiempoEstimadoMaximo($request, $response, $args);
    });

    $group->get('/pendientes', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->listarPedidosPendientesPorUsuario($request, $response, $args);
    })->add(new MiddlewareAutenticacion());

    $group->get('/pendientesPorTipoDeProducto', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->listarPedidosPendientes($request, $response, $args);
    })->add(new MiddlewareEstadoDetallePedido());

    $group->get('/listosParaServir', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->listarPedidosListosParaServir($request, $response);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocioOMozo')->add(new MiddlewareAutenticacion());

    $group->put('/modificarDetalle', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->modificarDetallePedido($request, $response, $args);
    })->add(new MiddlewareEstadoDetallePedido())->add(new MiddlewareAutenticacion());

    $group->put('/modificarEstado', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->modificarEstadoPedidoGeneral($request, $response, $args);
    })->add(new MiddlewareEstadoDetallePedido())->add(new MiddlewareAutenticacion());

    $group->post('/cobrarCuenta', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->cobrarCuenta($request, $response);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarMozo')->add(new MiddlewareAutenticacion());

    $group->get('/estadisticas30Dias', function (Request $request, Response $response, array $args) {
        $controlador = new PedidosController();
        return $controlador->PromedioIngresos30Dias($request, $response, $args);
    });
});

// Mesas
$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->post('/abrir', function (Request $request, Response $response, array $args) {
        $controlador = new MesaController();
        return $controlador->AbrirMesa($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarMozo');

    $group->get('/listar', function (Request $request, Response $response, array $args) {
        $controlador = new MesaController();
        return $controlador->listarMesas($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

    $group->put('/modificarEstado', function (Request $request, Response $response, array $args) {
        $controlador = new MesaController();
        return $controlador->modificarEstadoMesa($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarMozo')->add(new MiddlewareEstadoMesa())->add(new MiddlewareAutenticacion());

    $group->put('/cerrar', function (Request $request, Response $response, array $args) {
        $controlador = new MesaController();
        return $controlador->modificarEstadoMesa($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

    $group->get('/masUsada', function (Request $request, Response $response, array $args) {
        $controlador = new MesaController();
        return $controlador->obtenerMasUsada($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

    $group->get('/menosUsada', function (Request $request, Response $response, array $args) {
        $controlador = new MesaController();
        return $controlador->obtenerMenosUsada($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());
});

// Encuesta
$app->group('/encuestas', function (RouteCollectorProxy $group) {
    $group->post('/cargar', function (Request $request, Response $response, array $args) {
        $controlador = new EncuestaController();
        return $controlador->Cargar($request, $response, $args);
    });

    $group->get('/mejoresComentarios', function (Request $request, Response $response, array $args) {
        $controlador = new EncuestaController();
        return $controlador->TraerMejoresComentarios($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());

    $group->get('/peoresComentarios', function (Request $request, Response $response, array $args) {
        $controlador = new EncuestaController();
        return $controlador->TraerPeoresComentarios($request, $response, $args);
    })->add(\MiddlewareVerificarTipoUsuario::class . ':ValidarSocio')->add(new MiddlewareAutenticacion());
});

//LogIn
$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', function (Request $request, Response $response) {
        $controller = new LogInController();
        return $controller->iniciarSesion($request, $response);
    })->add(new MiddlewareLogIn());
});

$app->run();