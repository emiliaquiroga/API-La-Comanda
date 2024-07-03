<?php
use Slim\Psr7\Request;
use Psr\Http\Message\ResponseInterface as Response;

class LogInController{
    public function iniciarSesion(Request $request, Response $response){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $password = $parametros['password'];

        $usuario = new Usuario();
        $datos = array('usuario' => $usuario);
        $token = AutentificadorJWT::CrearToken($datos);
        $payload = json_encode(array('jwt' => $token));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}