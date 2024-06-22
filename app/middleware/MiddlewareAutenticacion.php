<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once '../app/utils/AutentificadorJWT.php';

class MiddlewareAutenticacion
{

    public function __invoke(Request $request, RequestHandler $handler): Response
    {   
        $response = $handler->handle($request);
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            $payload = AutentificadorJWT::ObtenerPayLoad($token);
            $usuario = $payload->data->usuario;

            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            $stmt = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE usuario = :usuario");
            $stmt->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception("Usuario no encontrado en la base de datos");
            }
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
            $response = $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        return $response;
    }

    public static function verificarToken(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            AutentificadorJWT::VerificarToken($token);
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }
}