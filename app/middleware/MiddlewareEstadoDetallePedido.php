<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Exception\HttpBadRequestException;

class MiddlewareEstadoDetallePedido{
    public function __invoke(Request $request, RequestHandler $handler): Response{
        $parametros = $request->getParsedBody();
        $usuario_modificante = $parametros['usuario_modificante'];
        $id_pedido = $parametros['id_pedido'];
        $id_producto = $parametros['id_producto'];
        $estado = $parametros['estado'];
        $tiempo_estimado = $parametros['tiempo_estimado'];

        $tipo_usuario = $this->verificarUsuario($usuario_modificante);
        $tipo_producto = $this->verificarTipoProducto($id_producto);
        $nombre_producto = $this->obtenerNombreProducto($id_producto);

        $response = new Response();

        switch($tipo_usuario):
            case 'bartender':
                if($tipo_producto === 'bebida' && $nombre_producto !== 'cerveza')
                    {
                        $response = $handler->handle($request);
                    }else{
                        $response = new Response();
                        $payload = json_encode(array("mensaje" => "Los bartenders no pueden modificar cervezas! Derive con cervecero!"));
                        $response->getBody()->write($payload);
                    }
                break;
            case 'cervecero':
                if($tipo_producto === 'bebida' && $nombre_producto === 'cerveza'){
                    $response = $handler->handle($request);
                }else{
                    $response = new Response();
                    $payload = json_encode(array("mensaje" => "Solo los cerveceros pueden cambiar el estado de las cervezas!"));
                    $response->getBody()->write($payload);
                }
                break;
            case 'cocinero':
                if($tipo_producto === 'comida'){
                    $response = $handler->handle($request);
                }else{
                    $response = new Response();
                    $payload = json_encode(array("mensaje" => "Los cocineros solo pueden manejar comida"));
                    $response->getBody()->write($payload);
                }
                break;
            case 'socio':
                $response = $handler->handle($request);
                echo "El que es socio modifica lo que quiere.";
                break;
            default:
                $response = new Response();
                $payload = json_encode(array("mensaje" => "Error inesperado!"));
                $response->getBody()->write($payload);
                break;
            endswitch;
        return $response->withHeader('Content-Type', 'application/json');
        }

        private function verificarUsuario(string $usuario_modificante): string {
            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            $consultaUsuario = $objAccesoDatos->prepararConsulta("SELECT tipo_usuario FROM usuarios WHERE usuario = :usuario");
            $consultaUsuario->bindValue(':usuario', $usuario_modificante, PDO::PARAM_STR);
            $consultaUsuario->execute();
            $usuario = $consultaUsuario->fetch(PDO::FETCH_ASSOC);
    
            if (!$usuario) {
                throw new HttpBadRequestException($request, "El usuario no existe.");
            }
    
            return $usuario['tipo_usuario'];
        }
        private function verificarTipoProducto(int $id_producto): string {
            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            $consultaProducto = $objAccesoDatos->prepararConsulta("SELECT tipo_producto FROM productos WHERE id = :id");
            $consultaProducto->bindValue(':id', $id_producto, PDO::PARAM_INT);
            $consultaProducto->execute();
            $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);
    
            if (!$producto) {
                throw new HttpBadRequestException($request, "El producto no está disponible en la carta.");
            }
    
            return $producto['tipo_producto'];
        }

        private function obtenerNombreProducto(int $id_producto): string {
            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            $consultaNombre = $objAccesoDatos->prepararConsulta("SELECT nombre_producto FROM productos WHERE id = :id");
            $consultaNombre->bindValue(':id', $id_producto, PDO::PARAM_INT);
            $consultaNombre->execute();
            $producto = $consultaNombre->fetch(PDO::FETCH_ASSOC);
    
            if (!$producto) {
                throw new HttpBadRequestException($request, "El producto no está disponible en la carta.");
            }
    
            return $producto['nombre_producto'];
        }
}

/*
Tengo que: 
chequear en la tabla detalle el TIPO del producto, no ingresarlo manualmente
TAMBIEN poner el usuario del empleado, que chequee en la tabla empleado el TIPO de empleado que es, y a partir de ahi dejarlo
Por ejemplo: si usuario es agustina, y agustina es bardenter, chequear si puede modificar el tipo del producto. 
TENGO que manejar el tema de la cerveza, no se si conviene hacerlo consultando la tabla producto en la columna no,bre producto.

*/