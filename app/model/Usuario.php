
<?php
class Usuario{

    public $usuario;
    public $password;
    public $tipo_usuario;

    private $tabla = 'usuarios';

    public function crearUsuario(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, password, tipo_usuario) VALUES (:usuario, :password, :tipo_usuario)");
        //$claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':password', $this->password, PDO::PARAM_STR); 
        $consulta->bindValue(':tipo_usuario', $this->tipo_usuario, PDO::PARAM_STR); 
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
    public function modificarDatos(){ //no lo estoy usando, pero está para probar y por si llega a ser necesario, lo hago en postman ao vivo
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE " . $this->tabla . " SET tipo_usuario = :tipo_usuario WHERE usuario = :usuario");
        $consulta->bindValue(':tipo_usuario', $this->tipo_usuario, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->execute();
    }

}    