<?php

require_once '../app/model/Usuario.php';

class Socio extends Usuario{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion=$db;
    }
    public function AltaSocio($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $tipo = "socio";
        $socio = new Socio($this->conexion);
        $socio->usuario = $usuario;
        $socio->password = $clave;
        $socio->tipo = $tipo; // acá lo que intento hacer es que en la DB
        // haya una columna de tipo.

        $payload = json_encode(array("mensaje" => "Socio creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }
}