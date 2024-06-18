-- Crear la tabla pedido usando TEXT en lugar de JSON
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    cod_pedido VARCHAR(255) NOT NULL,
    nombre_cliente TEXT,
    contenido TEXT,
    estado TEXT DEFAULT 'esperando preparacion',
    tiempo_estimado TEXT DEFAULT 'esperando preparacion',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
