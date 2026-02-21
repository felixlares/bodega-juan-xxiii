CREATE DATABASE IF NOT EXISTS bodega_juan_xxiii CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bodega_juan_xxiii;

CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tienda VARCHAR(255) NOT NULL,
    direccion TEXT,
    google_maps_url TEXT,
    whatsapp VARCHAR(50),
    email VARCHAR(255),
    horarios VARCHAR(255),
    redes_sociales JSON
);

CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    precio DECIMAL(10, 2) NOT NULL,
    precio_anterior DECIMAL(10, 2),
    descripcion TEXT,
    categoria ENUM('ropa', 'accesorios', 'hogar', 'electricidad', 'ferreteria', 'viveres') NOT NULL,
    imagen_principal VARCHAR(255),
    galeria JSON,
    video_url VARCHAR(255),
    sku VARCHAR(100),
    stock INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    destacado BOOLEAN DEFAULT FALSE,
    tags JSON,
    peso DECIMAL(10, 2),
    dimensiones_alto DECIMAL(10, 2),
    dimensiones_ancho DECIMAL(10, 2),
    dimensiones_profundidad DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar configuración por defecto si la tabla está vacía
INSERT INTO configuracion (nombre_tienda, direccion, google_maps_url, whatsapp, email, horarios, redes_sociales)
SELECT 
    'Bodega Juan XXIII',
    'Las 40 Calle 9 frente AGEL',
    'https://maps.google.com/?q=Las+40+Calle+9+frente+AGEL',
    '0412-1614868',
    'felixlares@gmail.com',
    '24 horas',
    '{"facebook": "", "instagram": "", "tiktok": ""}'
WHERE NOT EXISTS (SELECT 1 FROM configuracion);
