<?php

use Slim\Psr7\Request;
use Psr\Http\Message\ResponseInterface as Response;

class Mesa{

    public $codigo_mesa; // chequear que el mozo exista en la db.
    public $estado;
    public $id; 

    public static $estadosValidos = ['abierta', 'cerrada', 'con cliente esperando pedido', 'con cliente comiendo', 'con cliente pagando'];
    private $tabla = 'mesas';

    public function altaMesa(){
        if(!in_array($this->estado, self::$estadosValidos)){
            throw new Exception('Estado de mesa no valido.');
        }
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (codigo_mesa, estado) VALUES (:codigo_mesa, :estado)");
        //$claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR); 
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR); 
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public function leerMesas(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, codigo_mesa , estado FROM " .$this->tabla;
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