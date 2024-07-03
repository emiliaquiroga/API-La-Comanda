<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Usuario.php';



class UsuarioController extends Usuario{

    public function AsignarUsuario(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $usuario = new Usuario();
        $usuario->usuario = $data['usuario'];
        $usuario->password = $data['password'];
        $usuario->tipo_usuario = $data['tipo_usuario'];
        $mensaje= $usuario->crearUsuario();
        $response->getBody()->write(json_encode(['mensaje' => "Usuario creado exitosamente!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    function listarUsuario(Request $request, Response $response, $args){
        $usuario = new Usuario();
        $consulta = $usuario->leerUsuarios();
        $listaUsuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response -> getBody()->write(json_encode($listaUsuarios));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno(Request $request, Response $response, $args) {
        $parametros = $request->getParsedBody();
        if (!isset($parametros['id_usuario'])) {
            $payload = json_encode(array("error" => "id_usuario no proporcionado"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $id_usuario = $parametros['id_usuario'];
        Usuario::borrarUsuario($id_usuario);

        $payload = json_encode(array("mensaje" => "Usuario borrado con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno(Request $request, Response $response, $args) {
        $parametros = $request->getParsedBody();

        if (!isset($parametros['id_usuario'])) {
            $payload = json_encode(array("error" => "id_usuario no proporcionado"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $usuario = new Usuario();
        $usuario->usuario = $parametros['usuario'];
        $usuario->tipo_usuario = $parametros['tipo_usuario'];
        $usuario->modificarDatos();
        $payload = json_encode(array("mensaje" => "Usuario modificado con éxito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
