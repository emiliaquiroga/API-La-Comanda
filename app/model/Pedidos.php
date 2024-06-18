<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Message;

class Pedidos {
    public $id;
    public $id_mesa;
    public $cod_pedido;
    public $nombre_cliente;
    public $contenido;
    public $estado;
    public $tiempo_estimado;

    public $id_producto;
    public $cantidad;


    private $tabla = 'pedidos';

    public function altaPedido() {
        if ($this->id_mesa && $this->nombre_cliente && $this->contenido) {
            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            try {
                $pedidoId = $this->nuevoPedido($objAccesoDatos); //insert en tabla 'pedidos'
                if (is_string($this->contenido)) {
                    $this->contenido = json_decode($this->contenido, true);
                }
                $this->nuevoDetallePedido($objAccesoDatos, $pedidoId); //insert en tabla 'detalle_pedido'
                return $pedidoId;
            } catch (Exception $e) {
                //$objAccesoDatos->rollBack(); // deshago la accion en caso de error
                throw $e;
            }
        } else {
            throw new Exception("Todos los campos son obligatorios");
        }
    }

    private function nuevoPedido($objAccesoDatos){
        if ($this->id_mesa && $this->nombre_cliente && $this->contenido) {
            $consulta = $objAccesoDatos->prepararConsulta( "INSERT INTO pedidos (id_mesa, cod_pedido, nombre_cliente, contenido, estado) VALUES (:id_mesa, :cod_pedido, :nombre_cliente, :contenido, :estado)");
            $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT); 
            $consulta->bindValue(':cod_pedido', $this->cod_pedido, PDO::PARAM_STR);  
            $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR); 
            $consulta->bindValue(':contenido', json_encode($this->contenido), PDO::PARAM_STR);
            $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR); 
            $consulta->execute();

            $pedidoId = $objAccesoDatos->obtenerUltimoId();
            return $pedidoId;
        }
    }

    private function nuevoDetallePedido($objAccesoDatos, $pedidoId){
        foreach ($this->contenido as $detalle) {
            $consultaDetalle = $objAccesoDatos->prepararConsulta("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, estado, tiempo_estimado) VALUES (:id_pedido, :id_producto,  :cantidad, :estado, :tiempo_estimado)");
            $consultaDetalle->bindValue(':id_pedido', $pedidoId, PDO::PARAM_INT);
            $consultaDetalle->bindValue(':id_producto', $detalle['id_producto'], PDO::PARAM_INT);
            $consultaDetalle->bindValue(':cantidad', $detalle['cantidad_producto'], PDO::PARAM_INT);
            $consultaDetalle->bindValue(':estado', $this->estado, PDO::PARAM_STR);
            $consultaDetalle->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
            $consultaDetalle->execute();
        }
    }


    public function leerPedidos() {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, id_mesa, cod_pedido, nombre_cliente, contenido, estado, tiempo_estimado, fecha_pedido FROM " . $this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }

    public function leerDetalle(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, id_pedido, id_producto,  cantidad, estado, tiempo_estimado FROM detalle_pedido";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }

    public function generarCodigo() {
        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $ultimoId = ManipularDatos::obtenerInstancia()->obtenerUltimoId();
        $numero = (int)$ultimoId + 1;
        if (strlen($numero) == 1) {
            $letras = substr(str_shuffle($caracteres), 0, 4);
            $codigo = $letras . $numero;
        } else {
            $letras = substr(str_shuffle($caracteres), 0, 3);
            $codigo = $letras . str_pad($numero, 2, '0', STR_PAD_LEFT);
        }
        return $codigo;
    }

    public function modificarEstadoPedido() {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = :estado, tiempo_estimado = :tiempo_estimado WHERE id = :id");
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public function modificarDetallePedido(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE detalle_pedido SET estado = :estado, tiempo_estimado = :tiempo_estimado WHERE id_pedido = :id_pedido");
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->bindValue(':id_pedido', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }
}