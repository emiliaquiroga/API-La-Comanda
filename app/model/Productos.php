<?php

class Productos{
    public $nombre_producto;
    public $sector;
    public $stock;
    public $precio;

    public static $sectoresValidos = ['cocina', 'tragosVinos', 'cerveza', 'candybar']; 
    private $tabla = 'productos';

    public function altaProducto(){
        if(!in_array($this->sector, self::$sectoresValidos)){throw new Exception('Sector no válido.');}
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $existe = $this->verificarRepeticion($objAccesoDatos);

        if($existe){
            $consulta = $objAccesoDatos->prepararConsulta("UPDATE productos SET precio = :precio, stock = stock + :stock WHERE nombre_producto = :nombre_producto AND sector = :sector");
            $consulta->bindValue(':nombre_producto', $this->nombre_producto, PDO::PARAM_STR);
            $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR); 
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_STR); 
            $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR); 
        }else{
            $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre_producto, sector, stock, precio) VALUES (:nombre_producto, :sector, :stock, :precio)");
            $consulta->bindValue(':nombre_producto', $this->nombre_producto, PDO::PARAM_STR);
            $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR); 
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_STR); 
            $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR); 
        }
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    private function verificarRepeticion($objAccesoDatos){
        $query = "SELECT * FROM productos WHERE nombre_producto = :nombre_producto AND sector = :sector";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':nombre_producto', $this->nombre_producto, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->rowCount() > 0;
    }

    public static function descontarStock($cantidad, $id_producto){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "UPDATE productos SET stock = stock - :cantidad WHERE id = :id";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindvalue(':cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->bindvalue(':id', $id_producto, PDO::PARAM_STR);
        $consulta->execute();
    }

    public function leerProductos(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, nombre_producto, sector, stock, precio FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }
    public function leerPorSector($sector){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT dp.id, p.nombre_producto, p.sector, p.stock, p.precio FROM detalle_pedido dp JOIN productos p ON dp.id_producto = p.id WHERE p.sector = :sector";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta;
    }


    public function cargarArchivosCSV($archivo){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $fh = fopen($archivo, "r"); //fh = file handler
        if($fh === false){
            throw new Exception("Error al abrir CSV.");
        }
        $header = fgetcsv($fh, 1000, ","); // Leer la primera fila como encabezado
        if ($header === false || count($header) < 4) {
            throw new Exception("El archivo CSV tiene un encabezado inválido.");
        }
    
        while(($row = fgetcsv($fh, 1000, ","))!== false){
            // Verificar que la fila tenga al menos 4 elementos
            if (count($row) < 4) {
                echo "Fila incompleta, se omitirá: " . implode(",", $row) . "\n";
                continue;
            }
            try{
                $producto = new Productos();
                $producto->nombre_producto= $row[0];
                $producto->sector= $row[1];
                $producto->stock = $row[2];
                $producto->precio = $row[3];
                $producto->altaProducto();
                echo "Se cargo el archivo correctamente";
            }catch(Exception $e){
                throw $e;
            }
        }
        fclose($fh);
        
    } 

    public function exportarArchivoCSV(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $fh = fopen('php://temp', 'w+'); //file handler

        if ($fh === false) {
            throw new Exception("Error al crear archivo CSV.");
        }
        
        fputcsv($fh, ['id', 'nombre_producto', 'sector', 'stock', 'precio']);
        $productos = new Productos();
        $consulta = $productos->leerProductos();
        $listaProductos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        foreach($listaProductos as $producto){
            fputcsv($fh, $producto);
        }
        $csv = stream_get_contents($fh, -1, 0);
        fclose($fh);
        
        if ($csv === false) {
            throw new Exception("Error al leer el archivo CSV.");
        }
    
        return $csv;
    }
}


/*

MANEJAR REPETICIONES:
NO SE PUEDE REPETIR EL MISMO PRODUCTO, debería aumentar el stock solamente.
al igual que en el primer parcial.

*/