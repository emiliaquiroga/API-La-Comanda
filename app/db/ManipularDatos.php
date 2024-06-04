
<?php

class ManipularDatos{
    private static $objAccesoDatos;
    public $pdo;
    
    private function __construct(){
        $this->iniciarConexion();
    }

    private function iniciarConexion(){
        try {
            $conStr = 'mysql:host=localhost; dbname=la_comarca';
            $this->pdo = new PDO($conStr, "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            print "Error: " . $e->getMessage();
            die();
        }
    }
    public static function obtenerInstancia()
    {
        if (!isset(self::$objAccesoDatos)) {
            self::$objAccesoDatos = new ManipularDatos();
        }
        return self::$objAccesoDatos;
    }

    public function prepararConsulta($sql)
    {
        return $this->pdo->prepare($sql);
    }
    public function obtenerUltimoId()
    {
        return $this->pdo->lastInsertId();
    }

    public function obtenerConexion(){
        return $this->pdo;
    }
}

