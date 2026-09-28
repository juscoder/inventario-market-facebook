CREATE DATABASE IF NOT EXISTS inventario_remates CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventario_remates;

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    tags VARCHAR(255) DEFAULT NULL,
    imagen_url VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    clave_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin por defecto: admin / password123 (cambiar tras el primer ingreso)
INSERT IGNORE INTO usuarios (usuario, clave_hash)
VALUES ('admin', '$2y$10$IuSmIQePVv2UcR1unWNMq.IGYK00CaRqpCdwKeblOG5XTAcIfdkqy');
