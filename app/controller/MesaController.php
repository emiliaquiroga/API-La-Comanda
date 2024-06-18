<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Mesa.php';
require_once '../app/controller/Mozo.php';
require_once '../app/model/Usuario.php';


class MesaController {


    public function AbrirMesa(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $mesa = new Mesa();
        $mesa->mozo_asignado = $data['mozo_asignado'];
        $mesa->estado = $data['estado'];

        $mensaje= $mesa->altaMesa();

        
        $response->getBody()->write(json_encode(['mensaje' => "Mesa abierta exitosamente!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }
    /* public function AsignarMozo(Request $request, Response $response, $args){

        $data = $request->getParsedBody();

        switch($data['tipo']){
            case 'comida':
                $producto = new Comida($this->conexion);
                break;
            case 'bebida':
                $producto = new Bebida($this->conexion);
                break;
            
            default:
                $response->getBody()->write(json_encode(["message"=>"Producto no valido"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        
        }
        $producto->nombreProducto = $data['nombreProducto'];
        $producto->tipo = $data['tipo'];
        $producto->stock = $data['stock'];
        $producto->precio = $data['precio'];
        $mensaje= $producto->altaProducto();

        $response->getBody()->write(json_encode(['mensaje' => "Producto creado exitosamente reinona!"]));
        return $response->withHeader('Content-Type', 'application/json');
    } */

    function listarMesas(Request $request, Response $response, $args){
        $mesa = new Mesa();
        $consulta = $mesa->leerMesas();
        $listaMesas = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response -> getBody()->write(json_encode($listaMesas));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarEstadoMesa(Request $request, Response $response, array $args) {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $nuevo_estado = $parametros['estado'];

        $mesa = new Mesa();
        $mesa->id = $id;
        $mesa->estado = $nuevo_estado;
        $mesa->modificarEstadoMesa();

        $payload = json_encode(array("mensaje" => "Estado de la mesa modificado"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}