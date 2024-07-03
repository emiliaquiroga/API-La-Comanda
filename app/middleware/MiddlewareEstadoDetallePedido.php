<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Slim\Exception\HttpBadRequestException;
require_once '../app/utils/AutentificadorJWT.php';

class MiddlewareEstadoDetallePedido{
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response {
        try {
            $parametros = $request->getParsedBody();
            //$usuario_modificante = $request->getAttribute('usuario_modificante');
            $id_pedido = $parametros['id_pedido'];
            $id_producto = $parametros['id_producto'];
            $estado = $parametros['estado'];
            $tiempo_estimado = $parametros['tiempo_estimado'];
            $tipo_usuario = $this->obtenerTipoUsarioConToken($request);
            $sector = $this->verificarSectorProducto($id_producto);
            $response = new Response();

            switch ($tipo_usuario) {
                case 'bartender':
                    if ($sector === 'tragosVinos') {
                        $response = $handler->handle($request);
                    } else {
                        $payload = json_encode(array("mensaje" => "Los bartenders pueden acceder a gaseosa, tragos y/o vino."));
                        $response->getBody()->write($payload);
                    }
                    break;
                case 'cervecero':
                    if ($sector === 'cerveza') {
                        $response = $handler->handle($request);
                    } else {
                        $payload = json_encode(array("mensaje" => "Los cerveceros pueden acceder a cervezas unicamente."));
                        $response->getBody()->write($payload);
                    }
                    break;
                case 'cocinero':
                    if ($sector === 'cocina' || $sector === 'candybar') {
                        $response = $handler->handle($request);
                    } else {
                        $payload = json_encode(array("mensaje" => "Los cocineros solo pueden acceder a la cocina y al candybar."));
                        $response->getBody()->write($payload);
                    }
                    break;
                case 'socio':
                    $response = $handler->handle($request);
                    break;
                default:
                    $payload = json_encode(array("mensaje" => "Error inesperado."));
                    $response->getBody()->write($payload);
                    break;
            }
        } catch (Exception $e) {

            $payload = json_encode(array('error' => 'Token inválido o expirado'));
            $response = new Response();
            $response->getBody()->write($payload);
            $response = $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function obtenerTipoUsarioConToken(Request $request): string {
        $usuario_modificante = $request->getAttribute('usuario_modificante');
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $payload = AutentificadorJWT::ObtenerPayLoad($token);

        return $payload->data->tipo_usuario;
    }

    private function verificarSectorProducto(int $id_producto): string {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consultaProducto = $objAccesoDatos->prepararConsulta("SELECT sector FROM productos WHERE id = :id");
        $consultaProducto->bindValue(':id', $id_producto, PDO::PARAM_INT);
        $consultaProducto->execute();
        $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            throw new HttpBadRequestException($request, "El producto no está disponible en la carta.");
        }

        return $producto['sector'];
    }

}
