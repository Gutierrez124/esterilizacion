-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-10-2026 a las 19:26:48
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_esterilizacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proceso_esterilizacion`
--

CREATE TABLE `proceso_esterilizacion` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `servicio_id` bigint(20) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `turno` enum('DIURNO','NOCTURNO') NOT NULL,
  `hora` time DEFAULT NULL,
  `recibe_id` bigint(20) UNSIGNED NOT NULL,
  `entrega_id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_piezas` int(11) NOT NULL,
  `tipo_lavado` enum('ULTRASONICA','MANUAL','LAVADORA_DESCONTAMINADORA','NO_CORRESPONDE') NOT NULL,
  `equipo_biomedico` enum('LAVADOR_ULTRASONICO','BIDESTILADOR','LAVADORA_TERMODESINFECTADORA','PISTOLA_AIRE_COMPRIMIDO') DEFAULT NULL,
  `operador_id` bigint(20) UNSIGNED NOT NULL,
  `creado_por_id` bigint(20) UNSIGNED DEFAULT NULL,
  `estado_actual` enum('ROJA','AZUL','VERDE') NOT NULL DEFAULT 'ROJA',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `proceso_esterilizacion`
--

INSERT INTO `proceso_esterilizacion` (`id`, `servicio_id`, `fecha`, `turno`, `hora`, `recibe_id`, `entrega_id`, `material_id`, `cantidad_piezas`, `tipo_lavado`, `equipo_biomedico`, `operador_id`, `creado_por_id`, `estado_actual`, `creado_en`, `actualizado_en`) VALUES
(1, 1, '2026-10-16', 'DIURNO', '19:35:00', 2, 3, 12, 16, 'ULTRASONICA', 'LAVADOR_ULTRASONICO', 3, 10, 'ROJA', '2026-10-16 00:35:59', '2026-10-16 00:35:59'),
(3, 1, '2026-10-18', 'DIURNO', '09:00:00', 2, 1, 12, 16, 'ULTRASONICA', 'BIDESTILADOR', 3, 10, 'AZUL', '2026-10-18 14:00:51', '2026-10-18 14:00:51');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `proceso_esterilizacion`
--
ALTER TABLE `proceso_esterilizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proceso_esterilizacion_recibe_id_foreign` (`recibe_id`),
  ADD KEY `proceso_esterilizacion_entrega_id_foreign` (`entrega_id`),
  ADD KEY `proceso_esterilizacion_operador_id_foreign` (`operador_id`),
  ADD KEY `proceso_esterilizacion_fecha_index` (`fecha`),
  ADD KEY `proceso_esterilizacion_servicio_id_fecha_index` (`servicio_id`,`fecha`),
  ADD KEY `proceso_esterilizacion_estado_actual_fecha_index` (`estado_actual`,`fecha`),
  ADD KEY `proceso_esterilizacion_material_id_index` (`material_id`),
  ADD KEY `proceso_esterilizacion_creado_por_id_foreign` (`creado_por_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `proceso_esterilizacion`
--
ALTER TABLE `proceso_esterilizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `proceso_esterilizacion`
--
ALTER TABLE `proceso_esterilizacion`
  ADD CONSTRAINT `proceso_esterilizacion_creado_por_id_foreign` FOREIGN KEY (`creado_por_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `proceso_esterilizacion_entrega_id_foreign` FOREIGN KEY (`entrega_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `proceso_esterilizacion_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`),
  ADD CONSTRAINT `proceso_esterilizacion_operador_id_foreign` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `proceso_esterilizacion_recibe_id_foreign` FOREIGN KEY (`recibe_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `proceso_esterilizacion_servicio_id_foreign` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
