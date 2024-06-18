<?php

require_once '../app/model/Usuario.php';

class Mozo extends Usuario{

    public function AltaMozo($request, $response, $arg){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
        $tipo = "mozo";
        $mozo = new Mozo();
        $mozo->usuario = $usuario;
        $mozo->password = $clave;
        $mozo->tipo_usuario = $tipo; // acá lo que intento hacer es que en la DB
        // haya una columna de tipo.

        $payload = json_encode(array("mensaje" => "Mozo creado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json'); // esto lo saque del modelo de usuarioController
    }

}