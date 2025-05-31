-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 10:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `watchful_eye`
--

-- --------------------------------------------------------

--
-- Table structure for table `apartamento`
--

CREATE TABLE `apartamento` (
  `num_apt` int(11) NOT NULL,
  `id_torre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apartamento`
--

INSERT INTO `apartamento` (`num_apt`, `id_torre`) VALUES
(101, 1),
(202, 1),
(303, 1),
(404, 1),
(505, 1),
(100, 2),
(200, 2),
(300, 2),
(400, 2),
(500, 2);

-- --------------------------------------------------------

--
-- Table structure for table `camara`
--

CREATE TABLE `camara` (
  `id_camara` int(11) NOT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `tipo_camara` varchar(50) DEFAULT NULL,
  `id_tipo_comportamiento` int(11) DEFAULT NULL,
  `id_torre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `camara`
--

INSERT INTO `camara` (`id_camara`, `ubicacion`, `tipo_camara`, `id_tipo_comportamiento`, `id_torre`) VALUES
(101, 'Entrada Principal', 'PTZ', NULL, 1),
(102, 'Pasillo Norte', 'Fija', NULL, 2),
(103, 'Estacionamiento', 'Bullet', NULL, 1),
(104, 'Salida de Emergencia', 'Domótica', NULL, 2),
(105, 'Recepción', 'Oculta', NULL, 1),
(106, 'Pasillo Sur', 'Fija', NULL, 2),
(107, 'Área de Juegos', 'PTZ', NULL, 1),
(108, 'Ascensor 1', 'Domótica', NULL, 2),
(109, 'ubicacion', '109', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `comportamiento`
--

CREATE TABLE `comportamiento` (
  `id_comportamiento` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_riesgo` int(11) DEFAULT NULL,
  `id_camara` int(11) DEFAULT NULL,
  `id_persona` int(11) DEFAULT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `gravedad` int(11) DEFAULT NULL,
  `id_tipo_comportamiento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comportamiento`
--

INSERT INTO `comportamiento` (`id_comportamiento`, `descripcion`, `id_riesgo`, `id_camara`, `id_persona`, `fecha_hora`, `gravedad`, `id_tipo_comportamiento`) VALUES
(6, 'xxxxxxxxxxxxxxxx', NULL, NULL, 24, '2025-05-26 07:33:18', NULL, NULL),
(7, 'dfgbhytrfyghgtjtgjgjghy', NULL, NULL, 32, '2025-05-31 16:27:56', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `incidente`
--

CREATE TABLE `incidente` (
  `id_incidente` int(11) NOT NULL,
  `id_comportamiento` int(11) DEFAULT NULL,
  `fecha_hora_inicio` datetime DEFAULT NULL,
  `fecha_hora_fin` datetime DEFAULT NULL,
  `estado` enum('reportado','en_proceso','resuelto') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incidente`
--

INSERT INTO `incidente` (`id_incidente`, `id_comportamiento`, `fecha_hora_inicio`, `fecha_hora_fin`, `estado`) VALUES
(6, 6, '2025-05-26 07:33:18', NULL, 'reportado'),
(7, 7, '2025-05-31 16:27:56', NULL, 'reportado');

-- --------------------------------------------------------

--
-- Table structure for table `intentos_login`
--

CREATE TABLE `intentos_login` (
  `id` int(11) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `intentos` int(11) DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `persona`
--

CREATE TABLE `persona` (
  `id_persona` int(11) NOT NULL,
  `nombre1` varchar(50) DEFAULT NULL,
  `apellido1` varchar(50) DEFAULT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `tipo_persona` enum('P','A','V','X') DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `rol` varchar(20) DEFAULT 'usuario',
  `password` varchar(255) DEFAULT NULL,
  `tipo_documento` varchar(5) DEFAULT NULL,
  `numero_documento` varchar(20) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `persona`
--

INSERT INTO `persona` (`id_persona`, `nombre1`, `apellido1`, `direccion`, `telefono`, `correo`, `tipo_persona`, `fecha_nacimiento`, `rol`, `password`, `tipo_documento`, `numero_documento`, `estado`) VALUES
(24, 'Juan Diego', 'Paz', 'carrera 46CC #70 Sur - 30', '55566666', 'pedro@example.com', 'A', '2004-04-05', 'usuario', '$2y$10$6p/JWDblVBamK9HNhGtBBOfGKj5JnTA2cFfhQ7BNqxVhFUfS/PIyW', 'CC', '55555555', 0),
(30, 'Admin', 'Principal', 'Calle Ficticia #123', '3001234567', 'admin@ejemplo.com', 'X', '2000-06-13', 'admin', '$2y$10$upt8gdupbjiefi5FH7vA1Oy9BaRohOSG6eE/CqqtkDd9R4pHrMLCu', 'CC', '1000000000', 1),
(31, 'Cesar Andres', 'Berrio', '34', '4566788887', 'reefhhjj@hgrr.com', 'A', '1996-06-18', 'usuario', '$2y$10$vcUu4xKE0s2qcI/Mo5zDXecGdSRL.LP0l4cwD.cwgiWifiMoSldOW', 'PA', '3334566332', 1),
(32, 'Maria Angel ', ' Lopez', '67', '56643224556', 'gggggg@ghgg.ciom', 'V', '2003-09-15', 'usuario', '$2y$10$/pZf5/zbGRNGYv1bR5qxhuqcx1LauYemD.dgRZBRWk.NRHZUcc.Bu', 'CE', '9876355673', 1),
(33, 'Juan Diego', 'Paz', 'carrera 46CC #70 Sur - 30', '3154523777', 'GLORIAAMPAROLORA@GMAIL.COM', 'P', '1978-01-02', 'usuario', '$2y$10$8O3Ly31TGvjqCqhzO/HDu.4sRk6aloizpimGdM6h7ixgnrbGHkRTi', 'CC', '43151932', 1),
(34, 'Jorgen Andres', 'Perez', 'calle 32', '55555555555', 'hhhhhhhh@hh.com', 'P', '2003-06-24', 'usuario', '$2y$10$o90t.M31ungtOu7Z5zZd1.5R6BjPuWcklA2m23TQ/Fn.bF/4NOVDa', 'CE', '123456789', 1);

-- --------------------------------------------------------

--
-- Table structure for table `persona_apartamento`
--

CREATE TABLE `persona_apartamento` (
  `id_persona` int(11) NOT NULL,
  `num_apt` int(11) NOT NULL,
  `id_torre` int(11) NOT NULL,
  `tipo_relacion` char(1) NOT NULL COMMENT 'P: Propietario, A: Arrendatario',
  `fecha_inicio` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `persona_apartamento`
--

INSERT INTO `persona_apartamento` (`id_persona`, `num_apt`, `id_torre`, `tipo_relacion`, `fecha_inicio`) VALUES
(24, 101, 1, 'P', NULL),
(31, 100, 2, 'A', NULL),
(33, 303, 1, 'P', NULL),
(34, 300, 2, 'P', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reset_password_tokens`
--

CREATE TABLE `reset_password_tokens` (
  `id` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reset_password_tokens`
--

INSERT INTO `reset_password_tokens` (`id`, `correo`, `token`, `expiracion`) VALUES
(4, 'gggggg@ghgg.ciom', 'b82b2901efd11aa42fd2be55f57d36165183e403f66b3d82d211c9096c3259b1', '2025-06-01 01:11:18');

-- --------------------------------------------------------

--
-- Table structure for table `torre`
--

CREATE TABLE `torre` (
  `id_torre` int(11) NOT NULL,
  `numero_pisos` int(11) DEFAULT NULL,
  `numeros_aptos_por_piso` int(11) DEFAULT NULL,
  `id_unidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `torre`
--

INSERT INTO `torre` (`id_torre`, `numero_pisos`, `numeros_aptos_por_piso`, `id_unidad`) VALUES
(1, 5, 1, NULL),
(2, 5, 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apartamento`
--
ALTER TABLE `apartamento`
  ADD PRIMARY KEY (`num_apt`),
  ADD UNIQUE KEY `uk_apt_torre` (`num_apt`,`id_torre`),
  ADD KEY `id_torre` (`id_torre`);

--
-- Indexes for table `camara`
--
ALTER TABLE `camara`
  ADD PRIMARY KEY (`id_camara`),
  ADD KEY `id_tipo_comportamiento` (`id_tipo_comportamiento`),
  ADD KEY `id_torre` (`id_torre`);

--
-- Indexes for table `comportamiento`
--
ALTER TABLE `comportamiento`
  ADD PRIMARY KEY (`id_comportamiento`),
  ADD KEY `id_riesgo` (`id_riesgo`),
  ADD KEY `id_camara` (`id_camara`),
  ADD KEY `id_persona` (`id_persona`),
  ADD KEY `id_tipo_comportamiento` (`id_tipo_comportamiento`);

--
-- Indexes for table `incidente`
--
ALTER TABLE `incidente`
  ADD PRIMARY KEY (`id_incidente`),
  ADD KEY `id_comportamiento` (`id_comportamiento`);

--
-- Indexes for table `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`id_persona`);

--
-- Indexes for table `persona_apartamento`
--
ALTER TABLE `persona_apartamento`
  ADD PRIMARY KEY (`id_persona`,`num_apt`,`id_torre`),
  ADD KEY `num_apt` (`num_apt`,`id_torre`);

--
-- Indexes for table `reset_password_tokens`
--
ALTER TABLE `reset_password_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `torre`
--
ALTER TABLE `torre`
  ADD PRIMARY KEY (`id_torre`),
  ADD KEY `id_unidad` (`id_unidad`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apartamento`
--
ALTER TABLE `apartamento`
  MODIFY `num_apt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=506;

--
-- AUTO_INCREMENT for table `camara`
--
ALTER TABLE `camara`
  MODIFY `id_camara` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `comportamiento`
--
ALTER TABLE `comportamiento`
  MODIFY `id_comportamiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `incidente`
--
ALTER TABLE `incidente`
  MODIFY `id_incidente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `persona`
--
ALTER TABLE `persona`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `reset_password_tokens`
--
ALTER TABLE `reset_password_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `torre`
--
ALTER TABLE `torre`
  MODIFY `id_torre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apartamento`
--
ALTER TABLE `apartamento`
  ADD CONSTRAINT `apartamento_ibfk_1` FOREIGN KEY (`id_torre`) REFERENCES `torre` (`id_torre`);

--
-- Constraints for table `camara`
--
ALTER TABLE `camara`
  ADD CONSTRAINT `camara_ibfk_1` FOREIGN KEY (`id_tipo_comportamiento`) REFERENCES `tipo_comportamiento` (`id_tipo_comportamiento`),
  ADD CONSTRAINT `camara_ibfk_2` FOREIGN KEY (`id_torre`) REFERENCES `torre` (`id_torre`);

--
-- Constraints for table `comportamiento`
--
ALTER TABLE `comportamiento`
  ADD CONSTRAINT `comportamiento_ibfk_1` FOREIGN KEY (`id_riesgo`) REFERENCES `nivel_riesgo` (`id_riesgo`),
  ADD CONSTRAINT `comportamiento_ibfk_2` FOREIGN KEY (`id_camara`) REFERENCES `camara` (`id_camara`),
  ADD CONSTRAINT `comportamiento_ibfk_3` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`),
  ADD CONSTRAINT `comportamiento_ibfk_4` FOREIGN KEY (`id_tipo_comportamiento`) REFERENCES `tipo_comportamiento` (`id_tipo_comportamiento`);

--
-- Constraints for table `incidente`
--
ALTER TABLE `incidente`
  ADD CONSTRAINT `incidente_ibfk_1` FOREIGN KEY (`id_comportamiento`) REFERENCES `comportamiento` (`id_comportamiento`);

--
-- Constraints for table `persona_apartamento`
--
ALTER TABLE `persona_apartamento`
  ADD CONSTRAINT `persona_apartamento_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `persona` (`id_persona`) ON DELETE CASCADE,
  ADD CONSTRAINT `persona_apartamento_ibfk_2` FOREIGN KEY (`num_apt`,`id_torre`) REFERENCES `apartamento` (`num_apt`, `id_torre`) ON DELETE CASCADE;

--
-- Constraints for table `torre`
--
ALTER TABLE `torre`
  ADD CONSTRAINT `torre_ibfk_1` FOREIGN KEY (`id_unidad`) REFERENCES `unidad_residencial` (`id_unidad`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
