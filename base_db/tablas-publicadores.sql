CREATE TABLE `publicadores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `especialidad` varchar(100) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo_academico` varchar(100) DEFAULT NULL,
  `institucion` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `experiencia_años` int(11) DEFAULT 0,
  `limite_publicaciones_mes` int(11) DEFAULT 10,
  `publicaciones_este_mes` int(11) DEFAULT 0,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_activacion` timestamp NULL DEFAULT NULL,
  `estado` enum('activo','pendiente','suspendido','inactivo') DEFAULT 'pendiente',
  `motivo_suspension` text DEFAULT NULL,
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `foto_perfil` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0 COMMENT 'Si el publicador tiene 2FA activado',
  `blocked_until` datetime DEFAULT NULL COMMENT 'Bloqueado hasta esta fecha'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE publicadores 
ADD COLUMN motivo_rechazo TEXT NULL AFTER estado;



CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `contenido` longtext NOT NULL,
  `resumen` text DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `publicador_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `estado` enum('publicado','borrador','revision','rechazado','rechazada') DEFAULT NULL,
  `mensaje_rechazo` text DEFAULT NULL,
  `tipo` enum('articulo','noticia','tutorial','investigacion') DEFAULT 'articulo',
  `fecha_publicacion` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `vistas` int(11) DEFAULT 0,
  `likes` int(11) DEFAULT 0,
  `meta_descripcion` varchar(300) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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