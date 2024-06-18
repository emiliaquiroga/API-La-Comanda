<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Productos.php';
require_once 'Bebida.php';
require_once 'Comida.php';

class ProductosController extends Productos{

    public function AsignarProducto(Request $request, Response $response, $args){

        $data = $request->getParsedBody();

        switch($data['tipo']){
            case 'comida':
                $producto = new Comida();
                break;
            case 'bebida':
                $producto = new Bebida();
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
    }

    function listarProducto(Request $request, Response $response, $args){
        $producto = new Productos();
        $consulta = $producto->leerProductos();
        $listaProductos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response -> getBody()->write(json_encode($listaProductos));
        return $response->withHeader('Content-Type', 'application/json');
    }


}