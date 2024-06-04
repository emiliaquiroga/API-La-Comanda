<?php

require_once '../app/model/Usuario.php';

class Cervecero extends Usuario{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion=$db;
    }
    public function AltaCervecero($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $tipo = "Cervecero";

        $cervecero = new Cervecero($this->conexion);
        $cervecero->usuario = $usuario;
        $cervecero->password = $clave;
        $cervecero->tipo = $tipo; // acá lo que intento hacer es que en la DB
        // haya una columna de tipo.

        $payload = json_encode(array("mensaje" => "Cervecero creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }
}