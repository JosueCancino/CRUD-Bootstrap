CREATE TABLE `tbl_empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `edad` int(11) DEFAULT NULL,
  `sexo` varchar(10) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_empleados` (`id`, `nombre`, `edad`, `sexo`, `telefono`, `cargo`, `avatar`)
VALUES
	(3,'Any somosa',23,'Femenino','432432432','Asistente','a26b9df685.png'),
	(4,'Urian',31,'Masculino','432432432','Asistente','f752ce2c9b.png'),
	(6,'Abelado P',39,'Masculino','23213213','Desarrollador','b70032d832.png'),
	(7,'Camilo',30,'Masculino','333434','Contador','daea327347.jpg'),
	(8,'Fabio',49,'Masculino','4444443','Secretario','dd12c93c0a.png'),
	(9,'Brenda Cataleya',18,'Masculino','5565656','Desarrollador Web','6a712f30fc.png');

CREATE TABLE `tbl_contratos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_contrato` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_contratos` (`id`, `tipo_contrato`)
VALUES
  (1, 'Tiempo completo'),
  (2, 'Medio tiempo'),
  (3, 'Freelance'),
  (4, 'Pasantía');

CREATE TABLE `tbl_detalle_contrato` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `salario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_empleado` (`empleado_id`),
  KEY `fk_contrato` (`contrato_id`),
  CONSTRAINT `fk_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `tbl_empleados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_contrato` FOREIGN KEY (`contrato_id`) REFERENCES `tbl_contratos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_detalle_contrato` (`id`, `empleado_id`, `contrato_id`, `fecha_inicio`, `fecha_fin`, `salario`)
VALUES
  (1, 3, 1, '2024-01-01', NULL, 2500.00), -- Any somosa - Tiempo completo
  (2, 4, 2, '2023-12-01', NULL, 1500.00), -- Urian - Medio tiempo
  (3, 6, 3, '2025-01-15', NULL, 1200.00), -- Abelado P - Freelance
  (4, 7, 1, '2022-06-01', '2024-06-01', 3000.00), -- Camilo - Tiempo completo (contrato finalizado)
  (5, 8, 2, '2025-03-01', NULL, 1700.00), -- Fabio - Medio tiempo
  (6, 9, 4, '2025-02-01', '2025-08-01', 800.00); -- Brenda Cataleya - Pasantía
