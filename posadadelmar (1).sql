-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-05-2025 a las 14:24:58
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
-- Base de datos: `posadadelmar`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoriasplatillos`
--

CREATE TABLE `categoriasplatillos` (
  `CategoriaPlatilloID` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoriasplatillos`
--

INSERT INTO `categoriasplatillos` (`CategoriaPlatilloID`, `Nombre`, `Descripcion`, `Activo`) VALUES
(1, 'Pezcados', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoriasproductos`
--

CREATE TABLE `categoriasproductos` (
  `CategoriaID` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoriasproductos`
--

INSERT INTO `categoriasproductos` (`CategoriaID`, `Nombre`, `Descripcion`, `Activo`) VALUES
(1, 'Pescado', 'pez', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `ClienteID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `TipoDocumento` enum('CED','PAS','RUC') DEFAULT 'CED',
  `NumeroDocumento` varchar(20) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Direccion` text DEFAULT NULL,
  `FechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`ClienteID`, `Nombre`, `Apellido`, `TipoDocumento`, `NumeroDocumento`, `Telefono`, `Email`, `Direccion`, `FechaRegistro`) VALUES
(1, 'Merluz', 'losa', 'PAS', '1425445214', '04161144857', 'fronten.jesus.borges@gmail.com', 'x', '2025-05-07 21:24:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compradetalles`
--

CREATE TABLE `compradetalles` (
  `DetalleID` int(11) NOT NULL,
  `CompraID` int(11) DEFAULT NULL,
  `ProductoID` int(11) DEFAULT NULL,
  `Cantidad` int(11) NOT NULL,
  `PrecioUnitario` decimal(10,2) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `CompraID` int(11) NOT NULL,
  `ProveedorID` int(11) DEFAULT NULL,
  `Fecha` date NOT NULL,
  `NumeroFactura` varchar(50) DEFAULT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  `Impuesto` decimal(10,2) DEFAULT 0.00,
  `Total` decimal(10,2) NOT NULL,
  `Estado` enum('Pendiente','Pagada','Cancelada') DEFAULT 'Pendiente',
  `Notas` text DEFAULT NULL,
  `FechaCreacion` datetime DEFAULT current_timestamp(),
  `UsuarioID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consumoingredientes`
--

CREATE TABLE `consumoingredientes` (
  `ConsumoID` int(11) NOT NULL,
  `OrdenDetalleID` int(11) NOT NULL,
  `IngredienteID` int(11) NOT NULL,
  `CantidadConsumida` decimal(10,3) NOT NULL,
  `FechaHora` datetime DEFAULT current_timestamp(),
  `UsuarioID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consumoingredientes`
--

INSERT INTO `consumoingredientes` (`ConsumoID`, `OrdenDetalleID`, `IngredienteID`, `CantidadConsumida`, `FechaHora`, `UsuarioID`) VALUES
(6, 18, 4, 0.400, '2025-05-12 18:56:11', 1),
(7, 18, 12, 2.000, '2025-05-12 18:56:11', 1),
(8, 18, 1, 1.000, '2025-05-12 18:56:11', 1),
(9, 18, 5, 1.000, '2025-05-12 18:56:11', 1),
(10, 18, 6, 0.002, '2025-05-12 18:56:11', 1),
(16, 20, 4, 0.400, '2025-05-12 18:58:53', 1),
(17, 20, 12, 2.000, '2025-05-12 18:58:53', 1),
(18, 20, 1, 1.000, '2025-05-12 18:58:53', 1),
(19, 20, 5, 1.000, '2025-05-12 18:58:53', 1),
(20, 20, 6, 0.002, '2025-05-12 18:58:53', 1),
(51, 27, 4, 0.600, '2025-05-12 19:44:53', 1),
(52, 27, 12, 3.000, '2025-05-12 19:44:53', 1),
(53, 27, 1, 1.500, '2025-05-12 19:44:53', 1),
(54, 27, 5, 1.500, '2025-05-12 19:44:53', 1),
(55, 27, 6, 0.003, '2025-05-12 19:44:53', 1),
(56, 28, 4, 0.600, '2025-05-12 19:50:53', 1),
(57, 28, 12, 3.000, '2025-05-12 19:50:53', 1),
(58, 28, 1, 1.500, '2025-05-12 19:50:53', 1),
(59, 28, 5, 1.500, '2025-05-12 19:50:53', 1),
(60, 28, 6, 0.003, '2025-05-12 19:50:53', 1),
(61, 29, 4, 0.600, '2025-05-12 19:56:51', 1),
(62, 29, 12, 3.000, '2025-05-12 19:56:51', 1),
(63, 29, 1, 1.500, '2025-05-12 19:56:51', 1),
(64, 29, 5, 1.500, '2025-05-12 19:56:51', 1),
(65, 29, 6, 0.003, '2025-05-12 19:56:51', 1),
(66, 30, 4, 0.800, '2025-05-12 19:59:22', 1),
(67, 30, 12, 4.000, '2025-05-12 19:59:22', 1),
(68, 30, 1, 2.000, '2025-05-12 19:59:22', 1),
(69, 30, 5, 2.000, '2025-05-12 19:59:22', 1),
(70, 30, 6, 0.004, '2025-05-12 19:59:22', 1),
(71, 31, 4, 0.600, '2025-05-12 20:17:37', 1),
(72, 31, 12, 3.000, '2025-05-12 20:17:37', 1),
(73, 31, 1, 1.500, '2025-05-12 20:17:37', 1),
(74, 31, 5, 1.500, '2025-05-12 20:17:37', 1),
(75, 31, 6, 0.003, '2025-05-12 20:17:37', 1),
(76, 32, 4, 0.600, '2025-05-12 20:52:10', 1),
(77, 32, 12, 3.000, '2025-05-12 20:52:10', 1),
(78, 32, 1, 1.500, '2025-05-12 20:52:10', 1),
(79, 32, 5, 1.500, '2025-05-12 20:52:10', 1),
(80, 32, 6, 0.003, '2025-05-12 20:52:10', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `EmpleadoID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `TipoDocumento` enum('CED','PAS') DEFAULT 'CED',
  `NumeroDocumento` varchar(20) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Direccion` text DEFAULT NULL,
  `Cargo` varchar(50) DEFAULT NULL,
  `Foto` varchar(255) DEFAULT NULL,
  `FechaContratacion` date DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`EmpleadoID`, `Nombre`, `Apellido`, `TipoDocumento`, `NumeroDocumento`, `Telefono`, `Email`, `Direccion`, `Cargo`, `Foto`, `FechaContratacion`, `Activo`) VALUES
(1, 'Jesus Alfredo', 'Borges Alvarez', 'CED', '27924797', '0416114485', 'jesus@gmail.com', 'amuay calle 2', 'jefe', NULL, '2025-05-01', 1),
(2, 'Jose', 'Alvarez', 'CED', '26565166', '04161144857', 'fronten.jesus.borges@gmail.com', 'aaaa aaa aaa a', 'Habitaciones', NULL, '2025-05-01', 1),
(3, 'Jose', 'Alvarez', 'CED', '2656516', '04161144857', 'fronten.jesus.borges@gmail.com', 'aaaa aaa aaa a', 'Habitaciones', NULL, '2025-05-01', 1),
(4, 'Jesus Alfredo', 'Borges Alvarez', 'CED', '2656516744', '041611448572', 'fronten.jesus.borges@gmail.co', 'aaaaaaaaaaa', 'hab', NULL, '2025-05-01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `HabitacionID` int(11) NOT NULL,
  `Numero` varchar(10) NOT NULL,
  `TipoHabitacionID` int(11) DEFAULT NULL,
  `Estado` enum('Disponible','Ocupada','Mantenimiento','Reservada') DEFAULT 'Disponible',
  `Notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`HabitacionID`, `Numero`, `TipoHabitacionID`, `Estado`, `Notas`) VALUES
(1, '1', 1, 'Disponible', ''),
(2, '2', 1, 'Disponible', ''),
(3, '3', 1, 'Disponible', ''),
(4, '4', 2, 'Disponible', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingredientes`
--

CREATE TABLE `ingredientes` (
  `IngredienteID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `UnidadMedida` varchar(20) NOT NULL,
  `Stock` decimal(10,3) DEFAULT 0.000,
  `StockMinimo` decimal(10,3) DEFAULT 1.000,
  `Activo` tinyint(1) DEFAULT 1,
  `FechaCreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ingredientes`
--

INSERT INTO `ingredientes` (`IngredienteID`, `Nombre`, `Descripcion`, `UnidadMedida`, `Stock`, `StockMinimo`, `Activo`, `FechaCreacion`) VALUES
(1, 'Cebollas', '', 'kg', -12.500, 1.000, 1, '2025-05-12 07:55:01'),
(2, 'Tomate', '', 'kg', 6.000, 1.000, 1, '2025-05-12 07:56:14'),
(3, 'Tajali', '', 'kg', 20.000, 1.000, 1, '2025-05-12 07:56:28'),
(4, 'limón', '', 'kg', 3.400, 1.000, 1, '2025-05-12 07:56:41'),
(5, 'Harina pan', '', 'kg', 33.500, 1.000, 1, '2025-05-12 07:56:59'),
(6, 'aceite', '', 'l', 47.967, 1.000, 1, '2025-05-12 07:57:19'),
(7, 'harina de trigo todo uso', '', 'kg', 10.000, 1.000, 1, '2025-05-12 07:58:11'),
(8, 'huevos', '', 'unidad', 80.000, 1.000, 1, '2025-05-12 07:58:28'),
(9, 'sal', '', 'kg', 10.000, 1.000, 1, '2025-05-12 07:59:03'),
(10, 'cilantro', '', 'kg', 5.000, 1.000, 1, '2025-05-12 07:59:29'),
(11, 'pimentón', '', 'kg', 10.000, 1.000, 1, '2025-05-12 07:59:56'),
(12, 'Pargo Rojo', '', 'kg', -23.000, 1.000, 1, '2025-05-12 08:23:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingredientesmovimientos`
--

CREATE TABLE `ingredientesmovimientos` (
  `idmovimientoi` int(11) NOT NULL,
  `ingredientesID` int(11) NOT NULL,
  `Tipo` varchar(200) NOT NULL,
  `Cantidad` text NOT NULL,
  `Referencia` varchar(200) NOT NULL,
  `Notas` text NOT NULL,
  `UsuarioID` int(11) NOT NULL,
  `FechaHora` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ingredientesmovimientos`
--

INSERT INTO `ingredientesmovimientos` (`idmovimientoi`, `ingredientesID`, `Tipo`, `Cantidad`, `Referencia`, `Notas`, `UsuarioID`, `FechaHora`) VALUES
(7, 6, 'Ajuste', '0', 'Reactivación de ingrediente', '', 1, '2025-05-12 08:33:50'),
(8, 6, 'Salida', '1', 'Ajuste manual', 'Ajuste de stock por administrador', 1, '2025-05-12 08:34:27'),
(9, 4, 'Salida', '0.4', 'Orden #25', 'Preparación de platillo', 1, '2025-05-12 18:56:11'),
(10, 12, 'Salida', '2', 'Orden #25', 'Preparación de platillo', 1, '2025-05-12 18:56:11'),
(11, 1, 'Salida', '1', 'Orden #25', 'Preparación de platillo', 1, '2025-05-12 18:56:11'),
(12, 5, 'Salida', '1', 'Orden #25', 'Preparación de platillo', 1, '2025-05-12 18:56:11'),
(13, 6, 'Salida', '0.002', 'Orden #25', 'Preparación de platillo', 1, '2025-05-12 18:56:11'),
(14, 4, 'Salida', '0.4', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 18:57:08'),
(15, 12, 'Salida', '2', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 18:57:08'),
(16, 1, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 18:57:08'),
(17, 5, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 18:57:08'),
(18, 6, 'Salida', '0.002', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 18:57:08'),
(19, 4, 'Salida', '0.4', 'Orden #27', 'Preparación de platillo', 1, '2025-05-12 18:58:53'),
(20, 12, 'Salida', '2', 'Orden #27', 'Preparación de platillo', 1, '2025-05-12 18:58:53'),
(21, 1, 'Salida', '1', 'Orden #27', 'Preparación de platillo', 1, '2025-05-12 18:58:53'),
(22, 5, 'Salida', '1', 'Orden #27', 'Preparación de platillo', 1, '2025-05-12 18:58:53'),
(23, 6, 'Salida', '0.002', 'Orden #27', 'Preparación de platillo', 1, '2025-05-12 18:58:53'),
(24, 2, 'Ajuste', '0', 'Reactivación de ingrediente', '', 1, '2025-05-12 19:08:31'),
(25, 6, 'Salida', '1', 'fghgfhfghfgh', 'ggggg', 1, '2025-05-12 19:09:10'),
(26, 6, 'Salida', '1', 'fghgfhfghfgh', 'ggggg', 1, '2025-05-12 19:09:31'),
(27, 4, 'Salida', '0.4', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:35:25'),
(28, 12, 'Salida', '2', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:35:25'),
(29, 1, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:35:25'),
(30, 5, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:35:25'),
(31, 6, 'Salida', '0.002', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:35:25'),
(32, 4, 'Salida', '0.4', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:36:06'),
(33, 12, 'Salida', '2', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:36:06'),
(34, 1, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:36:06'),
(35, 5, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:36:06'),
(36, 6, 'Salida', '0.002', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:36:06'),
(37, 4, 'Salida', '0.4', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:37:01'),
(38, 12, 'Salida', '2', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:37:01'),
(39, 1, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:37:01'),
(40, 5, 'Salida', '1', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:37:01'),
(41, 6, 'Salida', '0.002', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:37:01'),
(42, 4, 'Salida', '0.6', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:39:47'),
(43, 12, 'Salida', '3', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:39:47'),
(44, 1, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:39:47'),
(45, 5, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:39:47'),
(46, 6, 'Salida', '0.003', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:39:47'),
(47, 4, 'Salida', '0.6', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:40:33'),
(48, 12, 'Salida', '3', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:40:33'),
(49, 1, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:40:33'),
(50, 5, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:40:33'),
(51, 6, 'Salida', '0.003', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:40:33'),
(52, 4, 'Salida', '0.6', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:42:12'),
(53, 12, 'Salida', '3', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:42:12'),
(54, 1, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:42:12'),
(55, 5, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:42:12'),
(56, 6, 'Salida', '0.003', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:42:12'),
(57, 4, 'Salida', '0.6', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:44:53'),
(58, 12, 'Salida', '3', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:44:53'),
(59, 1, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:44:53'),
(60, 5, 'Salida', '1.5', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:44:53'),
(61, 6, 'Salida', '0.003', 'Orden #26', 'Preparación de platillo', 1, '2025-05-12 19:44:53'),
(62, 4, 'Salida', '0.6', 'Orden #28', 'Preparación de platillo', 1, '2025-05-12 19:50:53'),
(63, 12, 'Salida', '3', 'Orden #28', 'Preparación de platillo', 1, '2025-05-12 19:50:53'),
(64, 1, 'Salida', '1.5', 'Orden #28', 'Preparación de platillo', 1, '2025-05-12 19:50:53'),
(65, 5, 'Salida', '1.5', 'Orden #28', 'Preparación de platillo', 1, '2025-05-12 19:50:53'),
(66, 6, 'Salida', '0.003', 'Orden #28', 'Preparación de platillo', 1, '2025-05-12 19:50:53'),
(67, 4, 'Salida', '0.6', 'Orden #29', 'Preparación de platillo', 1, '2025-05-12 19:56:51'),
(68, 12, 'Salida', '3', 'Orden #29', 'Preparación de platillo', 1, '2025-05-12 19:56:51'),
(69, 1, 'Salida', '1.5', 'Orden #29', 'Preparación de platillo', 1, '2025-05-12 19:56:51'),
(70, 5, 'Salida', '1.5', 'Orden #29', 'Preparación de platillo', 1, '2025-05-12 19:56:51'),
(71, 6, 'Salida', '0.003', 'Orden #29', 'Preparación de platillo', 1, '2025-05-12 19:56:51'),
(72, 4, 'Entrada', '0.600', 'Orden #29', 'Cancelación de orden', 1, '2025-05-12 19:57:04'),
(73, 12, 'Entrada', '3.000', 'Orden #29', 'Cancelación de orden', 1, '2025-05-12 19:57:04'),
(74, 1, 'Entrada', '1.500', 'Orden #29', 'Cancelación de orden', 1, '2025-05-12 19:57:04'),
(75, 5, 'Entrada', '1.500', 'Orden #29', 'Cancelación de orden', 1, '2025-05-12 19:57:04'),
(76, 6, 'Entrada', '0.003', 'Orden #29', 'Cancelación de orden', 1, '2025-05-12 19:57:04'),
(77, 4, 'Salida', '0.8', 'Orden #30', 'Preparación de platillo', 1, '2025-05-12 19:59:22'),
(78, 12, 'Salida', '4', 'Orden #30', 'Preparación de platillo', 1, '2025-05-12 19:59:22'),
(79, 1, 'Salida', '2', 'Orden #30', 'Preparación de platillo', 1, '2025-05-12 19:59:22'),
(80, 5, 'Salida', '2', 'Orden #30', 'Preparación de platillo', 1, '2025-05-12 19:59:22'),
(81, 6, 'Salida', '0.004', 'Orden #30', 'Preparación de platillo', 1, '2025-05-12 19:59:22'),
(82, 4, 'Entrada', '0.800', 'Orden #30', 'Cancelación de orden', 1, '2025-05-12 19:59:30'),
(83, 12, 'Entrada', '4.000', 'Orden #30', 'Cancelación de orden', 1, '2025-05-12 19:59:30'),
(84, 1, 'Entrada', '2.000', 'Orden #30', 'Cancelación de orden', 1, '2025-05-12 19:59:30'),
(85, 5, 'Entrada', '2.000', 'Orden #30', 'Cancelación de orden', 1, '2025-05-12 19:59:30'),
(86, 6, 'Entrada', '0.004', 'Orden #30', 'Cancelación de orden', 1, '2025-05-12 19:59:30'),
(87, 4, 'Salida', '0.6', 'Orden #31', 'Preparación de platillo', 1, '2025-05-12 20:17:37'),
(88, 12, 'Salida', '3', 'Orden #31', 'Preparación de platillo', 1, '2025-05-12 20:17:37'),
(89, 1, 'Salida', '1.5', 'Orden #31', 'Preparación de platillo', 1, '2025-05-12 20:17:37'),
(90, 5, 'Salida', '1.5', 'Orden #31', 'Preparación de platillo', 1, '2025-05-12 20:17:37'),
(91, 6, 'Salida', '0.003', 'Orden #31', 'Preparación de platillo', 1, '2025-05-12 20:17:37'),
(92, 4, 'Salida', '0.6', 'Orden #32', 'Preparación de platillo', 1, '2025-05-12 20:52:10'),
(93, 12, 'Salida', '3', 'Orden #32', 'Preparación de platillo', 1, '2025-05-12 20:52:10'),
(94, 1, 'Salida', '1.5', 'Orden #32', 'Preparación de platillo', 1, '2025-05-12 20:52:10'),
(95, 5, 'Salida', '1.5', 'Orden #32', 'Preparación de platillo', 1, '2025-05-12 20:52:10'),
(96, 6, 'Salida', '0.003', 'Orden #32', 'Preparación de platillo', 1, '2025-05-12 20:52:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventariomovimientos`
--

CREATE TABLE `inventariomovimientos` (
  `MovimientoID` int(11) NOT NULL,
  `ProductoID` int(11) DEFAULT NULL,
  `Tipo` enum('Entrada','Salida','Ajuste') NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `PrecioUnitario` decimal(10,2) DEFAULT NULL,
  `FechaHora` datetime DEFAULT current_timestamp(),
  `UsuarioID` int(11) DEFAULT NULL,
  `Referencia` varchar(100) DEFAULT NULL,
  `Notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesasrestaurante`
--

CREATE TABLE `mesasrestaurante` (
  `MesaID` int(11) NOT NULL,
  `Numero` varchar(10) NOT NULL,
  `Capacidad` int(11) DEFAULT 4,
  `Estado` enum('Disponible','Ocupada','Reservada','Mantenimiento') DEFAULT 'Disponible',
  `Ubicacion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesasrestaurante`
--

INSERT INTO `mesasrestaurante` (`MesaID`, `Numero`, `Capacidad`, `Estado`, `Ubicacion`) VALUES
(1, '1', 4, 'Disponible', 'ggggg'),
(2, '2', 4, 'Disponible', 'xxxxxxxxxxxxxxx'),
(4, '3', 4, 'Disponible', 'xxx'),
(6, '4', 4, 'Disponible', 'xxx');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordendetalles`
--

CREATE TABLE `ordendetalles` (
  `DetalleID` int(11) NOT NULL,
  `OrdenID` int(11) NOT NULL,
  `PlatilloID` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL DEFAULT 1,
  `PrecioUnitario` decimal(10,2) NOT NULL,
  `Notas` text DEFAULT NULL,
  `Estado` enum('Pendiente','EnPreparacion','Listo','Entregado','Cancelado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordendetalles`
--

INSERT INTO `ordendetalles` (`DetalleID`, `OrdenID`, `PlatilloID`, `Cantidad`, `PrecioUnitario`, `Notas`, `Estado`) VALUES
(18, 25, 1, 2, 10.00, NULL, 'Entregado'),
(20, 27, 1, 2, 10.00, NULL, 'Entregado'),
(27, 26, 1, 3, 10.00, NULL, 'Entregado'),
(28, 28, 1, 3, 10.00, NULL, 'Cancelado'),
(29, 29, 1, 3, 10.00, NULL, 'Cancelado'),
(30, 30, 1, 4, 10.00, NULL, 'Cancelado'),
(31, 31, 1, 3, 10.00, NULL, 'Entregado'),
(32, 32, 1, 3, 10.00, NULL, 'Entregado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenesrestaurante`
--

CREATE TABLE `ordenesrestaurante` (
  `OrdenID` int(11) NOT NULL,
  `ClienteID` int(11) DEFAULT NULL,
  `HabitacionID` int(11) DEFAULT NULL,
  `MesaID` int(11) DEFAULT NULL,
  `UsuarioID` int(11) NOT NULL,
  `FechaHora` datetime DEFAULT current_timestamp(),
  `Estado` enum('Pendiente','EnPreparacion','Listo','Entregado','Cancelado') DEFAULT 'Pendiente',
  `Tipo` enum('Restaurante','Habitacion','ParaLlevar') DEFAULT 'Restaurante',
  `Subtotal` decimal(10,2) NOT NULL,
  `Descuento` decimal(10,2) DEFAULT 0.00,
  `Impuesto` decimal(10,2) DEFAULT 0.00,
  `Total` decimal(10,2) NOT NULL,
  `Notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenesrestaurante`
--

INSERT INTO `ordenesrestaurante` (`OrdenID`, `ClienteID`, `HabitacionID`, `MesaID`, `UsuarioID`, `FechaHora`, `Estado`, `Tipo`, `Subtotal`, `Descuento`, `Impuesto`, `Total`, `Notas`) VALUES
(25, 1, NULL, 1, 1, '2025-05-12 18:56:11', 'Entregado', 'Restaurante', 20.00, 0.00, 2.40, 22.40, 'xxxxxxxxxxxxx'),
(26, 1, NULL, 2, 1, '2025-05-12 18:57:08', 'Entregado', 'Restaurante', 30.00, 0.00, 3.60, 33.60, 'x'),
(27, 1, NULL, 2, 1, '2025-05-12 18:58:53', 'Entregado', 'Restaurante', 20.00, 0.00, 2.40, 22.40, ''),
(28, 1, NULL, 1, 1, '2025-05-12 19:50:53', 'Cancelado', 'Restaurante', 30.00, 0.00, 3.60, 33.60, ''),
(29, 1, NULL, 1, 1, '2025-05-12 19:56:51', 'Cancelado', 'Restaurante', 30.00, 0.00, 3.60, 33.60, ''),
(30, 1, NULL, 2, 1, '2025-05-12 19:59:22', 'Cancelado', 'Restaurante', 40.00, 0.00, 4.80, 44.80, ''),
(31, 1, NULL, 1, 1, '2025-05-12 20:17:37', 'Entregado', 'Restaurante', 30.00, 0.00, 3.60, 33.60, ''),
(32, 1, NULL, 1, 1, '2025-05-12 20:52:10', 'Entregado', 'Restaurante', 30.00, 0.00, 3.60, 33.60, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquetecomponentes`
--

CREATE TABLE `paquetecomponentes` (
  `ComponenteID` int(11) NOT NULL,
  `PaqueteID` int(11) DEFAULT NULL,
  `Tipo` enum('Habitacion','Servicio','Producto') NOT NULL,
  `ItemID` int(11) NOT NULL,
  `Cantidad` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paquetecomponentes`
--

INSERT INTO `paquetecomponentes` (`ComponenteID`, `PaqueteID`, `Tipo`, `ItemID`, `Cantidad`) VALUES
(10, 4, 'Producto', 1, 1),
(11, 4, 'Habitacion', 1, 1),
(12, 4, 'Servicio', 2, 1),
(13, 5, 'Producto', 1, 1),
(14, 5, 'Habitacion', 1, 1),
(15, 5, 'Servicio', 2, 1),
(28, 3, 'Servicio', 2, 1),
(29, 3, 'Habitacion', 1, 1),
(30, 3, 'Servicio', 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquetes`
--

CREATE TABLE `paquetes` (
  `PaqueteID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `DuracionDias` int(11) DEFAULT 1,
  `Activo` tinyint(1) DEFAULT 1,
  `FechaCreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paquetes`
--

INSERT INTO `paquetes` (`PaqueteID`, `Nombre`, `Descripcion`, `Precio`, `DuracionDias`, `Activo`, `FechaCreacion`) VALUES
(3, 'toño2', 'stdhgdsgrdgrd', 50.00, 1, 1, '2025-05-09 09:05:49'),
(4, 'toño', 'stdhgdsgrdgrd', 50.00, 1, 1, '2025-05-09 09:06:05'),
(5, 'toño', 'stdhgdsgrdgrd', 50.00, 1, 1, '2025-05-09 09:06:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `platillos`
--

CREATE TABLE `platillos` (
  `PlatilloID` int(11) NOT NULL,
  `Codigo` varchar(20) DEFAULT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `CategoriaPlatilloID` int(11) DEFAULT NULL,
  `PrecioVenta` decimal(10,2) NOT NULL,
  `TiempoPreparacion` int(11) DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1,
  `Imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `platillos`
--

INSERT INTO `platillos` (`PlatilloID`, `Codigo`, `Nombre`, `Descripcion`, `CategoriaPlatilloID`, `PrecioVenta`, `TiempoPreparacion`, `Activo`, `Imagen`) VALUES
(1, '21312321312312', 'Pescado Frito', 'Pargo rojo frito con arepa y limon', 1, 10.00, 15, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `ProductoID` int(11) NOT NULL,
  `CodigoBarras` varchar(50) DEFAULT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `CategoriaID` int(11) DEFAULT NULL,
  `ProveedorID` int(11) DEFAULT NULL,
  `PrecioCompra` decimal(10,2) NOT NULL,
  `PrecioVenta` decimal(10,2) NOT NULL,
  `Stock` int(11) DEFAULT 0,
  `StockMinimo` int(11) DEFAULT 5,
  `UnidadMedida` varchar(20) DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1,
  `FechaCreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`ProductoID`, `CodigoBarras`, `Nombre`, `Descripcion`, `CategoriaID`, `ProveedorID`, `PrecioCompra`, `PrecioVenta`, `Stock`, `StockMinimo`, `UnidadMedida`, `Activo`, `FechaCreacion`) VALUES
(1, 'sin', 'Merluz', 'comprada a x', 1, NULL, 4.00, 5.00, 1, 5, 'Kilogramo', 1, '2025-05-07 20:41:36'),
(8, '21312321312312', 'pargo', 'aaaaaaaaaaaaaaa', 1, 1, 22.00, 24.00, 7, 5, 'Kilogramo', 1, '2025-05-07 21:07:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `ProveedorID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `RUC` varchar(20) DEFAULT NULL,
  `Telefono` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Direccion` text DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`ProveedorID`, `Nombre`, `RUC`, `Telefono`, `Email`, `Direccion`, `Activo`) VALUES
(1, 'toño', 'a2332', '111111111', 'JESUSALFREDO187@gmail.com', 'amuay calle x xx xxx', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `RecetaID` int(11) NOT NULL,
  `PlatilloID` int(11) NOT NULL,
  `IngredienteID` int(11) NOT NULL,
  `Cantidad` decimal(10,3) NOT NULL,
  `Notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`RecetaID`, `PlatilloID`, `IngredienteID`, `Cantidad`, `Notas`) VALUES
(2, 1, 4, 0.200, ''),
(3, 1, 12, 1.000, ''),
(8, 1, 1, 0.500, ''),
(9, 1, 5, 0.500, ''),
(10, 1, 6, 0.001, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registrocheckin`
--

CREATE TABLE `registrocheckin` (
  `ID` int(11) NOT NULL,
  `ReservacionID` int(11) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `UsuarioID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registrocheckin`
--

INSERT INTO `registrocheckin` (`ID`, `ReservacionID`, `FechaHora`, `UsuarioID`) VALUES
(25, 21, '2025-05-08 14:00:47', 1),
(26, 21, '2025-05-08 14:03:43', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registrocheckout`
--

CREATE TABLE `registrocheckout` (
  `ID` int(11) NOT NULL,
  `ReservacionID` int(11) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `UsuarioID` int(11) NOT NULL,
  `VentaID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registrocheckout`
--

INSERT INTO `registrocheckout` (`ID`, `ReservacionID`, `FechaHora`, `UsuarioID`, `VentaID`) VALUES
(10, 21, '2025-05-08 14:03:45', 1, 13);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservaciones`
--

CREATE TABLE `reservaciones` (
  `ReservacionID` int(11) NOT NULL,
  `ClienteID` int(11) DEFAULT NULL,
  `HabitacionID` int(11) DEFAULT NULL,
  `FechaEntrada` date NOT NULL,
  `FechaSalida` date NOT NULL,
  `Adultos` int(11) DEFAULT 1,
  `Ninos` int(11) DEFAULT 0,
  `Estado` enum('Pendiente','Confirmada','Cancelada','NoShow','Completada') DEFAULT 'Pendiente',
  `Total` decimal(10,2) DEFAULT NULL,
  `Notas` text DEFAULT NULL,
  `FechaCreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservaciones`
--

INSERT INTO `reservaciones` (`ReservacionID`, `ClienteID`, `HabitacionID`, `FechaEntrada`, `FechaSalida`, `Adultos`, `Ninos`, `Estado`, `Total`, `Notas`, `FechaCreacion`) VALUES
(21, 1, 1, '2025-05-08', '2025-05-13', 2, 0, 'Completada', NULL, '', '2025-05-08 14:00:40'),
(22, 1, 2, '2025-05-09', '2025-05-10', 2, 0, 'Cancelada', NULL, '', '2025-05-08 22:24:28'),
(23, 1, 2, '2025-05-10', '2025-05-11', 2, 0, 'Cancelada', NULL, '', '2025-05-08 22:24:54'),
(24, 1, 1, '2025-05-30', '2025-05-31', 1, 0, 'Completada', 25.00, NULL, '2025-05-09 11:02:19'),
(25, 1, 1, '2025-05-14', '2025-05-15', 1, 0, 'Completada', 25.00, NULL, '2025-05-09 11:18:29'),
(26, 1, 1, '2025-05-29', '2025-05-30', 2, 0, 'Pendiente', NULL, '', '2025-05-09 11:31:16'),
(27, 1, 1, '2025-05-31', '2025-06-01', 1, 0, 'Completada', 25.00, NULL, '2025-05-09 12:44:58'),
(28, 1, 1, '2025-05-28', '2025-05-29', 1, 0, 'Completada', 25.00, NULL, '2025-05-09 12:45:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `ServicioID` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Tipo` enum('Habitacion','Paquete','General') DEFAULT 'General',
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`ServicioID`, `Nombre`, `Descripcion`, `Precio`, `Tipo`, `Activo`) VALUES
(2, 'Desauno, Almuerzo y cena', 'Desayuno y almuerzo completo a la habitacion', 30.00, 'Habitacion', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `serviciosprogramados`
--

CREATE TABLE `serviciosprogramados` (
  `ProgramacionID` int(11) NOT NULL,
  `VentaID` int(11) DEFAULT NULL,
  `ServicioID` int(11) DEFAULT NULL,
  `Fecha` date NOT NULL,
  `Cantidad` int(11) DEFAULT 1,
  `PrecioUnitario` decimal(10,2) NOT NULL,
  `Estado` enum('Pendiente','Completado','Cancelado') DEFAULT 'Pendiente',
  `Notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiposhabitacion`
--

CREATE TABLE `tiposhabitacion` (
  `TipoHabitacionID` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Capacidad` int(11) DEFAULT 2,
  `PrecioNoche` decimal(10,2) NOT NULL,
  `Imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tiposhabitacion`
--

INSERT INTO `tiposhabitacion` (`TipoHabitacionID`, `Nombre`, `Descripcion`, `Capacidad`, `PrecioNoche`, `Imagen`) VALUES
(1, 'Familiar', '', 4, 25.00, NULL),
(2, 'grande', '', 2, 35.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `UsuarioID` int(11) NOT NULL,
  `EmpleadoID` int(11) DEFAULT NULL,
  `NombreUsuario` varchar(50) NOT NULL,
  `Contrasena` varchar(255) NOT NULL,
  `Rol` enum('Admin','Gerente','Recepcion','Cajero','Bodega') NOT NULL,
  `Activo` tinyint(1) DEFAULT 1,
  `UltimoLogin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`UsuarioID`, `EmpleadoID`, `NombreUsuario`, `Contrasena`, `Rol`, `Activo`, `UltimoLogin`) VALUES
(1, 1, 'jesus', '27924797', 'Admin', 1, '2025-05-01 19:59:39'),
(2, 2, 'Joseal', '$2y$10$XLqRA05VSRX3ISFHcPwPR.s6Nm/aQ55KnUzWahieWd7JANJFk6hxi', '', 1, NULL),
(3, 2, 'Josealv', '22222222', '', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventadetalles`
--

CREATE TABLE `ventadetalles` (
  `DetalleID` int(11) NOT NULL,
  `VentaID` int(11) DEFAULT NULL,
  `ProductoID` int(11) DEFAULT NULL,
  `Cantidad` int(11) NOT NULL,
  `PrecioUnitario` decimal(10,2) NOT NULL,
  `Descuento` decimal(10,2) DEFAULT 0.00,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventadetalles`
--

INSERT INTO `ventadetalles` (`DetalleID`, `VentaID`, `ProductoID`, `Cantidad`, `PrecioUnitario`, `Descuento`, `Subtotal`) VALUES
(1, 14, 1, 3, 5.00, 0.00, 15.00),
(2, 14, 8, 3, 24.00, 0.00, 72.00),
(3, 16, 8, 3, 24.00, 0.00, 72.00),
(4, 16, 1, 2, 5.00, 0.00, 10.00),
(5, 17, 8, 1, 24.00, 0.00, 24.00),
(6, 26, 1, 1, 5.00, 0.00, 5.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `VentaID` int(11) NOT NULL,
  `ClienteID` int(11) DEFAULT NULL,
  `UsuarioID` int(11) DEFAULT NULL,
  `FechaHora` datetime DEFAULT current_timestamp(),
  `Tipo` enum('Producto','Servicio','Habitacion','Paquete','Restaurante') NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  `Descuento` decimal(10,2) DEFAULT 0.00,
  `Impuesto` decimal(10,2) DEFAULT 0.00,
  `Total` decimal(10,2) NOT NULL,
  `MetodoPago` enum('Efectivo','Tarjeta','Transferencia','Mixto') NOT NULL,
  `Estado` enum('Pendiente','Completada','Cancelada') DEFAULT 'Completada',
  `Notas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`VentaID`, `ClienteID`, `UsuarioID`, `FechaHora`, `Tipo`, `Subtotal`, `Descuento`, `Impuesto`, `Total`, `MetodoPago`, `Estado`, `Notas`) VALUES
(13, 1, 1, '2025-05-08 14:03:45', 'Habitacion', 125.00, 0.00, 20.00, 145.00, 'Efectivo', 'Completada', NULL),
(14, 1, 1, '2025-05-08 22:32:20', 'Producto', 87.00, 0.00, 13.92, 100.92, 'Tarjeta', 'Completada', ''),
(15, NULL, 1, '2025-05-08 22:56:42', 'Producto', 25.00, 0.00, 4.00, 29.00, 'Efectivo', 'Cancelada', ''),
(16, NULL, 1, '2025-05-09 08:10:33', 'Producto', 112.00, 0.00, 17.92, 129.92, 'Efectivo', 'Cancelada', ''),
(17, NULL, 1, '2025-05-09 08:11:24', 'Producto', 54.00, 0.00, 8.64, 62.64, 'Efectivo', 'Cancelada', ''),
(18, 1, 1, '2025-05-09 08:16:10', 'Producto', 30.00, 0.00, 4.80, 34.80, 'Tarjeta', 'Cancelada', ''),
(25, 1, 1, '2025-05-09 10:40:35', 'Paquete', 50.00, 0.00, 0.00, 50.00, 'Tarjeta', 'Completada', NULL),
(26, 1, 1, '2025-05-09 10:46:59', 'Paquete', 50.00, 0.00, 0.00, 50.00, 'Tarjeta', 'Completada', NULL),
(31, 1, 1, '2025-05-09 11:02:19', 'Paquete', 50.00, 0.00, 0.00, 50.00, 'Tarjeta', 'Completada', NULL),
(33, 1, 1, '2025-05-09 11:18:29', 'Paquete', 50.00, 0.00, 0.00, 50.00, 'Efectivo', 'Completada', NULL),
(44, 1, 1, '2025-05-09 12:44:58', 'Paquete', 50.00, 0.00, 0.00, 50.00, 'Efectivo', 'Completada', NULL),
(45, 1, 1, '2025-05-09 12:45:10', 'Paquete', 50.00, 0.00, 0.00, 50.00, 'Efectivo', 'Completada', NULL),
(47, 1, 1, '2025-05-12 19:04:50', '', 20.00, 0.00, 2.40, 22.40, 'Efectivo', 'Completada', NULL),
(48, 1, 1, '2025-05-12 19:05:47', '', 20.00, 0.00, 2.40, 22.40, 'Efectivo', 'Completada', NULL),
(49, 1, 1, '2025-05-12 19:50:17', '', 30.00, 0.00, 3.60, 33.60, 'Efectivo', 'Completada', NULL),
(50, 1, 1, '2025-05-12 20:17:46', 'Restaurante', 30.00, 0.00, 3.60, 33.60, 'Efectivo', 'Completada', NULL),
(51, 1, 1, '2025-05-12 20:52:33', 'Restaurante', 30.00, 0.00, 3.60, 33.60, 'Efectivo', 'Completada', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventasanulaciones`
--

CREATE TABLE `ventasanulaciones` (
  `ID` int(11) NOT NULL,
  `VentaID` int(11) NOT NULL,
  `FechaAnulacion` datetime NOT NULL,
  `UsuarioID` int(11) NOT NULL,
  `Motivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventasanulaciones`
--

INSERT INTO `ventasanulaciones` (`ID`, `VentaID`, `FechaAnulacion`, `UsuarioID`, `Motivo`) VALUES
(1, 18, '2025-05-09 08:21:13', 1, 'Anulación manual'),
(2, 17, '2025-05-09 08:21:20', 1, 'Anulación manual'),
(3, 16, '2025-05-09 08:21:23', 1, 'Anulación manual'),
(4, 15, '2025-05-09 08:21:25', 1, 'Anulación manual');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventaservicios`
--

CREATE TABLE `ventaservicios` (
  `VentaServicioID` int(11) NOT NULL,
  `VentaID` int(11) DEFAULT NULL,
  `Tipo` enum('Habitacion','Servicio','Paquete') NOT NULL,
  `ItemID` int(11) NOT NULL,
  `FechaInicio` datetime DEFAULT NULL,
  `FechaFin` datetime DEFAULT NULL,
  `Cantidad` int(11) DEFAULT 1,
  `PrecioUnitario` decimal(10,2) NOT NULL,
  `Descuento` decimal(10,2) DEFAULT 0.00,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventaservicios`
--

INSERT INTO `ventaservicios` (`VentaServicioID`, `VentaID`, `Tipo`, `ItemID`, `FechaInicio`, `FechaFin`, `Cantidad`, `PrecioUnitario`, `Descuento`, `Subtotal`) VALUES
(13, 13, 'Habitacion', 1, '2025-05-08 00:00:00', '2025-05-13 00:00:00', 5, 25.00, 0.00, 125.00),
(14, 15, 'Servicio', 1, NULL, NULL, 1, 25.00, 0.00, 25.00),
(15, 16, 'Servicio', 2, NULL, NULL, 1, 30.00, 0.00, 30.00),
(16, 17, 'Servicio', 2, NULL, NULL, 1, 30.00, 0.00, 30.00),
(17, 18, 'Servicio', 2, NULL, NULL, 1, 30.00, 0.00, 30.00),
(30, 25, 'Paquete', 3, '2025-05-10 00:00:00', '2025-05-11 00:00:00', 1, 50.00, 0.00, 50.00),
(31, 26, 'Paquete', 4, '2025-05-17 00:00:00', '2025-05-18 00:00:00', 1, 50.00, 0.00, 50.00),
(38, 31, 'Paquete', 3, '2025-05-30 00:00:00', '2025-05-31 00:00:00', 1, 50.00, 0.00, 50.00),
(39, 31, 'Servicio', 2, '2025-05-30 00:00:00', '2025-05-31 00:00:00', 1, 30.00, 0.00, 30.00),
(40, 31, 'Habitacion', 1, '2025-05-30 00:00:00', '2025-05-31 00:00:00', 1, 25.00, 0.00, 25.00),
(41, 31, 'Servicio', 2, '2025-05-30 00:00:00', '2025-05-31 00:00:00', 1, 30.00, 0.00, 30.00),
(44, 33, 'Paquete', 3, '2025-05-14 00:00:00', '2025-05-15 00:00:00', 1, 50.00, 0.00, 50.00),
(45, 33, 'Servicio', 2, '2025-05-14 00:00:00', '2025-05-15 00:00:00', 1, 30.00, 0.00, 30.00),
(46, 33, 'Habitacion', 1, '2025-05-14 00:00:00', '2025-05-15 00:00:00', 1, 25.00, 0.00, 25.00),
(47, 33, 'Servicio', 2, '2025-05-14 00:00:00', '2025-05-15 00:00:00', 1, 30.00, 0.00, 30.00),
(65, 44, 'Paquete', 3, '2025-05-31 00:00:00', '2025-06-01 00:00:00', 1, 50.00, 0.00, 50.00),
(66, 44, 'Servicio', 2, '2025-05-31 00:00:00', '2025-06-01 00:00:00', 1, 30.00, 0.00, 30.00),
(67, 44, 'Habitacion', 1, '2025-05-31 00:00:00', '2025-06-01 00:00:00', 1, 25.00, 0.00, 25.00),
(68, 44, 'Servicio', 2, '2025-05-31 00:00:00', '2025-06-01 00:00:00', 1, 30.00, 0.00, 30.00),
(69, 45, 'Paquete', 3, '2025-05-28 00:00:00', '2025-05-29 00:00:00', 1, 50.00, 0.00, 50.00),
(70, 45, 'Servicio', 2, '2025-05-28 00:00:00', '2025-05-29 00:00:00', 1, 30.00, 0.00, 30.00),
(71, 45, 'Habitacion', 1, '2025-05-28 00:00:00', '2025-05-29 00:00:00', 1, 25.00, 0.00, 25.00),
(72, 45, 'Servicio', 2, '2025-05-28 00:00:00', '2025-05-29 00:00:00', 1, 30.00, 0.00, 30.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventasrestaurante`
--

CREATE TABLE `ventasrestaurante` (
  `VentaRestauranteID` int(11) NOT NULL,
  `VentaID` int(11) NOT NULL,
  `OrdenID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventasrestaurante`
--

INSERT INTO `ventasrestaurante` (`VentaRestauranteID`, `VentaID`, `OrdenID`) VALUES
(2, 47, 27),
(3, 48, 25),
(4, 49, 26),
(5, 50, 31),
(6, 51, 32);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoriasplatillos`
--
ALTER TABLE `categoriasplatillos`
  ADD PRIMARY KEY (`CategoriaPlatilloID`);

--
-- Indices de la tabla `categoriasproductos`
--
ALTER TABLE `categoriasproductos`
  ADD PRIMARY KEY (`CategoriaID`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`ClienteID`),
  ADD UNIQUE KEY `NumeroDocumento` (`NumeroDocumento`);

--
-- Indices de la tabla `compradetalles`
--
ALTER TABLE `compradetalles`
  ADD PRIMARY KEY (`DetalleID`),
  ADD KEY `CompraID` (`CompraID`),
  ADD KEY `ProductoID` (`ProductoID`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`CompraID`),
  ADD KEY `ProveedorID` (`ProveedorID`),
  ADD KEY `UsuarioID` (`UsuarioID`);

--
-- Indices de la tabla `consumoingredientes`
--
ALTER TABLE `consumoingredientes`
  ADD PRIMARY KEY (`ConsumoID`),
  ADD KEY `OrdenDetalleID` (`OrdenDetalleID`),
  ADD KEY `IngredienteID` (`IngredienteID`),
  ADD KEY `UsuarioID` (`UsuarioID`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`EmpleadoID`),
  ADD UNIQUE KEY `NumeroDocumento` (`NumeroDocumento`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`HabitacionID`),
  ADD UNIQUE KEY `Numero` (`Numero`),
  ADD KEY `TipoHabitacionID` (`TipoHabitacionID`);

--
-- Indices de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  ADD PRIMARY KEY (`IngredienteID`);

--
-- Indices de la tabla `ingredientesmovimientos`
--
ALTER TABLE `ingredientesmovimientos`
  ADD PRIMARY KEY (`idmovimientoi`);

--
-- Indices de la tabla `inventariomovimientos`
--
ALTER TABLE `inventariomovimientos`
  ADD PRIMARY KEY (`MovimientoID`),
  ADD KEY `ProductoID` (`ProductoID`),
  ADD KEY `UsuarioID` (`UsuarioID`);

--
-- Indices de la tabla `mesasrestaurante`
--
ALTER TABLE `mesasrestaurante`
  ADD PRIMARY KEY (`MesaID`),
  ADD UNIQUE KEY `Numero` (`Numero`);

--
-- Indices de la tabla `ordendetalles`
--
ALTER TABLE `ordendetalles`
  ADD PRIMARY KEY (`DetalleID`),
  ADD KEY `OrdenID` (`OrdenID`),
  ADD KEY `PlatilloID` (`PlatilloID`);

--
-- Indices de la tabla `ordenesrestaurante`
--
ALTER TABLE `ordenesrestaurante`
  ADD PRIMARY KEY (`OrdenID`),
  ADD KEY `ClienteID` (`ClienteID`),
  ADD KEY `HabitacionID` (`HabitacionID`),
  ADD KEY `UsuarioID` (`UsuarioID`),
  ADD KEY `MesaID` (`MesaID`);

--
-- Indices de la tabla `paquetecomponentes`
--
ALTER TABLE `paquetecomponentes`
  ADD PRIMARY KEY (`ComponenteID`),
  ADD KEY `PaqueteID` (`PaqueteID`);

--
-- Indices de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  ADD PRIMARY KEY (`PaqueteID`);

--
-- Indices de la tabla `platillos`
--
ALTER TABLE `platillos`
  ADD PRIMARY KEY (`PlatilloID`),
  ADD UNIQUE KEY `Codigo` (`Codigo`),
  ADD KEY `CategoriaPlatilloID` (`CategoriaPlatilloID`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`ProductoID`),
  ADD UNIQUE KEY `CodigoBarras` (`CodigoBarras`),
  ADD KEY `CategoriaID` (`CategoriaID`),
  ADD KEY `ProveedorID` (`ProveedorID`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`ProveedorID`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`RecetaID`),
  ADD UNIQUE KEY `PlatilloID` (`PlatilloID`,`IngredienteID`),
  ADD KEY `IngredienteID` (`IngredienteID`);

--
-- Indices de la tabla `registrocheckin`
--
ALTER TABLE `registrocheckin`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ReservacionID` (`ReservacionID`),
  ADD KEY `UsuarioID` (`UsuarioID`);

--
-- Indices de la tabla `registrocheckout`
--
ALTER TABLE `registrocheckout`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ReservacionID` (`ReservacionID`),
  ADD KEY `UsuarioID` (`UsuarioID`),
  ADD KEY `VentaID` (`VentaID`);

--
-- Indices de la tabla `reservaciones`
--
ALTER TABLE `reservaciones`
  ADD PRIMARY KEY (`ReservacionID`),
  ADD KEY `ClienteID` (`ClienteID`),
  ADD KEY `HabitacionID` (`HabitacionID`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`ServicioID`);

--
-- Indices de la tabla `serviciosprogramados`
--
ALTER TABLE `serviciosprogramados`
  ADD PRIMARY KEY (`ProgramacionID`),
  ADD KEY `VentaID` (`VentaID`),
  ADD KEY `ServicioID` (`ServicioID`);

--
-- Indices de la tabla `tiposhabitacion`
--
ALTER TABLE `tiposhabitacion`
  ADD PRIMARY KEY (`TipoHabitacionID`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`UsuarioID`),
  ADD UNIQUE KEY `NombreUsuario` (`NombreUsuario`),
  ADD KEY `EmpleadoID` (`EmpleadoID`);

--
-- Indices de la tabla `ventadetalles`
--
ALTER TABLE `ventadetalles`
  ADD PRIMARY KEY (`DetalleID`),
  ADD KEY `VentaID` (`VentaID`),
  ADD KEY `ProductoID` (`ProductoID`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`VentaID`),
  ADD KEY `ClienteID` (`ClienteID`),
  ADD KEY `UsuarioID` (`UsuarioID`);

--
-- Indices de la tabla `ventasanulaciones`
--
ALTER TABLE `ventasanulaciones`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `ventaservicios`
--
ALTER TABLE `ventaservicios`
  ADD PRIMARY KEY (`VentaServicioID`),
  ADD KEY `VentaID` (`VentaID`);

--
-- Indices de la tabla `ventasrestaurante`
--
ALTER TABLE `ventasrestaurante`
  ADD PRIMARY KEY (`VentaRestauranteID`),
  ADD KEY `VentaID` (`VentaID`),
  ADD KEY `OrdenID` (`OrdenID`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoriasplatillos`
--
ALTER TABLE `categoriasplatillos`
  MODIFY `CategoriaPlatilloID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categoriasproductos`
--
ALTER TABLE `categoriasproductos`
  MODIFY `CategoriaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `ClienteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `compradetalles`
--
ALTER TABLE `compradetalles`
  MODIFY `DetalleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `CompraID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consumoingredientes`
--
ALTER TABLE `consumoingredientes`
  MODIFY `ConsumoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `EmpleadoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `HabitacionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ingredientes`
--
ALTER TABLE `ingredientes`
  MODIFY `IngredienteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `ingredientesmovimientos`
--
ALTER TABLE `ingredientesmovimientos`
  MODIFY `idmovimientoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `inventariomovimientos`
--
ALTER TABLE `inventariomovimientos`
  MODIFY `MovimientoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `mesasrestaurante`
--
ALTER TABLE `mesasrestaurante`
  MODIFY `MesaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ordendetalles`
--
ALTER TABLE `ordendetalles`
  MODIFY `DetalleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `ordenesrestaurante`
--
ALTER TABLE `ordenesrestaurante`
  MODIFY `OrdenID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `paquetecomponentes`
--
ALTER TABLE `paquetecomponentes`
  MODIFY `ComponenteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `paquetes`
--
ALTER TABLE `paquetes`
  MODIFY `PaqueteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `platillos`
--
ALTER TABLE `platillos`
  MODIFY `PlatilloID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `ProductoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `ProveedorID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `RecetaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `registrocheckin`
--
ALTER TABLE `registrocheckin`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `registrocheckout`
--
ALTER TABLE `registrocheckout`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `reservaciones`
--
ALTER TABLE `reservaciones`
  MODIFY `ReservacionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `ServicioID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `serviciosprogramados`
--
ALTER TABLE `serviciosprogramados`
  MODIFY `ProgramacionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tiposhabitacion`
--
ALTER TABLE `tiposhabitacion`
  MODIFY `TipoHabitacionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `UsuarioID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ventadetalles`
--
ALTER TABLE `ventadetalles`
  MODIFY `DetalleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `VentaID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `ventasanulaciones`
--
ALTER TABLE `ventasanulaciones`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ventaservicios`
--
ALTER TABLE `ventaservicios`
  MODIFY `VentaServicioID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT de la tabla `ventasrestaurante`
--
ALTER TABLE `ventasrestaurante`
  MODIFY `VentaRestauranteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compradetalles`
--
ALTER TABLE `compradetalles`
  ADD CONSTRAINT `compradetalles_ibfk_1` FOREIGN KEY (`CompraID`) REFERENCES `compras` (`CompraID`),
  ADD CONSTRAINT `compradetalles_ibfk_2` FOREIGN KEY (`ProductoID`) REFERENCES `productos` (`ProductoID`);

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`ProveedorID`) REFERENCES `proveedores` (`ProveedorID`),
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`UsuarioID`) REFERENCES `usuarios` (`UsuarioID`);

--
-- Filtros para la tabla `consumoingredientes`
--
ALTER TABLE `consumoingredientes`
  ADD CONSTRAINT `consumoingredientes_ibfk_1` FOREIGN KEY (`OrdenDetalleID`) REFERENCES `ordendetalles` (`DetalleID`),
  ADD CONSTRAINT `consumoingredientes_ibfk_2` FOREIGN KEY (`IngredienteID`) REFERENCES `ingredientes` (`IngredienteID`),
  ADD CONSTRAINT `consumoingredientes_ibfk_3` FOREIGN KEY (`UsuarioID`) REFERENCES `usuarios` (`UsuarioID`);

--
-- Filtros para la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD CONSTRAINT `habitaciones_ibfk_1` FOREIGN KEY (`TipoHabitacionID`) REFERENCES `tiposhabitacion` (`TipoHabitacionID`);

--
-- Filtros para la tabla `inventariomovimientos`
--
ALTER TABLE `inventariomovimientos`
  ADD CONSTRAINT `inventariomovimientos_ibfk_1` FOREIGN KEY (`ProductoID`) REFERENCES `productos` (`ProductoID`),
  ADD CONSTRAINT `inventariomovimientos_ibfk_2` FOREIGN KEY (`UsuarioID`) REFERENCES `usuarios` (`UsuarioID`);

--
-- Filtros para la tabla `ordendetalles`
--
ALTER TABLE `ordendetalles`
  ADD CONSTRAINT `ordendetalles_ibfk_1` FOREIGN KEY (`OrdenID`) REFERENCES `ordenesrestaurante` (`OrdenID`),
  ADD CONSTRAINT `ordendetalles_ibfk_2` FOREIGN KEY (`PlatilloID`) REFERENCES `platillos` (`PlatilloID`);

--
-- Filtros para la tabla `ordenesrestaurante`
--
ALTER TABLE `ordenesrestaurante`
  ADD CONSTRAINT `ordenesrestaurante_ibfk_1` FOREIGN KEY (`ClienteID`) REFERENCES `clientes` (`ClienteID`),
  ADD CONSTRAINT `ordenesrestaurante_ibfk_2` FOREIGN KEY (`HabitacionID`) REFERENCES `habitaciones` (`HabitacionID`),
  ADD CONSTRAINT `ordenesrestaurante_ibfk_3` FOREIGN KEY (`UsuarioID`) REFERENCES `usuarios` (`UsuarioID`),
  ADD CONSTRAINT `ordenesrestaurante_ibfk_4` FOREIGN KEY (`MesaID`) REFERENCES `mesasrestaurante` (`MesaID`);

--
-- Filtros para la tabla `paquetecomponentes`
--
ALTER TABLE `paquetecomponentes`
  ADD CONSTRAINT `paquetecomponentes_ibfk_1` FOREIGN KEY (`PaqueteID`) REFERENCES `paquetes` (`PaqueteID`);

--
-- Filtros para la tabla `platillos`
--
ALTER TABLE `platillos`
  ADD CONSTRAINT `platillos_ibfk_1` FOREIGN KEY (`CategoriaPlatilloID`) REFERENCES `categoriasplatillos` (`CategoriaPlatilloID`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`CategoriaID`) REFERENCES `categoriasproductos` (`CategoriaID`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`ProveedorID`) REFERENCES `proveedores` (`ProveedorID`);

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`PlatilloID`) REFERENCES `platillos` (`PlatilloID`),
  ADD CONSTRAINT `recetas_ibfk_2` FOREIGN KEY (`IngredienteID`) REFERENCES `ingredientes` (`IngredienteID`);

--
-- Filtros para la tabla `registrocheckin`
--
ALTER TABLE `registrocheckin`
  ADD CONSTRAINT `registrocheckin_ibfk_1` FOREIGN KEY (`ReservacionID`) REFERENCES `reservaciones` (`ReservacionID`),
  ADD CONSTRAINT `registrocheckin_ibfk_2` FOREIGN KEY (`UsuarioID`) REFERENCES `usuarios` (`UsuarioID`);

--
-- Filtros para la tabla `registrocheckout`
--
ALTER TABLE `registrocheckout`
  ADD CONSTRAINT `registrocheckout_ibfk_1` FOREIGN KEY (`ReservacionID`) REFERENCES `reservaciones` (`ReservacionID`),
  ADD CONSTRAINT `registrocheckout_ibfk_2` FOREIGN KEY (`UsuarioID`) REFERENCES `usuarios` (`UsuarioID`),
  ADD CONSTRAINT `registrocheckout_ibfk_3` FOREIGN KEY (`VentaID`) REFERENCES `ventas` (`VentaID`);

--
-- Filtros para la tabla `reservaciones`
--
ALTER TABLE `reservaciones`
  ADD CONSTRAINT `reservaciones_ibfk_1` FOREIGN KEY (`ClienteID`) REFERENCES `clientes` (`ClienteID`),
  ADD CONSTRAINT `reservaciones_ibfk_2` FOREIGN KEY (`HabitacionID`) REFERENCES `habitaciones` (`HabitacionID`);

--
-- Filtros para la tabla `serviciosprogramados`
--
ALTER TABLE `serviciosprogramados`
  ADD CONSTRAINT `serviciosprogramados_ibfk_1` FOREIGN KEY (`VentaID`) REFERENCES `ventas` (`VentaID`),
  ADD CONSTRAINT `serviciosprogramados_ibfk_2` FOREIGN KEY (`ServicioID`) REFERENCES `servicios` (`ServicioID`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleados` (`EmpleadoID`);

--
-- Filtros para la tabla `ventadetalles`
--
ALTER TABLE `ventadetalles`
  ADD CONSTRAINT `ventadetalles_ibfk_1` FOREIGN KEY (`VentaID`) REFERENCES `ventas` (`VentaID`),
  ADD CONSTRAINT `ventadetalles_ibfk_2` FOREIGN KEY (`ProductoID`) REFERENCES `productos` (`ProductoID`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`ClienteID`) REFERENCES `clientes` (`ClienteID`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`UsuarioID`) REFERENCES `usuarios` (`UsuarioID`);

--
-- Filtros para la tabla `ventaservicios`
--
ALTER TABLE `ventaservicios`
  ADD CONSTRAINT `ventaservicios_ibfk_1` FOREIGN KEY (`VentaID`) REFERENCES `ventas` (`VentaID`);

--
-- Filtros para la tabla `ventasrestaurante`
--
ALTER TABLE `ventasrestaurante`
  ADD CONSTRAINT `ventasrestaurante_ibfk_1` FOREIGN KEY (`VentaID`) REFERENCES `ventas` (`VentaID`),
  ADD CONSTRAINT `ventasrestaurante_ibfk_2` FOREIGN KEY (`OrdenID`) REFERENCES `ordenesrestaurante` (`OrdenID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
