-- Tabla INDEPENDIENTE para publicadores
CREATE TABLE publicadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    titulo_academico VARCHAR(100) NULL,
    institucion VARCHAR(150) NULL,
    departamento VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    biografia TEXT NULL,
    avatar VARCHAR(255) NULL,
    areas_interes JSON NULL,
    experiencia_años INT DEFAULT 0,
    cv_url VARCHAR(255) NULL,
    orcid_id VARCHAR(20) NULL,
    linkedin_url VARCHAR(255) NULL,
    permisos JSON NULL,
    limite_publicaciones_mes INT DEFAULT 10,
    publicaciones_este_mes INT DEFAULT 0,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    fecha_activacion TIMESTAMP NULL,
    estado ENUM('activo', 'pendiente', 'suspendido', 'inactivo') DEFAULT 'pendiente',
    motivo_suspension TEXT NULL,
    notificaciones_email BOOLEAN DEFAULT TRUE,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de publicaciones
CREATE TABLE publicaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    slug VARCHAR(220) UNIQUE NOT NULL,
    contenido LONGTEXT NOT NULL,
    resumen TEXT NULL,
    imagen_principal VARCHAR(255) NULL,
    publicador_id INT NOT NULL,
    categoria_id INT NOT NULL,
    estado ENUM('publicado', 'borrador', 'revision', 'rechazado') DEFAULT 'borrador',
    tipo ENUM('articulo', 'noticia', 'tutorial', 'investigacion') DEFAULT 'articulo',
    fecha_publicacion TIMESTAMP NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    vistas INT DEFAULT 0,
    likes INT DEFAULT 0,
    meta_descripcion VARCHAR(300) NULL,
    tags JSON NULL,
    FOREIGN KEY (publicador_id) REFERENCES publicadores(id) ON DELETE CASCADE
);

-- Tabla de categorías
CREATE TABLE categorias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    descripcion TEXT NULL,
    color VARCHAR(7) DEFAULT '#007acc',
    icono VARCHAR(50) NULL,
    estado ENUM('activa', 'inactiva') DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);