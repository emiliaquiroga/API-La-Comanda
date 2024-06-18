<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
require_once "./model/Usuario.php";
require_once "./model/Pedidos.php";

class Bartender extends Usuario {


    public function AltaBartender($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $tipo = "Bartender";
        $bartender = new Bartender();
        $bartender->usuario = $usuario;
        $bartender->password = $clave;
        $bartender->tipo_usuario = $tipo; // acá lo que intento hacer es que en la DB
        // haya una columna de tipo.
        $is = $bartender->crearUsuario();
        $payload = json_encode(array("mensaje" => "Bartender creado con exito"));

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