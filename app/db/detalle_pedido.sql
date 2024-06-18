CREATE TABLE detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    estado TEXT DEFAULT 'esperando preparacion',
    tiempo_estimado INT DEFAULT 30,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id),
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);