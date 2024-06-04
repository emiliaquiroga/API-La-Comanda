
<?php
class Usuario{

    public $usuario;
    public $password;
    public $tipo;

    private $conexion;
    private $tabla = 'usuarios';

    public function crearUsuario(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, password, tipo_usuario) VALUES (:usuario, :password, :tipo)");
        //$claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':password', $this->password, PDO::PARAM_STR); 
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR); 
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public function leerUsuarios(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, usuario, password, tipo_usuario, fecha_alta FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }
}