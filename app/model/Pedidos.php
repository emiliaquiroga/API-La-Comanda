<?php

class Pedidos{

    public $id;
    public $nro_mesa;
    public $cod_pedido;
    public $estado;

    private $tabla = 'pedidos';
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function altaPedido(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (nro_mesa, cod_pedido, estado) VALUES (:nro_mesa, :cod_pedido, :estado)");
        //$claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta->bindValue(':nro_mesa', $this->nro_mesa, PDO::PARAM_STR); 
        $consulta->bindValue(':cod_pedido', $this->cod_pedido, PDO::PARAM_STR); 
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR); 
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public function leerPedidos(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT nro_mesa, cod_pedido, estado FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }

    public function generarCodigo(){
        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $ultimoId = ManipularDatos::obtenerInstancia()->obtenerUltimoId();
        $numero = (int)$ultimoId + 1;
        //estoy generando un codigo alfanumerico de 5 digitos.
        if (strlen($numero) == 1) { //esto es para que, si el numero tiene 1 solo digito, hayan 4 letras
            $letras = substr(str_shuffle($caracteres), 0, 4);
            $codigo = $letras . $numero;
        } else { // si el numero tiene 2 digitos, habran 3 letras.
            $letras = substr(str_shuffle($caracteres), 0, 3);
            $codigo = $letras . str_pad($numero, 2, '0', STR_PAD_LEFT);
        }
        return $codigo;

        

    }
}