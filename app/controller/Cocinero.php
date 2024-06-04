<?php

require_once '../app/model/Usuario.php';

class Cocinero extends Usuario{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion=$db;
    }
    public function AltaCocinero($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $tipo = "Cocinero";
        $cocinero = new Cocinero($this->conexion);
        $cocinero->usuario = $usuario;
        $cocinero->password = $clave;
        $cocinero->tipo = $tipo; // acá lo que intento hacer es que en la DB
        // haya una columna de tipo.

        $payload = json_encode(array("mensaje" => "Cocinero creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }
}