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

            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE usuario = :usuario");
            $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $consulta->execute();
            $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                throw new Exception("Usuario no encontrado en la base de datos");
            }

            $request = $request->withAttribute('usuario_modificante', $usuario);
            $request = $request->withAttribute('tipo_usuario', $tipo_usuario);
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