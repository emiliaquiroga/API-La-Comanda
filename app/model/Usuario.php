
<?php
class Usuario{

    public $usuario;
    public $password;
    public $tipo_usuario;
    public static $perfilesValidos = ['socio', 'bartender', 'mozo', 'cocinero', 'cervecero'];
    private $tabla = 'usuarios';

    public function crearUsuario(){
        if(!in_array($this->tipo_usuario, self::$perfilesValidos)){
            throw new Exception('Perfil de usuario no válido.');
        }
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $claveHash = password_hash($this->password, PASSWORD_DEFAULT);
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, password, tipo_usuario) VALUES (:usuario, :password, :tipo_usuario)");
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':password', $claveHash, PDO::PARAM_STR); 
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

    public function modificarDatos() {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE {$this->tabla} SET tipo_usuario = :tipo_usuario WHERE usuario = :usuario");
        $consulta->bindValue(':tipo_usuario', $this->tipo_usuario, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function borrarUsuario($id_usuario) {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE {$this->tabla} SET estado = '0', fecha_baja = NOW() WHERE id_usuario = :id_usuario");
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $consulta->execute();
    }

}    