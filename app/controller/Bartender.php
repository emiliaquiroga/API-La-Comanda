<?php

require_once "./model/Usuario.php";

class Bartender extends Usuario {
    
    private $conexion;

    public function __construct($db)
    {
        $this->conexion=$db;
    }

    public function AltaBartender($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $tipo = "Bartender";
        $bartender = new Bartender($this->conexion);
        $bartender->usuario = $usuario;
        $bartender->password = $clave;
        $bartender->tipo = $tipo; // acá lo que intento hacer es que en la DB
        // haya una columna de tipo.
        $is = $bartender->crearUsuario();
        $payload = json_encode(array("mensaje" => "Bartender creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }
}