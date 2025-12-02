CREATE DATABASE IF NOT EXISTS burger;
USE burger;

CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    celular VARCHAR(20),
    direccion VARCHAR(200),
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50),
    imagen VARCHAR(200)
);

CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    estado ENUM('Pendiente','Completado','Cancelado') DEFAULT 'Pendiente',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pedido_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS administradores (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO productos (nombre, descripcion, precio, categoria, imagen) VALUES
('Hamburguesa Clásica', 'Carne de res, queso, lechuga, tomate, pan artesanal', 85.00, 'Hamburguesa', 'Hamburguesa_clasica.jpg'),
('Hamburguesa Doble Queso', 'Doble carne de res, doble queso cheddar, cebolla caramelizada', 120.00, 'Hamburguesa', 'hamburguesa_doble.jpg'),
('Papas Fritas', 'Papas fritas crujientes', 35.00, 'Complementos', 'papas.jpg'),
('Refresco Cola', 'Refresco de cola 500ml', 20.00, 'Bebidas', 'refresco_cola.jpg'),
('Agua Mineral', 'Agua natural 500ml', 15.00, 'Bebidas', 'agua.jpg');
