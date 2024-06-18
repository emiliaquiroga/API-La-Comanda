<?php

require_once '../app/model/Productos.php';

class Comida extends Productos{

    public function AltaComida($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $nombreProducto = $parametros['nombreProducto'];
        $tipo = $parametros['tipo'];
        $stock = $parametros['stock'];
        $precio = $parametros['precio'];
        $comida = new Comida();
        $comida->nombreProducto = $nombreProducto;
        $comida->tipo = $tipo; 
        $comida->stock = $stock;
        $comida->precio = $precio;
        $is = $comida->altaProducto();
        $payload = json_encode(array("mensaje" => "Comida creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }
}