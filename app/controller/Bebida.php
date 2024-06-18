<?php

require_once '../app/model/Productos.php';

class Bebida extends Productos{

    public function AltaBebida($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $nombreProducto = $parametros['nombreProducto'];
        $tipo = $parametros['tipo'];
        $stock = $parametros['stock'];
        $precio = $parametros['precio'];
        $bebida = new Bebida();
        $bebida->nombreProducto = $nombreProducto;
        $bebida->tipo = $tipo; 
        $bebida->stock = $stock;
        $bebida->precio = $precio;
        $is = $bebida->altaProducto();
        $payload = json_encode(array("mensaje" => "Bebida creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }
}