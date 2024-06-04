<?php
class Mesa{

    public $mozo_asignado;

    public $estado;

    private $tabla = 'mesas';
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    public function altaMesa(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (mozo_asignado, estado) VALUES (:mozo_asignado, :estado)");
        //$claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta->bindValue(':mozo_asignado', $this->mozo_asignado, PDO::PARAM_STR); 
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR); 
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public function leerMesas(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT nro_mesa, mozo_asignado , estado FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }
}