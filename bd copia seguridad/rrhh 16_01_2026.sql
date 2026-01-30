-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-01-2026 a las 16:00:56
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `rrhh`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `cargo_id` int(11) NOT NULL,
  `nombre_cargo` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`cargo_id`, `nombre_cargo`) VALUES
(1, 'administrador'),
(2, 'trabajador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_vacaciones`
--

CREATE TABLE `configuracion_vacaciones` (
  `id_config` int(11) NOT NULL,
  `dias_por_ano` int(11) NOT NULL DEFAULT 15,
  `dias_adicionales_por_ano` int(11) DEFAULT 1,
  `max_dias_acumulables` int(11) DEFAULT 30,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion_vacaciones`
--

INSERT INTO `configuracion_vacaciones` (`id_config`, `dias_por_ano`, `dias_adicionales_por_ano`, `max_dias_acumulables`, `activo`) VALUES
(1, 15, 1, 30, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_asignacion`
--

CREATE TABLE `detalle_asignacion` (
  `id_detalle_asig` int(11) NOT NULL,
  `id_detalle` int(11) NOT NULL,
  `id_asignacion` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_asignacion`
--

INSERT INTO `detalle_asignacion` (`id_detalle_asig`, `id_detalle`, `id_asignacion`, `monto`) VALUES
(1, 1, 1, 0.00),
(2, 1, 2, 2500.00),
(3, 1, 3, 0.00),
(4, 2, 1, 0.00),
(5, 2, 2, 90.00),
(6, 2, 3, 0.00),
(7, 3, 1, 0.00),
(8, 3, 2, 2500.00),
(9, 3, 3, 0.00),
(10, 4, 1, 0.00),
(11, 4, 2, 90.00),
(12, 4, 3, 0.00),
(13, 5, 1, 0.00),
(14, 5, 2, 2500.00),
(15, 5, 3, 0.00),
(16, 6, 1, 0.00),
(17, 6, 2, 90.00),
(18, 6, 3, 0.00),
(19, 7, 1, 0.00),
(20, 7, 2, 2500.00),
(21, 7, 3, 0.00),
(22, 8, 1, 0.00),
(23, 8, 2, 90.00),
(24, 8, 3, 0.00),
(25, 9, 1, 0.00),
(26, 9, 2, 2500.00),
(27, 9, 3, 0.00),
(28, 10, 1, 0.00),
(29, 10, 2, 90.00),
(30, 10, 3, 0.00),
(31, 11, 1, 0.00),
(32, 11, 2, 2500.00),
(33, 11, 3, 0.00),
(34, 12, 1, 0.00),
(35, 12, 2, 90.00),
(36, 12, 3, 0.00),
(37, 13, 1, 0.00),
(38, 13, 2, 2500.00),
(39, 13, 3, 0.00),
(40, 14, 1, 0.00),
(41, 14, 2, 90.00),
(42, 14, 3, 0.00),
(43, 15, 1, 0.00),
(44, 15, 2, 2500.00),
(45, 15, 3, 0.00),
(46, 16, 1, 0.00),
(47, 16, 2, 90.00),
(48, 16, 3, 0.00),
(49, 17, 1, 0.00),
(50, 17, 2, 2500.00),
(51, 17, 3, 0.00),
(52, 18, 1, 0.00),
(53, 18, 2, 90.00),
(54, 18, 3, 0.00),
(55, 19, 1, 0.00),
(56, 19, 2, 2500.00),
(57, 19, 3, 0.00),
(58, 20, 1, 0.00),
(59, 20, 2, 90.00),
(60, 20, 3, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_deduccion`
--

CREATE TABLE `detalle_deduccion` (
  `id_detalle_ded` int(11) NOT NULL,
  `id_detalle` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_deduccion`
--

INSERT INTO `detalle_deduccion` (`id_detalle_ded`, `id_detalle`, `id_tipo`, `monto`) VALUES
(1, 1, 1, 1000.00),
(2, 1, 2, 250.00),
(3, 1, 3, 125.00),
(4, 2, 1, 36.00),
(5, 2, 2, 9.00),
(6, 2, 3, 4.50),
(7, 3, 1, 1000.00),
(8, 3, 2, 250.00),
(9, 3, 3, 125.00),
(10, 4, 1, 36.00),
(11, 4, 2, 9.00),
(12, 4, 3, 4.50),
(13, 5, 1, 1000.00),
(14, 5, 2, 250.00),
(15, 5, 3, 125.00),
(16, 6, 1, 36.00),
(17, 6, 2, 9.00),
(18, 6, 3, 4.50),
(19, 7, 1, 1000.00),
(20, 7, 2, 250.00),
(21, 7, 3, 125.00),
(22, 8, 1, 36.00),
(23, 8, 2, 9.00),
(24, 8, 3, 4.50),
(25, 9, 1, 1000.00),
(26, 9, 2, 250.00),
(27, 9, 3, 125.00),
(28, 10, 1, 36.00),
(29, 10, 2, 9.00),
(30, 10, 3, 4.50),
(31, 11, 1, 1000.00),
(32, 11, 2, 250.00),
(33, 11, 3, 125.00),
(34, 12, 1, 36.00),
(35, 12, 2, 9.00),
(36, 12, 3, 4.50),
(37, 13, 1, 1000.00),
(38, 13, 2, 250.00),
(39, 13, 3, 125.00),
(40, 14, 1, 36.00),
(41, 14, 2, 9.00),
(42, 14, 3, 4.50),
(43, 15, 1, 1000.00),
(44, 15, 2, 250.00),
(45, 15, 3, 125.00),
(46, 16, 1, 36.00),
(47, 16, 2, 9.00),
(48, 16, 3, 4.50),
(49, 17, 1, 1000.00),
(50, 17, 2, 250.00),
(51, 17, 3, 125.00),
(52, 18, 1, 36.00),
(53, 18, 2, 9.00),
(54, 18, 3, 4.50),
(55, 19, 1, 1000.00),
(56, 19, 2, 250.00),
(57, 19, 3, 125.00),
(58, 20, 1, 36.00),
(59, 20, 2, 9.00),
(60, 20, 3, 4.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_nomina`
--

CREATE TABLE `detalle_nomina` (
  `id_detalle` int(11) NOT NULL,
  `id_nomina` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `salario_base` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_asignaciones` decimal(12,2) DEFAULT 0.00,
  `total_deducciones` decimal(12,2) DEFAULT 0.00,
  `total_pagar` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_nomina`
--

INSERT INTO `detalle_nomina` (`id_detalle`, `id_nomina`, `empleado_id`, `salario_base`, `total_asignaciones`, `total_deducciones`, `total_pagar`) VALUES
(1, 1, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(2, 1, 2, 900.00, 90.00, 49.50, 940.50),
(3, 2, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(4, 2, 2, 900.00, 90.00, 49.50, 940.50),
(5, 6, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(6, 6, 2, 900.00, 90.00, 49.50, 940.50),
(7, 7, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(8, 7, 2, 900.00, 90.00, 49.50, 940.50),
(9, 8, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(10, 8, 2, 900.00, 90.00, 49.50, 940.50),
(11, 9, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(12, 9, 2, 900.00, 90.00, 49.50, 940.50),
(13, 10, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(14, 10, 2, 900.00, 90.00, 49.50, 940.50),
(15, 11, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(16, 11, 2, 900.00, 90.00, 49.50, 940.50),
(17, 12, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(18, 12, 2, 900.00, 90.00, 49.50, 940.50),
(19, 13, 1, 25000.00, 2500.00, 1375.00, 26125.00),
(20, 13, 2, 900.00, 90.00, 49.50, 940.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `salario_base` decimal(10,2) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `cedula`, `nombre`, `apellido`, `direccion`, `telefono`, `email`, `fecha_ingreso`, `salario_base`, `estado`) VALUES
(1, '12345678', 'Jhon', 'Administrador', 'Caracas', '04141234567', 'jhon@example.com', '2024-01-01', 25000.00, 'activo'),
(2, '99887766', 'Carlos', 'Pérez', 'La Guaira', '04145556677', 'carlos@example.com', '2024-01-15', 10000.00, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `feriados`
--

CREATE TABLE `feriados` (
  `id_feriado` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('nacional','regional','interno') DEFAULT 'nacional',
  `obligatorio` tinyint(1) DEFAULT 1,
  `descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `feriados`
--

INSERT INTO `feriados` (`id_feriado`, `nombre`, `fecha`, `tipo`, `obligatorio`, `descripcion`, `creado_en`) VALUES
(1, 'Año Nuevo', '2025-01-01', 'nacional', 1, 'Inicio del año', '2025-12-29 14:41:48'),
(2, 'Día del Trabajo', '2025-05-01', 'nacional', 1, 'Feriado laboral', '2025-12-29 14:41:48'),
(3, 'Batalla de Carabobo', '2025-06-24', 'nacional', 1, 'Feriado histórico', '2025-12-29 14:41:48'),
(4, 'Feriado Interno Empresa', '2025-08-15', 'interno', 0, 'Aniversario empresa', '2025-12-29 14:41:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nomina`
--

CREATE TABLE `nomina` (
  `id_nomina` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `tipo` enum('semanal','quincenal','mensual') DEFAULT 'mensual',
  `estado` enum('abierta','cerrada','pagada') DEFAULT 'abierta',
  `creada_por` varchar(100) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `nomina`
--

INSERT INTO `nomina` (`id_nomina`, `fecha_inicio`, `fecha_fin`, `tipo`, `estado`, `creada_por`, `fecha_creacion`) VALUES
(1, '2025-11-10', '2025-11-16', 'semanal', 'abierta', 'jhon', '2025-11-14 15:44:10'),
(2, '2025-11-17', '2025-11-23', 'semanal', 'abierta', 'jhon', '2025-11-17 20:51:14'),
(3, '2025-11-24', '2025-11-30', 'semanal', 'abierta', 'admin', '2025-11-19 04:00:00'),
(4, '2025-11-17', '2025-11-23', 'semanal', '', 'jhon', '2025-11-19 04:00:00'),
(6, '2025-11-17', '2025-11-23', 'semanal', 'abierta', 'jhon', '2025-11-19 19:09:10'),
(7, '2025-11-17', '2025-11-23', 'semanal', 'abierta', 'jhon', '2025-11-19 19:26:33'),
(8, '2025-11-17', '2025-11-23', 'semanal', 'pagada', 'jhon', '2025-11-20 20:09:33'),
(9, '2025-02-11', '2025-02-15', 'semanal', 'abierta', 'jhon', '2025-11-30 01:53:43'),
(10, '2025-11-10', '2025-11-16', 'semanal', 'abierta', 'jhon', '2025-11-30 01:54:22'),
(11, '2025-11-10', '2025-11-16', 'semanal', 'abierta', 'jhon', '2025-11-30 02:23:00'),
(12, '2025-12-01', '2025-12-31', 'mensual', 'abierta', 'jhon', '2025-12-05 14:45:59'),
(13, '2025-11-28', '2025-12-05', 'semanal', 'pagada', 'jhon', '2025-12-05 19:15:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_nomina` int(11) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `total_pagado` decimal(14,2) DEFAULT NULL,
  `metodo` varchar(50) DEFAULT NULL,
  `notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_nomina`, `fecha_pago`, `total_pagado`, `metodo`, `notas`) VALUES
(1, 8, '2025-11-29', 27065.50, 'pago móvil', ''),
(2, 13, '2006-11-11', 27065.50, 'transferencia', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saldo_vacaciones`
--

CREATE TABLE `saldo_vacaciones` (
  `id_saldo` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `dias_disponibles` int(11) NOT NULL DEFAULT 0,
  `dias_disfrutados` int(11) NOT NULL DEFAULT 0,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_asignacion`
--

CREATE TABLE `tipo_asignacion` (
  `id_asignacion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('fijo','porcentaje') NOT NULL DEFAULT 'fijo',
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_asignacion`
--

INSERT INTO `tipo_asignacion` (`id_asignacion`, `nombre`, `tipo`, `valor`, `descripcion`) VALUES
(1, 'Bono Alimentación', 'fijo', 0.00, 'Bono en monto fijo por política'),
(2, 'Bono por Responsabilidad', 'porcentaje', 10.00, '10% sobre salario base'),
(3, 'Horas extras (ejemplo)', 'fijo', 0.00, 'Asignación por horas extras - monto calculado manualmente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_deduccion`
--

CREATE TABLE `tipo_deduccion` (
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `porcentaje` decimal(6,2) NOT NULL DEFAULT 0.00,
  `obligatorio` tinyint(1) DEFAULT 1,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_deduccion`
--

INSERT INTO `tipo_deduccion` (`id_tipo`, `nombre`, `porcentaje`, `obligatorio`, `descripcion`) VALUES
(1, 'IVSS', 4.00, 1, 'Seguro social - 4%'),
(2, 'FAOV', 1.00, 1, 'Fondo de Ahorro para la Vivienda - 1%'),
(3, 'Paro Forzoso', 0.50, 1, 'Paro Forzoso - 0.5%');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(20) DEFAULT NULL,
  `cargo_id` int(11) DEFAULT NULL,
  `nombre_apellido` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `clave`, `cargo_id`, `nombre_apellido`) VALUES
(1, 'jhon', '123', 1, 'Jhoneyker Correa'),
(2, 'empleado1', '123', 2, 'Juan Soto'),
(7, 'criss', '123', 2, 'cristian castillo'),
(8, 'ana', '123', 1, 'Ana Hernandez');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones`
--

CREATE TABLE `vacaciones` (
  `id_vacacion` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias_solicitados` int(11) NOT NULL,
  `dias_habiles` int(11) NOT NULL,
  `dias_feriados` int(11) DEFAULT 0,
  `estado` enum('pendiente','aprobada','rechazada','disfrutada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `creada_por` varchar(100) DEFAULT NULL,
  `creada_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vacaciones`
--

INSERT INTO `vacaciones` (`id_vacacion`, `empleado_id`, `fecha_inicio`, `fecha_fin`, `dias_solicitados`, `dias_habiles`, `dias_feriados`, `estado`, `observaciones`, `creada_por`, `creada_en`) VALUES
(1, 1, '2026-01-05', '2026-01-18', 14, 14, 0, 'aprobada', '', 'jhon', '2026-01-15 15:08:23'),
(2, 2, '2026-01-05', '2026-01-11', 7, 7, 0, 'aprobada', 'se le daran sus vacaciones att: jhon administrador', 'jhon', '2026-01-16 14:22:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones_feriados`
--

CREATE TABLE `vacaciones_feriados` (
  `id` int(11) NOT NULL,
  `id_vacacion` int(11) NOT NULL,
  `id_feriado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones_saldo`
--

CREATE TABLE `vacaciones_saldo` (
  `id_saldo` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `anio` int(11) NOT NULL,
  `dias_acumulados` int(11) NOT NULL,
  `dias_disfrutados` int(11) DEFAULT 0,
  `dias_pendientes` int(11) NOT NULL,
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vacaciones_saldo`
--

INSERT INTO `vacaciones_saldo` (`id_saldo`, `empleado_id`, `anio`, `dias_acumulados`, `dias_disfrutados`, `dias_pendientes`, `actualizado_en`) VALUES
(1, 1, 2026, 15, 14, 1, '2026-01-15 18:25:50'),
(2, 2, 2026, 15, 7, 8, '2026-01-16 14:22:58');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`cargo_id`);

--
-- Indices de la tabla `configuracion_vacaciones`
--
ALTER TABLE `configuracion_vacaciones`
  ADD PRIMARY KEY (`id_config`);

--
-- Indices de la tabla `detalle_asignacion`
--
ALTER TABLE `detalle_asignacion`
  ADD PRIMARY KEY (`id_detalle_asig`),
  ADD KEY `id_detalle` (`id_detalle`),
  ADD KEY `id_asignacion` (`id_asignacion`);

--
-- Indices de la tabla `detalle_deduccion`
--
ALTER TABLE `detalle_deduccion`
  ADD PRIMARY KEY (`id_detalle_ded`),
  ADD KEY `id_detalle` (`id_detalle`),
  ADD KEY `id_tipo` (`id_tipo`);

--
-- Indices de la tabla `detalle_nomina`
--
ALTER TABLE `detalle_nomina`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_nomina` (`id_nomina`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`id_feriado`);

--
-- Indices de la tabla `nomina`
--
ALTER TABLE `nomina`
  ADD PRIMARY KEY (`id_nomina`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `id_nomina` (`id_nomina`);

--
-- Indices de la tabla `saldo_vacaciones`
--
ALTER TABLE `saldo_vacaciones`
  ADD PRIMARY KEY (`id_saldo`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `tipo_asignacion`
--
ALTER TABLE `tipo_asignacion`
  ADD PRIMARY KEY (`id_asignacion`);

--
-- Indices de la tabla `tipo_deduccion`
--
ALTER TABLE `tipo_deduccion`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `cargo_id` (`cargo_id`);

--
-- Indices de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD PRIMARY KEY (`id_vacacion`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `vacaciones_feriados`
--
ALTER TABLE `vacaciones_feriados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_vacacion` (`id_vacacion`),
  ADD KEY `id_feriado` (`id_feriado`);

--
-- Indices de la tabla `vacaciones_saldo`
--
ALTER TABLE `vacaciones_saldo`
  ADD PRIMARY KEY (`id_saldo`),
  ADD UNIQUE KEY `empleado_id` (`empleado_id`,`anio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `cargo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `configuracion_vacaciones`
--
ALTER TABLE `configuracion_vacaciones`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_asignacion`
--
ALTER TABLE `detalle_asignacion`
  MODIFY `id_detalle_asig` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `detalle_deduccion`
--
ALTER TABLE `detalle_deduccion`
  MODIFY `id_detalle_ded` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `detalle_nomina`
--
ALTER TABLE `detalle_nomina`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id_feriado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `nomina`
--
ALTER TABLE `nomina`
  MODIFY `id_nomina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `saldo_vacaciones`
--
ALTER TABLE `saldo_vacaciones`
  MODIFY `id_saldo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_asignacion`
--
ALTER TABLE `tipo_asignacion`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_deduccion`
--
ALTER TABLE `tipo_deduccion`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  MODIFY `id_vacacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vacaciones_feriados`
--
ALTER TABLE `vacaciones_feriados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vacaciones_saldo`
--
ALTER TABLE `vacaciones_saldo`
  MODIFY `id_saldo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_asignacion`
--
ALTER TABLE `detalle_asignacion`
  ADD CONSTRAINT `detalle_asignacion_ibfk_1` FOREIGN KEY (`id_detalle`) REFERENCES `detalle_nomina` (`id_detalle`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_asignacion_ibfk_2` FOREIGN KEY (`id_asignacion`) REFERENCES `tipo_asignacion` (`id_asignacion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_deduccion`
--
ALTER TABLE `detalle_deduccion`
  ADD CONSTRAINT `detalle_deduccion_ibfk_1` FOREIGN KEY (`id_detalle`) REFERENCES `detalle_nomina` (`id_detalle`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_deduccion_ibfk_2` FOREIGN KEY (`id_tipo`) REFERENCES `tipo_deduccion` (`id_tipo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_nomina`
--
ALTER TABLE `detalle_nomina`
  ADD CONSTRAINT `detalle_nomina_ibfk_1` FOREIGN KEY (`id_nomina`) REFERENCES `nomina` (`id_nomina`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_nomina_ibfk_2` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_nomina`) REFERENCES `nomina` (`id_nomina`) ON DELETE CASCADE;

--
-- Filtros para la tabla `saldo_vacaciones`
--
ALTER TABLE `saldo_vacaciones`
  ADD CONSTRAINT `saldo_vacaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`cargo_id`);

--
-- Filtros para la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD CONSTRAINT `vacaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vacaciones_feriados`
--
ALTER TABLE `vacaciones_feriados`
  ADD CONSTRAINT `vacaciones_feriados_ibfk_1` FOREIGN KEY (`id_vacacion`) REFERENCES `vacaciones` (`id_vacacion`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacaciones_feriados_ibfk_2` FOREIGN KEY (`id_feriado`) REFERENCES `feriados` (`id_feriado`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vacaciones_saldo`
--
ALTER TABLE `vacaciones_saldo`
  ADD CONSTRAINT `vacaciones_saldo_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
