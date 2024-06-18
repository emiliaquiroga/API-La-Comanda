CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto TEXT NOT NULL,
    tipo_producto TEXT NOT NULL,
    stock INT NOT NULL,
    precio INT NOT NULL
);