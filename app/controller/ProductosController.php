<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


require_once '../app/model/Productos.php';


use Fpdf\Fpdf;

class ProductosController {

    public function AsignarProducto(Request $request, Response $response, $args){

        $data = $request->getParsedBody();
        $producto = new Productos();

        $producto->nombre_producto = $data['nombre_producto'];
        $producto->sector = $data['sector'];
        $producto->stock = $data['stock'];
        $producto->precio = $data['precio'];
        $mensaje= $producto->altaProducto();

        $response->getBody()->write(json_encode(['mensaje' => "Producto creado exitosamente!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarProducto(Request $request, Response $response, $args){
        $producto = new Productos();
        $consulta = $producto->leerProductos();
        $listaProductos = $consulta->fetchAll(PDO::FETCH_ASSOC);
        $response -> getBody()->write(json_encode($listaProductos));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listarPorSector(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $tipo_producto = $data['tipo'];
    
        $producto = new Productos();
        $consulta = $producto->leerPorSector($tipo_producto);
        $listaProductos = $consulta->fetchAll(PDO::FETCH_ASSOC);
    
        $response->getBody()->write(json_encode($listaProductos));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function cargarArchivosCSV(Request $request, Response $response, $args){
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


    public function exportarArchivosPDF(Request $request, Response $response, $args) {
        try {
            $productos = new Productos();
            $listaProductos = $productos->leerProductos()->fetchAll(PDO::FETCH_ASSOC);
            $pdf = new Fpdf();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 14);

            // columnas
            $pdf->Cell(40, 10, 'Nombre Producto', 1);
            $pdf->Cell(30, 10, 'Sector', 1);
            $pdf->Cell(20, 10, 'Stock', 1);
            $pdf->Cell(30, 10, 'Precio', 1);
            $pdf->Ln();
            foreach ($listaProductos as $producto) {
                $pdf->Cell(40, 10, $producto['nombre_producto'], 1);
                $pdf->Cell(30, 10, $producto['sector'], 1);
                $pdf->Cell(20, 10, $producto['stock'], 1);
                $pdf->Cell(30, 10, $producto['precio'], 1);
                $pdf->Ln();
            }
            $response->getBody()->write($pdf->Output('S'));
            return $response
                ->withHeader('Content-Type', 'application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="productos.pdf"')
                ->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write("Error: " . $e->getMessage());
            return $response->withHeader('Content-Type', 'text/plain')->withStatus(500);
        }
    } 
} 

