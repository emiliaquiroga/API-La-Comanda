<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Message;
use Slim\Exception\HttpUnauthorizedException;

require_once '../app/model/Productos.php';
require_once '../app/controller/ProductosController.php';
require_once '../app/controller/MesaController.php';


class Pedidos {
    public $id;
    public $id_mesa;
    public $cod_pedido;
    public $nombre_cliente;
    public $contenido;
    public $estado;
    public $tiempo_estimado;
    public $foto;
    public $fecha;
    public $fecha_cierre;
    

    public $id_producto;
    public $cantidad;


    private $tabla = 'pedidos';

    public function altaPedido() {
        if ($this->id_mesa && $this->nombre_cliente && $this->contenido) {
            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            try {
                $this->cod_pedido = $this->generarCodigo();
                $pedidoId = $this->nuevoPedido($objAccesoDatos); //insert en tabla 'pedidos'
                if (is_string($this->contenido)) {
                    $this->contenido = json_decode($this->contenido, true);
                }
                $this->nuevoDetallePedido($objAccesoDatos, $pedidoId); //insert en tabla 'detalle_pedido'
                $this->calcularCostoTotal($pedidoId, $objAccesoDatos);
                return $pedidoId;
            } catch (Exception $e) {
                throw $e;
            }
        } else {
            throw new Exception("Todos los campos son obligatorios");
        }
    }

    private function nuevoPedido($objAccesoDatos){
        if ($this->id_mesa && $this->nombre_cliente) {
            $consulta = $objAccesoDatos->prepararConsulta( "INSERT INTO pedidos (id_mesa, cod_pedido, nombre_cliente, estado, foto) VALUES (:id_mesa, :cod_pedido, :nombre_cliente, :estado, :foto)");
            $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT); 
            $consulta->bindValue(':cod_pedido', $this->cod_pedido, PDO::PARAM_STR);  
            $consulta->bindValue(':nombre_cliente', $this->nombre_cliente, PDO::PARAM_STR); 
            $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR); 
            $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
            $consulta->execute();

            $pedidoId = $objAccesoDatos->obtenerUltimoId();
            return $pedidoId;
        }
    }

    public static function guardarImagenPedido($ruta, $uploadedFile, $cod_pedido, $fecha) {
        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $filename = "pedido_" . $cod_pedido ."_".$fecha."." . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filepath = $ruta . DIRECTORY_SEPARATOR . $filename;

        $uploadedFile->moveTo($filepath);

        return $filename;
    }

    private function nuevoDetallePedido($objAccesoDatos, $pedidoId){
        foreach ($this->contenido as $detalle) {
            $precioProducto = $this->obtenerPrecioProducto($objAccesoDatos, $detalle['id_producto']);
            $codigo_mesa = $this->obtenerCodigoMesa($this->id_mesa, $objAccesoDatos);
            $consultaDetalle = $objAccesoDatos->prepararConsulta("INSERT INTO detalle_pedido (id_pedido, cod_pedido, codigo_mesa, id_producto, sector, precio_producto, cantidad, estado, tiempo_estimado) VALUES (:id_pedido, :cod_pedido, :codigo_mesa, :id_producto, :sector, :precio_producto, :cantidad, :estado, :tiempo_estimado)");
            $consultaDetalle->bindValue(':id_pedido', $pedidoId, PDO::PARAM_INT);
            $consultaDetalle->bindValue(':cod_pedido', $this->cod_pedido, PDO::PARAM_STR_CHAR);
            $consultaDetalle->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR_CHAR);
            $consultaDetalle->bindValue(':id_producto', $detalle['id_producto'], PDO::PARAM_INT);
            $consultaDetalle->bindValue(':sector', $detalle['sector'], PDO::PARAM_STR);
            $consultaDetalle->bindValue(':precio_producto', $precioProducto, PDO::PARAM_INT);
            $consultaDetalle->bindValue(':cantidad', $detalle['cantidad_producto'], PDO::PARAM_INT);
            $consultaDetalle->bindValue(':estado', $this->estado, PDO::PARAM_STR);
            $consultaDetalle->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
            $consultaDetalle->execute();
            Productos::descontarStock($this->cantidad, $this->id_producto);
            
        }
    }


    private function obtenerCodigoMesa($id_mesa, $objAccesoDatos) {
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa FROM mesas WHERE id = :id_mesa");
        $consulta->bindValue(':id_mesa', $id_mesa, PDO::PARAM_INT);
        $consulta->execute(); 
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($resultado) {
            return $resultado['codigo_mesa'];
        } else {
            return null; 
        }
    }

    public static function obtenerPorCodigoPedido($cod_pedido)
    {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, cod_pedido FROM pedidos WHERE cod_pedido = :cod_pedido");
        $consulta->bindValue(':cod_pedido', $cod_pedido, PDO::PARAM_STR);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        
        if($resultado) {
            return true;
        } else {
            return false;
        }
    }

    private function obtenerPrecioProducto($objAccesoDatos, $id_producto){
        $consulta = $objAccesoDatos->prepararConsulta("SELECT precio FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id_producto, PDO::PARAM_INT);
        $consulta->execute();
        $precio = $consulta->fetch(PDO::FETCH_ASSOC);

        if($precio){
            return $precio['precio'];
        }else{
            throw new Exception("No se encontró el precio del producto con ID $id_producto");
        }
    }
    public function leerPedidos() {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, id_mesa, cod_pedido, nombre_cliente, contenido, estado, costo_total, tiempo_estimado, fecha_pedido FROM " . $this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }

    public function leerDetalle(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, id_pedido, id_producto,  cantidad, estado, tiempo_estimado FROM detalle_pedido";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function listarPedidosPendientes(Request $request, Response $response){
        try{
                $objAccesoDatos = ManipularDatos::obtenerInstancia();
                $query = "SELECT id_producto, sector FROM detalle_pedido WHERE estado IN ('pendiente', 'en preparacion')";
                $consulta = $objAccesoDatos->prepararConsulta($query);
                $consulta->execute();
                $listaPedidos = $consulta->fetchAll(PDO::FETCH_ASSOC);
                
                return $listaPedidos;
            } catch(Exception $e){
                throw $e;
            }
    }

    public function listarPedidosListosParaServir(Request $request, Response $response){
        try{
            $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM detalle_pedido
                                                        WHERE estado = 'listo para servir'");
        $consulta->execute();
        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
        } catch(Exception $e){
            throw $e;
        }
    }

    public static function obtenerPendientesPorTipoUsuario($tipo_usuario) {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $sector = self::obtenerSectorPorTipoUsuario($tipo_usuario);

        $consulta = $objAccesoDatos->prepararConsulta("
            SELECT dp.*
            FROM detalle_pedido dp 
            JOIN productos pr ON dp.id_producto = pr.id
            WHERE dp.estado IN ('pendiente', 'en preparacion') AND pr.sector = :sector
        ");
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function obtenerSectorPorTipoUsuario($tipo_usuario) {
        switch ($tipo_usuario) {
            case 'cervecero':
                return 'cerveza';
            case 'bartender':
                return 'tragosVinos';
            case 'cocinero':
                return 'cocina'.'candybar';
            case 'socio':
                return 'socio'; 
            default:
                throw new Exception('Tipo de usuario no válido');
        }
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
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = :estado, tiempo_estimado = :tiempo_estimado WHERE id_producto = :id_producto");
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->execute();
    }

    public function modificarDetallePedido(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE detalle_pedido SET estado = :estado, tiempo_estimado = :tiempo_estimado WHERE id_producto = :id_producto");
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_INT);
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->execute();
    }

    private function calcularCostoTotal($pedidoId, $objAccesoDatos){
        $consulta = $objAccesoDatos->prepararConsulta("
        SELECT SUM(dp.cantidad * p.precio) as total
        FROM detalle_pedido dp
        JOIN productos p ON dp.id_producto = p.id
        WHERE dp.id_pedido = :id_pedido
        ");
        $consulta->bindValue(':id_pedido', $pedidoId, PDO::PARAM_INT);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        $total = $resultado['total'];

        $consultaUpdate = $objAccesoDatos->prepararConsulta("
        UPDATE pedidos SET costo_total = :costo_total WHERE id = :id_pedido
        ");
        $consultaUpdate->bindValue(':costo_total', $total, PDO::PARAM_INT);
        $consultaUpdate->bindValue(':id_pedido', $pedidoId, PDO::PARAM_INT);
        $consultaUpdate->execute();

    }

    public function obtenerTiemposEstimados($cod_pedido, $codigo_mesa) {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT tiempo_estimado FROM detalle_pedido WHERE cod_pedido = :cod_pedido AND codigo_mesa = :codigo_mesa";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':cod_pedido', $cod_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function traerPorCodigo($cod_pedido, $codigo_mesa){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT tiempo_estimado FROM detalle_pedido WHERE cod_pedido = :cod_pedido AND codigo_mesa = :codigo_mesa";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':cod_pedido',$cod_pedido, PDO::PARAM_STR_CHAR);
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_STR_CHAR);
        $consulta->execute();
        $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);
        
        if (!$consulta) {
            throw new Exception('Ocurrió un error, verifique que los datos ingresados sean correctos!');
        }
    
        return $resultado;
    }

    public function cobrarCuenta($cod_pedido) {
        try {
            $objAccesoDatos = ManipularDatos::obtenerInstancia();
            $query = "SELECT costo_total FROM pedidos WHERE cod_pedido = :cod_pedido";
            $consulta = $objAccesoDatos->prepararConsulta($query);
            $consulta->bindValue(':cod_pedido', $cod_pedido, PDO::PARAM_STR);
            $consulta->execute();

            $queryMesa = "UPDATE mesas 
                SET estado = 'con el cliente pagando' 
                WHERE id = (
                    SELECT m.id
                    FROM mesas m
                    INNER JOIN detalle_pedido dp ON m.codigo_mesa = dp.codigo_mesa
                    WHERE dp.cod_pedido = :cod_pedido
                    LIMIT 1
                )";
            $consultaUpdate = $objAccesoDatos->prepararConsulta($queryMesa);
            $consultaUpdate->bindValue(':cod_pedido', $cod_pedido, PDO::PARAM_STR);
            $consultaUpdate->execute();
    
            return true; 
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function ObtenerPedidoFinalizado()
    {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE estado = 'finalizado'");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerPrecioFinal(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT costo_total FROM pedidos WHERE estado = 'finalizado'");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
} 
