<?php

class Productos{
    public $nombreProducto;
    public $tipo;
    public $stock;
    public $precio;

    private $conexion;
    private $tabla = 'productos';

    public function altaProducto(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombreProducto, tipo, stock, precio) VALUES (:nombreProducto, :tipo, :stock, :precio)");
        $consulta->bindValue(':nombreProducto', $this->nombreProducto, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR); 
        $consulta->bindValue(':stock', $this->stock, PDO::PARAM_STR); 
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR); 
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
    public function leerProductos(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, nombreProducto, tipo, stock, precio FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }
}
    
