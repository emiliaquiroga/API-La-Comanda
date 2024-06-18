<?php

use Slim\Psr7\Request;
use Psr\Http\Message\ResponseInterface as Response;

class Mesa{

    public $mozo_asignado; // chequear que el mozo exista en la db.
    public $estado;
    public $id; 

    private $tabla = 'mesas';

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
        $query = "SELECT id, mozo_asignado , estado FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }

    public function modificarEstadoMesa(){ 
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE " . $this->tabla . " SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }
}