<?php

class Productos{
    public $nombre_producto;
    public $tipo;
    public $stock;
    public $precio;


    private $tabla = 'productos';

    public function altaProducto(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre_producto, tipo, stock, precio) VALUES (:nombre_producto, :tipo_producto, :stock, :precio)");
        $consulta->bindValue(':nombreProducto', $this->nombre_producto, PDO::PARAM_STR);
        $consulta->bindValue(':tipo_producto', $this->tipo, PDO::PARAM_STR); 
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_STR); 
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR); 
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public function leerProductos(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, nombre_producto, tipo_producto, stock, precio FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }

    public function cargarArchivosCSV($archivo){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $fh = fopen($archivo, "r"); //fh = file handler
        if($fh === false){
            throw new Exception("Error al abrir CSV.");
        }

        while(($row = fgetcsv($fh, 1000, ","))!== false){
            try{
                $nombre_producto = $row[0];
                $tipo_producto = $row[1];
                $stock = $row[2];
                $precio = $row[3];
                $query = "INSERT INTO ".$this->tabla."(nombre_producto, tipo_producto, stock, precio) VALUES (:nombre_producto, :tipo_producto, :stock, :precio)";
                $consulta = $objAccesoDatos->prepararConsulta($query);
                $consulta->bindParam(':nombre_producto', $nombre_producto);
                $consulta->bindParam(':tipo_producto', $tipo_producto);
                $consulta->bindParam(':stock', $stock);
                $consulta->bindParam(':precio', $precio);
                $consulta->execute();
            }catch(Exception $e){
                throw $e;
            }
        }
        fclose($fh);
        echo "Terminó ";
    } 

    public function exportarArchivoCSV(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $fh = fopen('php://temp', 'w+'); //file handler

        if ($fh === false) {
            throw new Exception("Error al crear archivo CSV.");
        }
        
        fputcsv($fh, ['id', 'nombre_producto', 'tio_producto', 'stock', 'precio']);
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
Cuanod hago el send and download, tiene que llegar y descargarse. En la response, devolver el csv,
poner en header que el tipo de rta es csv, y cuando demos el send and download, se descarga automáticamente. !!!!!

hashear contraseñas

MANEJAR REPETICIONES:
NO SE PUEDE REPETIR EL MISMO PRODUCTO, debería aumentar el stock solamente.
al igual que en el primer parcial.

*/