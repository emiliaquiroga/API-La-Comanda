<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once'../app/model/Pedidos.php';

class PedidosController{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function altaPedido(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $pedido = new Pedidos($this->conexion);
        $pedido->nro_mesa = $data['nro_mesa'];
        $pedido->cod_pedido = $pedido->generarCodigo();
        $pedido->estado = $data['estado'];

        $mensaje= $pedido->altaPedido();

        
        $response->getBody()->write(json_encode(['mensaje' => "Pedido creado exitosamente!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarPedidos(Request $request, Response $response, $args){
        $pedido = new Pedidos($this->conexion);
        $consulta = $pedido->leerPedidos();
        $listaPedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($listaPedidos));
        return $response->withHeader('Content-Type', 'application/json');
    }

}