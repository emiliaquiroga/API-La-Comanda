<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Slim\Exception\HttpBadRequestException;
require_once '../app/utils/AutentificadorJWT.php';

class MiddlewareVerificarTipoUsuario{
    public static function ValidarSocio(Request $request,RequestHandlerInterface $handler): Response{
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();
        if (empty($header)) {
            throw new Exception("Token no proporcionado");
        }
        try{
            $token = trim(explode("Bearer", $header)[1]);
            $payload= AutentificadorJWT::ObtenerPayLoad($token);
            $tipo_usuario = $payload->data->tipo_usuario;

            if($tipo_usuario === 'socio'){
                $request = $request->withAttribute('userData', $payload);
                return $handler->handle($request);
            }else{
                $response->getBody()->write(json_encode(['error' => 'Usuario no autorizado']));
                return $response->withHeader('Content-Type', 'application/json');
            }
        }catch (Exception $e) {
            if(empty($header)){
                $response->getBody()->write(json_encode(array('Error' => "Error en el Token.")));
                return $response->withHeader('Content-Type', 'application/json');
        }
        }
    }
    public static function ValidarMozo(Request $request,RequestHandlerInterface $handler): Response{
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();
        if (empty($header)) {
            throw new Exception("Token no proporcionado");
        }
        try{
            $token = trim(explode("Bearer", $header)[1]);
            $payload= AutentificadorJWT::ObtenerPayLoad($token);
            $tipo_usuario = $payload->data->tipo_usuario;

            if($tipo_usuario === 'mozo'){
                $request = $request->withAttribute('userData', $payload);
                return $handler->handle($request);
            }
        }catch (Exception $e) {
            if(empty($header)){
                $response->getBody()->write(json_encode(array('Error' => "Error en el Token.")));
                return $response->withHeader('Content-Type', 'application/json');
        }
        }
    }

    public static function ValidarCocinero(Request $request,RequestHandlerInterface $handler): Response{
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();
        if (empty($header)) {
            throw new Exception("Token no proporcionado");
        }
        try{
            $token = trim(explode("Bearer", $header)[1]);
            $payload= AutentificadorJWT::ObtenerPayLoad($token);
            $tipo_usuario = $payload->data->tipo_usuario;

            if($tipo_usuario === 'cocinero'){
                $request = $request->withAttribute('userData', $payload);
                $response = $handler->handle($request);

            }
        }catch (Exception $e) {
            if(empty($header)){
                $response->getBody()->write(json_encode(array('Error' => "Error en el Token."))); 
                return $response->withHeader('Content-Type', 'application/json');
        }

        }
    }

    public static function ValidarCervecero(Request $request,RequestHandlerInterface $handler): Response{
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();
        if (empty($header)) {
            throw new Exception("Token no proporcionado");
        }


        try{
            $token = trim(explode("Bearer", $header)[1]);
            $payload= AutentificadorJWT::ObtenerPayLoad($token);
            $tipo_usuario = $payload->data->tipo_usuario;

            if($tipo_usuario === 'cervecero'){
                $request = $request->withAttribute('userData', $payload);
                return  $handler->handle($request);

            }
        }catch (Exception $e) {
            if(empty($header)){
                $response->getBody()->write(json_encode(array('Error' => "Error en el Token.")));
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
    }
    public static function ValidarBartender(Request $request,RequestHandlerInterface $handler): Response{
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();
        if (empty($header)) {
            throw new Exception("Token no proporcionado");
        }
        try{
            $token = trim(explode("Bearer", $header)[1]);
            $payload= AutentificadorJWT::ObtenerPayLoad($token);
            $tipo_usuario = $payload->data->tipo_usuario;

            if($tipo_usuario === 'bartender'){
                $request = $request->withAttribute('userData', $payload);
                $response = $handler->handle($request);
            }
        }catch (Exception $e) {
            if(empty($header)){
                $response->getBody()->write(json_encode(array('Error' => "Error en el Token.")));
            return $response->withHeader('Content-Type', 'application/json');
            }
        }
    }

    public static function ValidarSocioOMozo(Request $request, RequestHandlerInterface $handler): Response {
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();
        if (empty($header)) {
            throw new Exception("Token no proporcionado");
        }
        try {
            $token = trim(explode("Bearer", $header)[1]);
            $payload = AutentificadorJWT::ObtenerPayLoad($token);
            $tipo_usuario = $payload->data->tipo_usuario;

            if ($tipo_usuario === 'socio' || $tipo_usuario === 'mozo') {
                $request = $request->withAttribute('userData', $payload);
                return $handler->handle($request);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Usuario no autorizado']));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['Error' => "Error en el Token."]));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }
}