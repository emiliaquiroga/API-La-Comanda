CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    codigo_mesa VARCHAR(50) NOT NULL,
    id_producto INT NOT NULL,
    sector TEXT NOT NULL,
    cantidad INT NOT NULL,
    precio_producto INT NOT NULL,
    estado TEXT DEFAULT 'pendiente',
    tiempo_estimado INT DEFAULT 30,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id),
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);
