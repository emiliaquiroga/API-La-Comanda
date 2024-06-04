<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Usuario.php';
require_once '../app/controller/Bartender.php';
require_once '../app/controller/Cervecero.php';
require_once '../app/controller/Mozo.php';
require_once '../app/controller/Cocinero.php';
require_once '../app/controller/Socio.php';


class UsuarioController{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function AsignarUsuario(Request $request, Response $response, $args){

        $data = $request->getParsedBody();

        switch($data['tipo_usuario']){
            case 'socio':
                $usuario = new Socio($this->conexion);
                break;
            case 'bartender':
                $usuario = new Bartender($this->conexion);
                break;
            case 'mozo':
                $usuario = new Mozo($this->conexion);
                break;
            case 'cocinero':
                $usuario = new Cocinero($this->conexion);
                break;
            case 'cervecero':
                $usuario = new Cervecero($this->conexion);
                break;
            default:
                $response->getBody()->write(json_encode(["message"=>"Rol no valido"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        
        }
        $usuario->usuario = $data['usuario'];
        $usuario->password = $data['password'];
        $usuario->tipo = $data['tipo_usuario'];
        $mensaje= $usuario->crearUsuario();

        $response->getBody()->write(json_encode(['mensaje' => "Usuario creado exitosamente reinona!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    function listarUsuario(Request $request, Response $response, $args){
        $usuario = new Usuario();
        $consulta = $usuario->leerUsuarios();
        $listaUsuarios = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response -> getBody()->write(json_encode($listaUsuarios));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
