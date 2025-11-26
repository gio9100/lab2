CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena_hash` varchar(255) NOT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `rol` ENUM('usuario', 'admin') DEFAULT 'usuario',
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` TIMESTAMP NULL,
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;