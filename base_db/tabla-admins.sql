CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nivel` enum('superadmin','admin') DEFAULT 'admin',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `foto_perfil` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0 COMMENT 'Si el admin tiene 2FA activado',
  `blocked_until` datetime DEFAULT NULL COMMENT 'Bloqueado hasta esta fecha',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;