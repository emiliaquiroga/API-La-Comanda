<?php

require_once '../app/model/Usuario.php';
require_once '../app/model/Encuesta.php';

class EncuestaController {

    public static function Cargar($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $cod_pedido = $parametros['cod_pedido'];
        $codigo_mesa = $parametros['codigo_mesa'];
        $puntuacion_mesa = $parametros['puntuacion_mesa'];
        $puntuacion_mozo = $parametros['puntuacion_mozo'];
        $puntuacion_comida = $parametros['puntuacion_comida'];
        $puntuacion_restaurante = $parametros['puntuacion_restaurante'];
        $comentarios = $parametros['comentarios'];

        if (!empty($comentarios) && !empty($cod_pedido)) {
            if (Pedidos::obtenerPorCodigoPedido($cod_pedido)) {
                $encuesta = new Encuesta();
                $encuesta->cod_pedido = $cod_pedido;
                $encuesta->codigo_mesa = $codigo_mesa;
                $encuesta->puntuacion_mesa = $puntuacion_mesa;
                $encuesta->puntuacion_mozo = $puntuacion_mozo;
                $encuesta->puntuacion_comida = $puntuacion_comida;
                $encuesta->puntuacion_restaurante = $puntuacion_restaurante;
                $encuesta->comentarios = $comentarios;

                try {
                    $resultado = $encuesta->crearEncuesta();
                    $payload = json_encode(array("Mensaje" => "Encuesta cargada con exito!"));
                } catch (Exception $e) {
                    $payload = json_encode(array("Error" => $e->getMessage()));
                    echo "problemilla";
                }
            } else {
                $payload = json_encode(array("Error" => "Codigo de pedido no encontrado"));
            }
        } else {
            $payload = json_encode(array("Error" => "Los campos cod_pedido y comentarios son obligatorios"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader("Content-type", "application/json");
    }

    public static function TraerMejoresComentarios($request, $response, $args)
    {
        $lista = Encuesta::obtenerMejorComentariosMesa();
        $payload = json_encode(array("listaMesa" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    } 

    public static function TraerPeoresComentarios($request, $response, $args)
    {
        $lista = Encuesta::obtenerPeorComentariosMesa();
        $payload = json_encode(array("listaMesa" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }
}
