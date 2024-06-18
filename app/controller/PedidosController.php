<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Pedidos.php';

class PedidosController {
    public $id_producto;
    public $nombre_producto;
    public $tipo_producto;
    public $cantidad_producto;

    
    public function darAltaPedido(Request $request, Response $response, $args) {
        $listado_pedido = $request->getParsedBody();
        $pedido = new Pedidos();
        $pedido->id_mesa =  $listado_pedido['id_mesa'];
        $pedido->cod_pedido =  $pedido->generarCodigo();
        $pedido->nombre_cliente =  $listado_pedido['nombre_cliente'];
        $pedido->contenido = $listado_pedido['contenido'];
        $pedido->estado =  $listado_pedido['estado'] ;
        
        try {
            $mensaje = $pedido->altaPedido();
            $response->getBody()->write(json_encode(['mensaje' => "Pedido creado exitosamente!", 'id' => $mensaje]));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    } 
    public function listarPedidos(Request $request, Response $response, $args) {
        $pedido = new Pedidos();
        $consulta = $pedido->leerPedidos();
        $listaPedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($listaPedidos));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarDetalle(Request $request, Response $response, $args){
        $pedido = new Pedidos();
        $consulta = $pedido->leerDetalle();
        $listaPedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($listaPedidos));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarEstadoPedidoGeneral(Request $request, Response $response, $args) {
        $parametros = $request->getParsedBody();
        $id_pedido = $parametros['id_pedido'];
        $pedido = new Pedidos();
        $consulta = $pedido->listarDetalle();
        $detalles = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $pedidoListo = true;

        foreach($detalles as $detalle){
            if($detalle['id_pedido'] == $id_pedido && $detalle['estado'] !== 'listo para servir'){
                $pedidoListo = false;
                break;
            }
        }
        if($pedidoListo){
            $pedido->id = $id_pedido;
            $pedido->estado = 'listo para servir';
            $pedido->tiempo_estimado = $this->obtenerMaximoTiempoEstimado($detalles, $id_pedido);
            try {
                $pedido->modificarEstadoPedido();
                $response->getBody()->write(json_encode(['mensaje' => "Estado del pedido general está listo para servir"]));
            } catch (Exception $e) {
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            }
        }else{
            $response->getBody()->write(json_encode(['mensaje' => "No todos los productos del pedido están 'listo para servir'."]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarDetallePedido(Request $request, Response $response, $args){
        $parametros = $request->getParsedBody();
        $id_pedido = $parametros['id_pedido'];
        $estado = $parametros['estado'];
        $tiempo_estimado = $parametros['tiempo_estimado']; 
    
        $pedido = new Pedidos();
        $pedido->id = $id_pedido;
        $pedido->estado = $estado;
        $pedido->tiempo_estimado = $tiempo_estimado;

        try {
            $pedido->modificarDetallePedido();
            $response->getBody()->write(json_encode(['mensaje' => "Detalle del pedido modificado exitosamente!"]));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function obtenerMaximoTiempoEstimado($detalles, $id_pedido) {
        $filtrados = array_filter($detalles, function($detalle) use ($id_pedido) {
            return $detalle['id_pedido'] == $id_pedido;
        });
        $tiemposEstimados = array_column($filtrados, 'tiempo_estimado');
        return max($tiemposEstimados);
    }
    
    
}



