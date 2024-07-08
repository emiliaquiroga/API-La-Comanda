<?php
use Slim\Psr7\Request;
use Psr\Http\Message\ResponseInterface as Response;

class LogInController{
    public function iniciarSesion(Request $request, Response $response){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $password = $parametros['password'];

        
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($resultado && password_verify($password, $resultado['password'])) {
            $datos = array('usuario' => $usuario, 'tipo_usuario' => $resultado['tipo_usuario']);
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        } else {
            $payload = json_encode(array('error' => 'Usuario o contraseña incorrectos'));
            $response = $response->withStatus(401);
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}