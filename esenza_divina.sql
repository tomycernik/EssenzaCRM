-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-08-2024 a las 20:07:53
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `esenza_divina`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aroma`
--

CREATE TABLE `aroma` (
  `id` int(10) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aroma`
--

INSERT INTO `aroma` (`id`, `nombre`) VALUES
(1, 'Lavadero'),
(2, 'Limon'),
(3, 'Vive'),
(4, 'Chicle'),
(5, 'Rosa y jazmin'),
(6, 'Sandia y pepino'),
(7, 'Naranja y pimienta'),
(8, 'Cony'),
(9, 'Vainicoco'),
(10, 'Uva'),
(11, 'Pera y flores blancas '),
(12, 'Verbena'),
(13, 'Frutilla');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id` int(10) NOT NULL,
  `cliente_id` int(100) NOT NULL,
  `cantidad_productos` int(10) NOT NULL,
  `costo_productos` int(100) NOT NULL,
  `costo_envio` int(10) NOT NULL,
  `costo_total` int(10) NOT NULL,
  `factura` varchar(100) NOT NULL,
  `medio_de_pago` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `id_vendedor` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id`, `cliente_id`, `cantidad_productos`, `costo_productos`, `costo_envio`, `costo_total`, `factura`, `medio_de_pago`, `fecha`, `id_vendedor`) VALUES
(1, 6, 0, 0, 0, 0, '', '', '2024-07-29', 6),
(2, 6, 0, 0, 0, 0, '', '', '2024-07-29', 6),
(3, 6, 0, 0, 0, 0, '', '', '2024-07-29', 6),
(4, 6, 0, 0, 0, 0, '', '', '2024-07-29', 6),
(5, 6, 0, 0, 0, 0, '', '', '2024-07-29', 6),
(6, 6, 7, 0, 0, 0, '', '', '2024-07-29', 6),
(7, 6, 6, 0, 0, 0, '', '', '2024-07-29', 6),
(8, 6, 5, 4500, 3000, 7500, '', 'Mercado pago', '2024-07-30', 6),
(9, 12, 45, 30000, 4566, 34566, '', 'Mercado pago', '2024-07-31', 6),
(10, 12, 109, 100000, 15666, 115666, '', 'Mercado pago', '2024-07-31', 6),
(11, 8, 57, 30000, 3000, 33000, '', 'Mercado pago', '2024-07-31', 13),
(12, 14, 11, 25000, 5000, 30000, '', 'Mercado pago', '2024-07-20', 7),
(13, 14, 10, 50000, 10000, 60000, '', 'Mercado pago', '2024-08-01', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_producto`
--

CREATE TABLE `pedido_producto` (
  `id` int(10) NOT NULL,
  `pedido_id` int(10) NOT NULL,
  `producto_id` int(10) NOT NULL,
  `cantidad` int(10) NOT NULL,
  `precio` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_producto`
--

INSERT INTO `pedido_producto` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio`) VALUES
(0, 1, 1, 5, 0),
(0, 1, 1, 4, 0),
(0, 1, 1, 6, 0),
(0, 2, 1, 5, 0),
(0, 2, 1, 4, 0),
(0, 2, 1, 6, 0),
(0, 3, 1, 6, 0),
(0, 4, 1, 5, 0),
(0, 5, 1, 5, 0),
(0, 6, 1, 5, 0),
(0, 6, 14, 2, 0),
(0, 7, 1, 6, 0),
(0, 8, 1, 5, 0),
(0, 9, 1, 45, 0),
(0, 10, 1, 109, 0),
(0, 11, 1, 45, 0),
(0, 11, 14, 12, 0),
(0, 12, 23, 4, 0),
(0, 12, 9, 7, 0),
(0, 13, 1, 5, 0),
(0, 13, 30, 5, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(10) NOT NULL,
  `tipo_id` int(10) NOT NULL,
  `aroma_id` int(11) NOT NULL,
  `precio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `tipo_id`, `aroma_id`, `precio`) VALUES
(1, 1, 1, 5000),
(2, 1, 2, 0),
(3, 1, 3, 0),
(4, 1, 4, 0),
(5, 1, 5, 0),
(6, 1, 6, 0),
(7, 1, 7, 0),
(8, 1, 8, 0),
(9, 1, 9, 0),
(10, 1, 10, 0),
(11, 1, 11, 0),
(12, 1, 12, 0),
(13, 1, 13, 0),
(14, 2, 1, 0),
(15, 2, 2, 0),
(16, 2, 3, 0),
(17, 2, 4, 0),
(18, 2, 5, 0),
(19, 2, 6, 0),
(20, 2, 7, 0),
(21, 2, 8, 0),
(22, 2, 9, 0),
(23, 2, 10, 0),
(24, 2, 11, 0),
(25, 2, 12, 0),
(26, 2, 13, 0),
(27, 3, 2, 0),
(28, 3, 7, 0),
(29, 3, 13, 0),
(30, 3, 10, 0),
(31, 3, 9, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_producto`
--

CREATE TABLE `tipo_producto` (
  `id` int(10) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_producto`
--

INSERT INTO `tipo_producto` (`id`, `nombre`) VALUES
(1, 'Textil'),
(2, 'Difusor'),
(3, 'Concentrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(10) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `celular` int(11) NOT NULL,
  `dni` int(11) NOT NULL,
  `rol` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `username`, `password`, `nombre`, `apellido`, `provincia`, `localidad`, `celular`, `dni`, `rol`) VALUES
(6, 'tomycernik', '$2y$10$8EO3NU3xxJHKIg5C93TRAetewnWCKj6aP9VAeIokryKn8cLVWIAXe', 'Tomas Cernik', '', '', '', 0, 0, 'admin'),
(7, 'tomycernik2', '$2y$10$eVbB9oIvTk3zy7K8KxhOPO8CBGX9VptE9SNCR3672veHKA/a7Bihy', 'Tomas Cernik', '', '', '', 0, 0, 'admin'),
(8, '', '', 'Manuel Benitez', '', '', '', 0, 0, 'cliente'),
(9, '', '', 'Manuel Benitez', '', '', '', 0, 0, 'cliente'),
(10, '', '', 'Manuel Benitez', '', '', '', 0, 0, 'cliente'),
(11, '', '', 'marcelo gallardo', '', '', '', 0, 0, 'cliente'),
(12, '', '', 'facundo colidio', '', 'San Juan', 'Mercedes', 1161948623, 44937345, 'cliente'),
(13, 'ml10', '$2y$10$UaeyahwF/MptW6iemzE4R.skTVk0DoOk5wcSYdedxoSw5mA7l7.Pa', 'manuel lanzini', '', '', '', 0, 0, 'admin'),
(14, '', '', 'lucila tonietti', '', 'Catamarca', 'Mercedes', 1161947262, 45400784, 'cliente');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aroma`
--
ALTER TABLE `aroma`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `id_vendedor` (`id_vendedor`);

--
-- Indices de la tabla `pedido_producto`
--
ALTER TABLE `pedido_producto`
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tipo_id` (`tipo_id`),
  ADD KEY `aroma_id` (`aroma_id`);

--
-- Indices de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aroma`
--
ALTER TABLE `aroma`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `tipo_producto`
--
ALTER TABLE `tipo_producto`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `usuario` (`id`),
  ADD CONSTRAINT `pedido_ibfk_2` FOREIGN KEY (`id_vendedor`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `pedido_producto`
--
ALTER TABLE `pedido_producto`
  ADD CONSTRAINT `pedido_producto_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`),
  ADD CONSTRAINT `pedido_producto_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`id`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`aroma_id`) REFERENCES `aroma` (`id`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`tipo_id`) REFERENCES `tipo_producto` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
