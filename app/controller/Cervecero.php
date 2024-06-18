<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
require_once '../app/model/Usuario.php';
require_once'../app/model/Pedidos.php';
class Cervecero extends Usuario{

    public function AltaCervecero($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $tipo = "Cervecero";

        $cervecero = new Cervecero();
        $cervecero->usuario = $usuario;
        $cervecero->password = $clave;
        $cervecero->tipo_usuario = $tipo; // acá lo que intento hacer es que en la DB
        // haya una columna de tipo.

        $payload = json_encode(array("mensaje" => "Cervecero creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }
    
    public function modificarEstadoPedido(Request $request, Response $response, array $args){
        $parametros = $request->getParsedBody();
        $tipo_producto = $parametros['tipo_producto'];
        $nombre_producto = $parametros['nombre_producto'];
        $estado = $parametros['estado'];

        $pedido = new Pedidos();
        $pedido->estado = $estado;
        $pedido->modificarEstadoPedido();
        $payload = json_encode(array("mensaje" => "Estado del pedido ha sido modificado"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}