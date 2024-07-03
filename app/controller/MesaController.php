<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Mesa.php';

require_once '../app/model/Usuario.php';


class MesaController {


    public function AbrirMesa(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $mesa = new Mesa();
        $mesa->codigo_mesa = self::generarCodigo();
        $mesa->estado = $data['estado'];
        $mensaje= $mesa->altaMesa();
        $response->getBody()->write(json_encode(['mensaje' => "Mesa abierta exitosamente!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    function listarMesas(Request $request, Response $response, $args){
        $mesa = new Mesa();
        $consulta = $mesa->leerMesas();
        $listaMesas = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response -> getBody()->write(json_encode($listaMesas));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function modificarEstadoMesa(Request $request, Response $response, array $args) {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $nuevo_estado = $parametros['estado'];

        $mesa = new Mesa();
        $mesa->id = $id;
        $mesa->estado = $nuevo_estado;
        $mesa->modificarEstadoMesa();

        $payload = json_encode(array("mensaje" => "Estado de la mesa modificado"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function generarCodigo() {
        $caracteres = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $ultimoId = ManipularDatos::obtenerInstancia()->obtenerUltimoId();
        $numero = (int)$ultimoId + 1;
        if (strlen($numero) == 1) {
            $letras = substr(str_shuffle($caracteres), 0, 4);
            $codigo = $letras . $numero;
        } else {
            $letras = substr(str_shuffle($caracteres), 0, 3);
            $codigo = $letras . str_pad($numero, 2, '0', STR_PAD_LEFT);
        }
        return $codigo;
    }


    public function obtenerMasUsada(Request $request, Response $response, array $args) {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, COUNT(id_mesa) AS cantidad FROM pedidos GROUP BY id_mesa ORDER BY cantidad DESC LIMIT 1");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        
        $payload = json_encode($resultado);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function obtenerMenosUsada(Request $request, Response $response, array $args) {
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, COUNT(id_mesa) AS cantidad FROM pedidos GROUP BY id_mesa ORDER BY cantidad ASC LIMIT 1");
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        
        $payload = json_encode($resultado);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}