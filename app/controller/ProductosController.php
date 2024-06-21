<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/model/Productos.php';
require_once 'Bebida.php';
require_once 'Comida.php';

class ProductosController {

    public function AsignarProducto(Request $request, Response $response, $args){

        $data = $request->getParsedBody();

        switch($data['tipo']){
            case 'comida':
                $producto = new Comida();
                break;
            case 'bebida':
                $producto = new Bebida();
                break;
            
            default:
                $response->getBody()->write(json_encode(["message"=>"Producto no valido"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        
        }
        $producto->nombreProducto = $data['nombreProducto'];
        $producto->tipo = $data['tipo'];
        $producto->stock = $data['stock'];
        $producto->precio = $data['precio'];
        $mensaje= $producto->altaProducto();

        $response->getBody()->write(json_encode(['mensaje' => "Producto creado exitosamente reinona!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarProducto(Request $request, Response $response, $args){
        $producto = new Productos();
        $consulta = $producto->leerProductos();
        $listaProductos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response -> getBody()->write(json_encode($listaProductos));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function cargarArchivosCSV(Request $request, Response $response, $args){
        //$parametros = $request->getUploadedFiles();
        //var_dump($parametros);
        $archivo = $_FILES['archivo']['tmp_name'];
        //$archivo = $parametros['archivo']['tmp_name'];

        if (!isset($archivo)) {
            $response->getBody()->write("Error al recibir por parametro.");
            return $response->withHeader('Content-Type', 'text/plain')->withStatus(400);
        }

        $fh = fopen($archivo, "r");
        if ($fh === false) {
            exit("Error al abrir el archivo CSV.");
        }
        try {
            $productos = new Productos();
            $productos->cargarArchivosCSV($archivo);
            echo "Proceso de carga de CSV terminado.";            
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        fclose($fh);
        echo "se cerro el archivo";
    } 
    public function exportarArchivosCSV(Request $request, Response $response, $args) {
        try {
            $productos = new Productos();
            $csv = $productos->exportarArchivoCSV();

            $response->getBody()->write($csv);
            return $response
                ->withHeader('Content-Type', 'text/csv')
                ->withHeader('Content-Disposition', 'attachment; filename="exportacion.csv"')
                ->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write("Error: " . $e->getMessage());
            return $response->withHeader('Content-Type', 'text/plain')->withStatus(500);
        }
    }
} 

