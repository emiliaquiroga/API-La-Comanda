<?php

class Encuesta{
    public $id;
    public $cod_pedido;
    public $codigo_mesa;
    public $puntuacion_mesa;
    public $puntuacion_mozo;
    public $puntuacion_comida;
    public $puntuacion_restaurante;
    public $comentarios;
    public $fecha;

    public function crearEncuesta(){
        if (!self::ValidarPuntuacion($this->puntuacion_mesa) ||
            !self::ValidarPuntuacion($this->puntuacion_mozo) ||
            !self::ValidarPuntuacion($this->puntuacion_comida) ||
            !self::ValidarPuntuacion($this->puntuacion_restaurante)) {
            throw new Exception("Las puntuaciones deben estar en el rango de 1 a 10");
        }

        // Validar comentarios
        $this->validarComentarios($this->comentarios);

        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO encuestas (cod_pedido, codigo_mesa, puntuacion_mesa, puntuacion_mozo, puntuacion_comida, puntuacion_restaurante, comentarios) 
                                VALUES (:cod_pedido, :codigo_mesa, :puntuacion_mesa, :puntuacion_mozo, :puntuacion_comida, :puntuacion_restaurante, :comentarios)");
        $consulta->bindValue(":cod_pedido", $this->cod_pedido, PDO::PARAM_STR);
        $consulta->bindValue(":codigo_mesa", $this->codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(":puntuacion_mesa", $this->puntuacion_mesa, PDO::PARAM_INT);
        $consulta->bindValue(":puntuacion_mozo", $this->puntuacion_mozo, PDO::PARAM_INT);
        $consulta->bindValue(":puntuacion_comida", $this->puntuacion_comida, PDO::PARAM_INT);
        $consulta->bindValue(":puntuacion_restaurante", $this->puntuacion_restaurante, PDO::PARAM_INT);
        $consulta->bindValue(":comentarios", $this->comentarios, PDO::PARAM_STR);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ValidarPuntuacion($puntuacion){
        return ($puntuacion >= 1 && $puntuacion <= 10);
    }

    public function validarComentarios($comentarios){
        if(strlen($comentarios) <= 66){
            return true;
        } else {
            http_response_code(400);
            echo 'Nos importa tu opinión, pero el comentario es muy extenso. Máximo 66 caracteres!';
            exit();
        }
    }

    public static function obtenerMejorComentariosMesa()
    {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("
            SELECT codigo_mesa, AVG((puntuacion_mesa + puntuacion_mozo + puntuacion_comida + puntuacion_restaurante) / 4) AS promedio_puntuacion 
            FROM encuestas 
            GROUP BY codigo_mesa 
            ORDER BY promedio_puntuacion DESC 
            LIMIT 1");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
    
    public static function obtenerPeorComentariosMesa()
    {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("
            SELECT codigo_mesa, AVG((puntuacion_mesa + puntuacion_mozo + puntuacion_comida + puntuacion_restaurante) / 4) AS promedio_puntuacion 
            FROM encuestas 
            GROUP BY codigo_mesa 
            ORDER BY promedio_puntuacion ASC 
            LIMIT 1");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
    
}
