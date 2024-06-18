<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class MiddlewareEstadoMesa {
    public function __invoke(Request $request, RequestHandler $handler): Response {   
        $parametros = $request->getParsedBody();
        $tipoUsuario = $parametros['tipo_usuario'];
        $nuevoEstado = $parametros['estado'];

        if (($nuevoEstado !== 'cerrada' && $tipoUsuario === 'mozo') || ($nuevoEstado === 'cerrada' && $tipoUsuario === 'socio')) {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "No estás autorizado para cambiar el estado de la mesa"));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
        
    }
}