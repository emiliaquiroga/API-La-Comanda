<?php

require_once '../app/model/Productos.php';

class Bebida extends Productos{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion=$db;
    }

    public function AltaBebida($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $nombreProducto = $parametros['nombreProducto'];
        $tipo = $parametros['tipo'];
        $stock = $parametros['stock'];
        $precio = $parametros['precio'];
        $bebida = new Bebida($this->conexion);
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