
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    nivel ENUM('superadmin', 'admin') DEFAULT 'admin',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo'
);

