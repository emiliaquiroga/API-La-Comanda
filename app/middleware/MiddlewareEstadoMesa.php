<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once '../app/utils/AutentificadorJWT.php';

class MiddlewareEstadoMesa {
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $parametros = $request->getParsedBody();
        $usuario_modificante = $request->getAttribute('usuario_modificante');
        $nuevoEstado = $parametros['estado'];
        $tipoUsuario = $this->obtenerTipoUsarioConToken($request);

        try {
            if (($nuevoEstado !== 'cerrada' && $tipoUsuario === 'mozo') || ($nuevoEstado === 'cerrada' && $tipoUsuario === 'socio')) {
                $response = $handler->handle($request);
            } else {
                $response = new Response();
                $payload = json_encode(array("mensaje" => "No estás autorizado para cambiar el estado de la mesa"));
                $response->getBody()->write($payload);
                $response = $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }
        } catch (Exception $e) {
            $payload = json_encode(array('error' => 'Token inválido o expirado'));
            $response = new Response();
            $response->getBody()->write($payload);
            $response = $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        return $response;
    }

    private function obtenerTipoUsarioConToken(Request $request): string {
        $usuario_modificante = $request->getAttribute('usuario_modificante');
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $payload = AutentificadorJWT::ObtenerPayLoad($token);

        return $payload->data->tipo_usuario;
    }
}