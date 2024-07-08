<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once '../app/utils/AutentificadorJWT.php';

class MiddlewareAutenticacion
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $header = $request->getHeaderLine('Authorization');
        $token = trim(str_replace("Bearer", "", $header));
        $response = new Response();

        try {
            $payload = AutentificadorJWT::ObtenerPayLoad($token);
            $usuario = $payload->data->usuario;
            $tipo_usuario = $payload->data->tipo_usuario;

            $request = $request->withAttribute('usuario', $usuario);
            $request = $request->withAttribute('tipo_usuario', $tipo_usuario);
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
            $response = $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        return $response;
    }
}