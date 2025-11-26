-- --------------------------------------------------
-- Base de datos: BURGER (versión final)
-- --------------------------------------------------
CREATE DATABASE IF NOT EXISTS burger;
USE burger;

-- --------------------------------------------------
-- Tabla: clientes
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    direccion VARCHAR(200),
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- Tabla: productos
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50),
    imagen VARCHAR(200)
);

-- --------------------------------------------------
-- Tabla: pedidos
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    estado ENUM('Pendiente','Completado','Cancelado') DEFAULT 'Pendiente',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE
);

-- --------------------------------------------------
-- Tabla: pedido_detalle
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS pedido_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
);

-- --------------------------------------------------
-- Tabla: administradores
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS administradores (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------
-- Datos de ejemplo: productos
-- --------------------------------------------------
INSERT INTO productos (nombre, descripcion, precio, categoria, imagen) VALUES
('Hamburguesa Clásica', 'Carne de res, queso, lechuga, tomate, pan artesanal', 85.00, 'Hamburguesa', 'imagenes/hamburguesa_clasica.jpg'),
('Hamburguesa Doble Queso', 'Doble carne de res, doble queso cheddar, cebolla caramelizada', 120.00, 'Hamburguesa', 'imagenes/hamburguesa_doble.jpg'),
('Papas Fritas', 'Papas fritas crujientes', 35.00, 'Complementos', 'imagenes/papas.jpg'),
('Refresco Cola', 'Refresco de cola 500ml', 20.00, 'Bebidas', 'imagenes/refresco_cola.jpg'),
('Agua Mineral', 'Agua natural 500ml', 15.00, 'Bebidas', 'imagenes/agua.jpg');

-- --------------------------------------------------
-- Datos de ejemplo: clientes
-- --------------------------------------------------
INSERT INTO clientes (nombre, correo, telefono, direccion, password) VALUES
('Juan Pérez', 'juan@example.com', '5512345678', 'Calle Falsa 123', '$2y$10$examplehashedpassword1'),
('María López', 'maria@example.com', '5598765432', 'Avenida Siempre Viva 742', '$2y$10$examplehashedpassword2');

-- --------------------------------------------------
-- Datos de ejemplo: administradores
-- --------------------------------------------------
INSERT INTO administradores (nombre, correo, password) VALUES
('Admin Principal', 'admin@burger.com', '$2y$10$examplehashedpasswordadmin');

-- --------------------------------------------------
-- Datos de ejemplo: pedidos
-- --------------------------------------------------
INSERT INTO pedidos (id_cliente, total, metodo_pago, estado) VALUES
(1, 105.00, 'Efectivo', 'Pendiente'),
(2, 155.00, 'Tarjeta', 'Completado');

-- --------------------------------------------------
-- Datos de ejemplo: pedido_detalle
-- --------------------------------------------------
INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, subtotal) VALUES
(1, 1, 1, 85.00),
(1, 4, 1, 20.00),
(2, 2, 1, 120.00),
(2, 3, 1, 35.00);
