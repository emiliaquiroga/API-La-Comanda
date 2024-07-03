<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Pedidos.php';

class PedidosController {
    public $id_producto;
    public $nombre_producto;
    public $tipo_producto;
    public $foto;
    public $fecha_cierre;
    public $cantidad_producto;

    
    public function darAltaPedido(Request $request, Response $response, $args) {
        $listado_pedido = $request->getParsedBody();
        $archivos = $request->getUploadedFiles();
        $pedido = new Pedidos();
        $pedido->id_mesa =  $listado_pedido['id_mesa'];
        $pedido->cod_pedido =  $pedido->generarCodigo();
        $pedido->nombre_cliente =  $listado_pedido['nombre_cliente'];
        $pedido->contenido = $listado_pedido['contenido'];
        $pedido->estado =  $listado_pedido['estado'] ;
        if (isset($archivos['foto'])) {
            $foto = $archivos['foto'];
            if ($foto->getError() === UPLOAD_ERR_OK) {
                $filename = Pedidos::guardarImagenPedido("../ImagenesDePedidos/2024", $foto, $pedido->cod_pedido, $pedido->fecha);
                $pedido->foto = $filename;
            }
        }
        
        try {
            $mensaje = $pedido->altaPedido();
            $response->getBody()->write(json_encode(['mensaje' => "Pedido creado exitosamente!"]));
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
            $pedido->tiempo_estimado = $this->obtenerTiempoEstimadoMaximo($request, $response, $args);
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

    public function obtenerTiempoEstimadoMaximo(Request $request, Response $response, $args) {
        $parametros = $request->getParsedBody();
        $cod_pedido = $parametros['cod_pedido'];
        $codigo_mesa = $parametros['codigo_mesa'];
        $pedido = new Pedidos();

        try {
            $tiemposEstimados = $pedido->obtenerTiemposEstimados($cod_pedido, $codigo_mesa);
            $tiempos = array_column($tiemposEstimados, 'tiempo_estimado');
            if (empty($tiempos)) {
                throw new Exception("No se encontraron tiempos estimados para el pedido y mesa especificados.");
            }
            $maximoTiempo = max($tiempos);
            $mensaje = "El tiempo estimado de preparacion de su pedido es de: " . $maximoTiempo . " minutos";
            $response->getBody()->write(json_encode(['mensaje' => $mensaje]));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
    public function listarPedidosPendientes(Request $request, Response $response, $args){
        try {
            $pedido = new Pedidos();
            $listaPedidos = $pedido->listarPedidosPendientes($request, $response);
            $response->getBody()->write(json_encode($listaPedidos));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function listarPedidosPendientesPorUsuario(Request $request, Response $response, $args){
        $tipo_usuario = $request->getAttribute('tipo_usuario');
        if (!in_array($tipo_usuario, ['bartender', 'cervecero', 'cocinero', 'candybar'])) {
            $response->getBody()->write(json_encode(['error' => 'Tipo de usuario no reconocido: ' . $tipo_usuario]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    
        
        try {
            $listaPedidos = Pedidos::obtenerPendientesPorTipoUsuario($tipo_usuario);
            $response->getBody()->write(json_encode($listaPedidos));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function listarPedidosListosParaServir(Request $request, Response $response){
        try{
            $pedido = new Pedidos();
            $listaPedidos = $pedido->listarPedidosListosParaServir($request, $response);
            $response->getBody()->write(json_encode($listaPedidos));
            return $response->withHeader('Content-Type', 'application/json');
        } catch(Exception $e){
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function cobrarCuenta(Request $request, Response $response) {
        try {
            $parametros = $request->getParsedBody();
            $cod_pedido = $parametros['cod_pedido']; 
    
            $modelo = new Pedidos(); 
            $exito = $modelo->cobrarCuenta($cod_pedido);
    
            if ($exito) {
                $response->getBody()->write(json_encode(['mensaje' => 'Cuenta cobrada exitosamente']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => 'No se pudo cobrar la cuenta']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    

public function PromedioIngresos30Dias($request, $response, $args)
{
    try {
        $fecha_actual = date("Y-m-d H:i:s");
        $fecha_actualObj = new DateTime($fecha_actual);
        $fecha_limite = $fecha_actualObj->modify('-30 days');
        
        $pedidos = Pedidos::ObtenerPedidoFinalizado();

        if (empty($pedidos)) {
            throw new Exception("No se encontraron pedidos finalizados en los ultimos 30 dias.");
        }

        $acumulador = 0;
        $contador = 0;

        foreach ($pedidos as $pedido) {
            if (isset($pedido['fecha_cierre']) && isset($pedido['costo_total'])) {
                $fecha_cierre = new DateTime($pedido['fecha_cierre']);
                if ($fecha_cierre >= $fecha_limite) {
                    $acumulador += $pedido['costo_total'];
                    $contador++;
                }
            } 
        }

        if ($contador === 0) {
            throw new Exception("No hay pedidos finalizados dentro del rango de los últimos 30 días.");
        }

        $promedio = $acumulador / $contador;

        $payload = json_encode(array("mensaje" => "El importe promedio en los últimos 30 días fue de: " . $promedio));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (Exception $e) {
        $payload = json_encode(array("error" => $e->getMessage()));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}

    
    


}



