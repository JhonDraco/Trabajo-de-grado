-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-08-2026 a las 04:15:40
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

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
-- Estructura de tabla para la tabla `asignacion_empleado`
--

CREATE TABLE `asignacion_empleado` (
  `id_asig_emp` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `id_asignacion` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `creada_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `asignacion_empleado`
--

INSERT INTO `asignacion_empleado` (`id_asig_emp`, `empleado_id`, `id_asignacion`, `monto`, `activa`, `creada_en`) VALUES
(1, 2, 4, 0.04, 0, '2026-03-07 02:59:24'),
(3, 2, 1, 1000.00, 1, '2026-03-07 03:16:49'),
(4, 5, 1, 1000.00, 1, '2026-03-07 03:17:31'),
(7, 2, 5, 10000.00, 1, '2026-03-07 14:20:17'),
(8, 2, 1, 10000.00, 1, '2026-03-09 15:35:50'),
(9, 5, 5, 10000.00, 1, '2026-03-09 15:36:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `cargo_id` int(11) NOT NULL,
  `accion` varchar(50) NOT NULL,
  `modulo` varchar(80) NOT NULL,
  `descripcion` text NOT NULL,
  `ip` varchar(45) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `usuario`, `cargo_id`, `accion`, `modulo`, `descripcion`, `ip`, `fecha`) VALUES
(1, 'jhon', 1, 'CREAR', 'Asignaciones', 'Asignó asignación ID 7 al empleado ID 2', '::1', '2026-04-17 11:00:49'),
(2, 'jhon', 1, 'CREAR', 'Feriados', 'Carga automática 2026: 15 insertados, 0 ya existían', '::1', '2026-04-21 09:26:45'),
(3, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-08 11:51:17'),
(4, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-16 14:32:26'),
(5, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-19 10:18:03'),
(6, 'rrhh', 4, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-19 10:20:46'),
(7, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-25 11:32:47'),
(8, 'rrhh', 4, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-25 11:34:48'),
(9, 'finanzas', 5, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-25 11:36:37'),
(10, 'analista', 3, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-25 11:38:54'),
(11, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-25 11:40:09'),
(12, 'finanzas', 5, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-25 11:40:26'),
(13, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-27 18:20:29'),
(14, 'rrhh', 4, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-27 18:21:10'),
(15, 'finanzas', 5, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-27 18:22:04'),
(16, 'analista', 3, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-27 18:22:52'),
(17, 'analista', 3, 'CREAR', 'Horas Extra', 'Registró 2 h (diurna) para empleado ID 2 — Bs. 125', '::1', '2026-07-27 18:23:33'),
(18, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-07-27 18:23:49'),
(19, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 69 (2026-07-20 a 2026-08-02)', '::1', '2026-07-29 10:00:27'),
(20, 'jhon', 1, 'CREAR', 'Horas Extra', 'Registró 2 h (nocturna) para empleado ID 2 — Bs. 166.66', '::1', '2026-07-29 10:01:24'),
(21, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 70 (2026-07-27 a 2026-08-02)', '::1', '2026-07-29 10:01:40'),
(22, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-08-03 19:53:12'),
(23, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 71 (2026-08-03 a 2026-08-09)', '::1', '2026-08-03 19:59:01'),
(24, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 72 (2026-08-03 a 2026-08-09)', '::1', '2026-08-03 20:02:24'),
(25, 'jhon', 1, 'TOGGLE', 'Asignaciones', 'Toggle activo para asignación ID 3: 0', '::1', '2026-08-03 20:05:48'),
(26, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 73 (2026-08-03 a 2026-08-09)', '::1', '2026-08-03 20:06:05'),
(27, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 74 (2026-08-03 a 2026-08-09)', '::1', '2026-08-03 20:07:16'),
(28, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-08-04 11:37:19'),
(29, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 75 (2026-08-03 a 2026-08-09)', '::1', '2026-08-04 11:37:49'),
(30, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-08-04 11:41:27'),
(31, 'jhon', 1, 'TOGGLE', 'Asignaciones', 'Toggle activo para asignación ID 7: 0', '::1', '2026-08-04 11:48:34'),
(32, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 76 (2026-08-03 a 2026-08-09)', '::1', '2026-08-04 14:32:32'),
(33, 'jhon', 1, 'CREAR', 'Nómina', 'Generó nómina ID 1 (2026-08-03 a 2026-08-09)', '::1', '2026-08-04 22:23:29'),
(34, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-08-05 19:45:57'),
(35, 'finanzas', 5, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-08-05 19:49:38'),
(36, 'jhon', 1, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-08-05 19:52:37'),
(37, 'Emilio123', 2, 'LOGIN', 'Acceso', 'Ingresó al sistema desde IP: ::1', '::1', '2026-08-05 19:54:45');

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
(2, 'trabajador'),
(3, 'Analista de Nómina'),
(4, 'RRHH'),
(5, 'Finanzas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deduccion_empleado`
--

CREATE TABLE `deduccion_empleado` (
  `id_deduccion_emp` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL,
  `cuotas` int(11) DEFAULT 1,
  `cuota_actual` int(11) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `creada_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `nombre` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `deduccion_empleado`
--

INSERT INTO `deduccion_empleado` (`id_deduccion_emp`, `empleado_id`, `id_tipo`, `monto`, `cuotas`, `cuota_actual`, `activa`, `creada_en`, `nombre`) VALUES
(1, 2, NULL, 500.00, 1, 1, 0, '2026-01-24 14:01:16', 'prestamo'),
(2, 1, NULL, 100.00, 1, 1, 0, '2026-01-24 17:18:27', 'prestamo'),
(3, 2, NULL, 333.00, 1, 1, 0, '2026-03-07 18:05:33', 'prestamo carro'),
(6, 1, NULL, 2000.00, 1, 1, 0, '2026-03-09 21:53:44', 'prestamo');

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
(3, 1, 4, 2500.00),
(4, 1, 5, 0.00),
(5, 1, 6, 12500.00),
(6, 2, 1, 1000.00),
(7, 2, 1, 10000.00),
(8, 2, 2, 1000.00),
(9, 2, 4, 1000.00),
(10, 2, 5, 10000.00),
(11, 2, 6, 5000.00),
(12, 3, 1, 0.00),
(13, 3, 2, 100.00),
(14, 3, 4, 100.00),
(15, 3, 5, 0.00),
(16, 3, 6, 500.00),
(17, 4, 1, 1000.00),
(18, 4, 2, 140.00),
(19, 4, 4, 140.00),
(20, 4, 5, 10000.00),
(21, 4, 6, 700.00),
(22, 5, 1, 0.00),
(23, 5, 2, 777.70),
(24, 5, 4, 777.70),
(25, 5, 5, 0.00),
(26, 5, 6, 3888.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_deduccion`
--

CREATE TABLE `detalle_deduccion` (
  `id_detalle_ded` int(11) NOT NULL,
  `id_detalle` int(11) NOT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_deduccion`
--

INSERT INTO `detalle_deduccion` (`id_detalle_ded`, `id_detalle`, `id_tipo`, `monto`) VALUES
(1, 1, 1, 1000.00),
(2, 1, 2, 250.00),
(3, 1, 3, 125.00),
(4, 2, 1, 400.00),
(5, 2, 2, 100.00),
(6, 2, 3, 50.00),
(7, 3, 1, 40.00),
(8, 3, 2, 10.00),
(9, 3, 3, 5.00),
(10, 4, 1, 56.00),
(11, 4, 2, 14.00),
(12, 4, 3, 7.00),
(13, 5, 1, 311.08),
(14, 5, 2, 77.77),
(15, 5, 3, 38.89);

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
  `total_horas_extra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_deducciones` decimal(12,2) DEFAULT 0.00,
  `total_pagar` decimal(12,2) DEFAULT 0.00,
  `feriados_trabajados` tinyint(1) NOT NULL DEFAULT 0,
  `monto_recargo` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_nomina`
--

INSERT INTO `detalle_nomina` (`id_detalle`, `id_nomina`, `empleado_id`, `salario_base`, `total_asignaciones`, `total_horas_extra`, `total_deducciones`, `total_pagar`, `feriados_trabajados`, `monto_recargo`) VALUES
(1, 1, 1, 25000.00, 17500.00, 0.00, 1375.00, 41125.00, 0, 0.00),
(2, 1, 2, 10000.00, 28000.00, 0.00, 550.00, 37450.00, 0, 0.00),
(3, 1, 4, 1000.00, 700.00, 0.00, 55.00, 1645.00, 0, 0.00),
(4, 1, 5, 1400.00, 11980.00, 0.00, 77.00, 13303.00, 0, 0.00),
(5, 1, 7, 7777.00, 5443.90, 0.00, 427.74, 12793.16, 0, 0.00);

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
(2, '99887766', 'Carlos', 'Pérez', 'La Guaira', '04145556677', 'carlos@example.com', '2024-01-15', 10000.00, 'activo'),
(4, '32657336', 'Jhon pequeño', 'Castillo correa', 'tucaca', '04123456781', 'casticj671119@gmail.com', '2025-11-13', 1000.00, 'activo'),
(5, '33657336', 'Emilio', 'Emiliano', 'La dolorita', '04142347728', 'emiliobatanero@gmail.com', '2025-01-29', 1400.00, 'activo'),
(7, '33218995', 'Isaac', 'Toro', 'Av. Principal #1', '0424-1314426', 'Isaac@email.com', '2026-01-15', 7777.00, 'activo');

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
(4, 'Feriado Interno Empresa', '2025-08-15', 'interno', 0, 'Aniversario empresa', '2025-12-29 14:41:48'),
(5, 'Año Nuevo', '2026-01-01', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(6, 'Carnaval (Lunes)', '2026-02-16', 'nacional', 1, 'Feriado móvil', '2026-04-21 13:26:45'),
(7, 'Carnaval (Martes)', '2026-02-17', 'nacional', 1, 'Feriado móvil', '2026-04-21 13:26:45'),
(8, 'Miércoles Santo', '2026-04-01', 'nacional', 1, 'Semana Santa — feriado móvil', '2026-04-21 13:26:45'),
(9, 'Jueves Santo', '2026-04-02', 'nacional', 1, 'Semana Santa — feriado móvil', '2026-04-21 13:26:45'),
(10, 'Viernes Santo', '2026-04-03', 'nacional', 1, 'Semana Santa — feriado móvil', '2026-04-21 13:26:45'),
(11, 'Declaración Independencia', '2026-04-19', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(12, 'Día del Trabajador', '2026-05-01', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(13, 'Batalla de Carabobo', '2026-06-24', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(14, 'Día de la Independencia', '2026-07-05', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(15, 'Natalicio Simón Bolívar', '2026-07-24', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(16, 'Día de la Resistencia', '2026-10-12', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(17, 'Nochebuena', '2026-12-24', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(18, 'Navidad', '2026-12-25', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45'),
(19, 'Fin de Año', '2026-12-31', 'nacional', 1, 'Art. 184 LOTTT', '2026-04-21 13:26:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horas_extras`
--

CREATE TABLE `horas_extras` (
  `id_hora_extra` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horas` decimal(4,2) NOT NULL,
  `tipo` enum('diurna','nocturna') NOT NULL DEFAULT 'diurna',
  `valor_hora` decimal(10,2) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `registrado_por` varchar(100) DEFAULT NULL,
  `creada_en` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liquidaciones`
--

CREATE TABLE `liquidaciones` (
  `id_liquidacion` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `salario_base` decimal(12,2) NOT NULL,
  `anos_servicio` int(11) NOT NULL DEFAULT 0,
  `meses_adicionales` int(11) NOT NULL DEFAULT 0,
  `prestaciones_sociales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `utilidades_prop` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bono_vacacional_prop` decimal(12,2) NOT NULL DEFAULT 0.00,
  `indemnizacion` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_liquidacion` decimal(12,2) NOT NULL DEFAULT 0.00,
  `motivo` varchar(100) NOT NULL DEFAULT 'Retiro voluntario',
  `despido_injustificado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_liquidacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(1, '2026-08-03', '2026-08-09', 'mensual', 'abierta', 'jhon', '2026-08-05 02:23:29');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_asignacion`
--

CREATE TABLE `tipo_asignacion` (
  `id_asignacion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('fijo','porcentaje') NOT NULL DEFAULT 'fijo',
  `porcentaje` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descripcion` text DEFAULT NULL,
  `aplica_a` varchar(20) DEFAULT 'todos',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_asignacion`
--

INSERT INTO `tipo_asignacion` (`id_asignacion`, `nombre`, `tipo`, `porcentaje`, `descripcion`, `aplica_a`, `activo`) VALUES
(1, 'Bono Alimentación', 'fijo', 0.00, 'Bono en monto fijo por política', 'todos', 1),
(2, 'Bono por Responsabilidad', 'porcentaje', 10.00, '10% sobre salario base', 'todos', 1),
(4, 'Bono De Transporte', 'porcentaje', 10.00, 'Bono De Transporte', 'todos', 1),
(5, 'Asignación personalizada', 'fijo', 0.00, 'Asignación creada directamente para empleado', 'todos', 1),
(6, 'Bono De Guerra Economica', 'porcentaje', 50.00, '', 'todos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_deduccion`
--

CREATE TABLE `tipo_deduccion` (
  `id_tipo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('legal','interna') DEFAULT 'legal',
  `porcentaje` decimal(6,2) NOT NULL DEFAULT 0.00,
  `forma` enum('porcentaje','fijo') DEFAULT 'porcentaje',
  `monto_fijo` decimal(10,2) DEFAULT 0.00,
  `obligatorio` tinyint(1) DEFAULT 1,
  `activo` tinyint(1) DEFAULT 1,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_deduccion`
--

INSERT INTO `tipo_deduccion` (`id_tipo`, `nombre`, `tipo`, `porcentaje`, `forma`, `monto_fijo`, `obligatorio`, `activo`, `descripcion`) VALUES
(1, 'IVSS', 'legal', 4.00, 'porcentaje', 0.00, 1, 1, 'Seguro social - 4%'),
(2, 'FAOV', 'legal', 1.00, 'porcentaje', 0.00, 1, 1, 'Fondo de Ahorro para la Vivienda - 1%'),
(3, 'Paro Forzoso', 'legal', 0.50, 'porcentaje', 0.00, 1, 1, 'Paro Forzoso - 0.5%'),
(4, 'poligel', 'legal', 10.00, 'porcentaje', 0.00, 1, 0, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `clave` varchar(255) DEFAULT NULL,
  `cargo_id` int(11) DEFAULT NULL,
  `nombre_apellido` varchar(50) NOT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `usuario`, `clave`, `cargo_id`, `nombre_apellido`, `empleado_id`, `activo`) VALUES
(1, 'jhon', '$2y$10$ebL1CzmALsWvFdsXLtPPue9x8Gb1Qf1GuP.1VhBYPherKeAeNu4jq', 1, 'Jhoneyker Correa', NULL, 1),
(2, 'jhonadmin', '123', 2, 'Jhoneyker Correa', 1, 1),
(7, 'Carlos123', '$2y$10$neoy0ayoblwgvngdrRPMBO.EG77LkjoLONqCQYrnSLlOUB1NfboR6', 2, 'cristian castillo', 2, 1),
(8, 'ana', '123', 1, 'Ana Hernandez', NULL, 1),
(9, 'admin', '123456', 1, 'Administrador Sistema', NULL, 1),
(10, 'Emilio123', '$2y$10$IGztPE3c8FPI5wM.SRSI3uPVW8ItiPCYkIeIAZ63UxnKHuoAwcQFK', 2, 'Emilio Emiliano', 5, 1),
(11, 'analista', '$2y$10$djDmqq1GvwNxwyJ/znS8d.WcmpBM52UywoeTzM8JsXYrnnREJx9RC', 3, 'Analista Nomina', NULL, 1),
(12, 'rrhh', '$2y$10$pvjeeLuZKLeSv4VybBp4ReG7g/EQbNXwHkP1gaMECmntxttIaxRP6', 4, 'Usuario RRHH', NULL, 1),
(13, 'finanzas', '$2y$10$hcbJYcvGLL6uHiMnmzErCOR244fgygYn/GgEo8wMxLR9zNlo77YgW', 5, 'Usuario Finanzas', NULL, 1),
(14, 'Isaac123', '1234', 2, 'Isaac Toro', 7, 1);

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
  `observaciones` text DEFAULT NULL,
  `creada_por` varchar(100) DEFAULT NULL,
  `creada_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `aprobado_por` varchar(100) DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vacaciones`
--

INSERT INTO `vacaciones` (`id_vacacion`, `empleado_id`, `fecha_inicio`, `fecha_fin`, `dias_solicitados`, `dias_habiles`, `observaciones`, `creada_por`, `creada_en`, `estado`, `aprobado_por`, `fecha_aprobacion`, `motivo_rechazo`) VALUES
(7, 1, '2026-03-03', '2026-03-05', 3, 3, '', 'jhon', '2026-03-02 15:47:34', 'rechazado', NULL, NULL, NULL),
(8, 1, '2026-03-10', '2026-03-28', 14, 14, '', 'jhon', '2026-03-02 19:34:55', 'aprobado', NULL, NULL, NULL),
(9, 1, '2026-04-07', '2026-04-11', 4, 4, '', 'jhon', '2026-03-02 19:35:49', 'rechazado', 'jhon', '2026-04-17 22:10:07', 'no tienes dias habiles'),
(10, 1, '2026-03-05', '2026-03-05', 1, 1, '', 'jhon', '2026-03-05 03:48:37', 'aprobado', 'jhon', '2026-03-04 23:48:40', NULL),
(11, 2, '2026-03-05', '2026-03-07', 2, 2, '', 'jhon', '2026-03-05 03:49:11', 'aprobado', 'jhon', '2026-03-04 23:49:19', NULL),
(12, 5, '2026-03-05', '2026-03-07', 2, 2, '', 'jhon', '2026-03-05 03:55:31', 'aprobado', 'jhon', '2026-03-04 23:55:34', NULL),
(13, 5, '2026-03-17', '2026-03-18', 2, 2, '', 'jhon', '2026-03-05 04:13:06', 'rechazado', NULL, NULL, NULL),
(14, 5, '2026-03-17', '2026-03-18', 2, 2, '', 'jhon', '2026-03-05 04:14:02', 'aprobado', 'jhon', '2026-03-05 00:14:06', NULL),
(15, 5, '2026-03-24', '2026-03-25', 2, 2, '', 'jhon', '2026-03-05 04:14:57', 'rechazado', 'jhon', '2026-03-05 00:15:01', 'asdas'),
(16, 5, '2026-04-07', '2026-04-08', 2, 2, '', 'jhon', '2026-03-05 04:16:15', 'aprobado', 'jhon', '2026-03-05 00:16:18', NULL),
(17, 2, '2026-04-08', '2026-04-10', 3, 3, '', 'jhon', '2026-03-31 02:26:42', 'aprobado', 'jhon', '2026-03-30 22:26:46', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion_empleado`
--
ALTER TABLE `asignacion_empleado`
  ADD PRIMARY KEY (`id_asig_emp`),
  ADD KEY `empleado_id` (`empleado_id`),
  ADD KEY `fk_asig_tipo` (`id_asignacion`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`cargo_id`);

--
-- Indices de la tabla `deduccion_empleado`
--
ALTER TABLE `deduccion_empleado`
  ADD PRIMARY KEY (`id_deduccion_emp`),
  ADD KEY `empleado_id` (`empleado_id`),
  ADD KEY `fk_ded_tipo` (`id_tipo`);

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
-- Indices de la tabla `horas_extras`
--
ALTER TABLE `horas_extras`
  ADD PRIMARY KEY (`id_hora_extra`),
  ADD KEY `empleado_id` (`empleado_id`);

--
-- Indices de la tabla `liquidaciones`
--
ALTER TABLE `liquidaciones`
  ADD PRIMARY KEY (`id_liquidacion`),
  ADD KEY `empleado_id` (`empleado_id`);

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion_empleado`
--
ALTER TABLE `asignacion_empleado`
  MODIFY `id_asig_emp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `cargo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `deduccion_empleado`
--
ALTER TABLE `deduccion_empleado`
  MODIFY `id_deduccion_emp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_asignacion`
--
ALTER TABLE `detalle_asignacion`
  MODIFY `id_detalle_asig` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `detalle_deduccion`
--
ALTER TABLE `detalle_deduccion`
  MODIFY `id_detalle_ded` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `detalle_nomina`
--
ALTER TABLE `detalle_nomina`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id_feriado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `horas_extras`
--
ALTER TABLE `horas_extras`
  MODIFY `id_hora_extra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `liquidaciones`
--
ALTER TABLE `liquidaciones`
  MODIFY `id_liquidacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nomina`
--
ALTER TABLE `nomina`
  MODIFY `id_nomina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_asignacion`
--
ALTER TABLE `tipo_asignacion`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tipo_deduccion`
--
ALTER TABLE `tipo_deduccion`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  MODIFY `id_vacacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_empleado`
--
ALTER TABLE `asignacion_empleado`
  ADD CONSTRAINT `asignacion_empleado_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`),
  ADD CONSTRAINT `fk_asig_tipo` FOREIGN KEY (`id_asignacion`) REFERENCES `tipo_asignacion` (`id_asignacion`);

--
-- Filtros para la tabla `deduccion_empleado`
--
ALTER TABLE `deduccion_empleado`
  ADD CONSTRAINT `deduccion_empleado_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ded_tipo` FOREIGN KEY (`id_tipo`) REFERENCES `tipo_deduccion` (`id_tipo`);

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
-- Filtros para la tabla `horas_extras`
--
ALTER TABLE `horas_extras`
  ADD CONSTRAINT `horas_extras_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);

--
-- Filtros para la tabla `liquidaciones`
--
ALTER TABLE `liquidaciones`
  ADD CONSTRAINT `liquidaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_nomina`) REFERENCES `nomina` (`id_nomina`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`cargo_id`);

--
-- Filtros para la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD CONSTRAINT `vacaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
