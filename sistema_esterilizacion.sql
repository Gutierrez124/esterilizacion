-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-10-2026 a las 03:00:38
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
-- Estructura de tabla para la tabla `area_azul`
--

CREATE TABLE `area_azul` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proceso_id` bigint(20) UNSIGNED NOT NULL,
  `hora` time DEFAULT NULL,
  `empaque` enum('PAPEL CREPADO','MANGA','PAPEL NO TEJIDO') DEFAULT NULL,
  `tipo_carga` enum('INSTRUMENTAL','TEXTIL','GOMA') NOT NULL,
  `numero_carga` int(11) DEFAULT NULL,
  `ib` enum('VIRO','NO VIRO','SI VIRO','REACCIONO','NO REACCIONO') DEFAULT NULL,
  `operador_id` bigint(20) UNSIGNED NOT NULL,
  `registrado_por_id` bigint(20) UNSIGNED DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area_verde`
--

CREATE TABLE `area_verde` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proceso_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `hora_entrega` time DEFAULT NULL,
  `entrega_id` bigint(20) UNSIGNED NOT NULL,
  `recibe_id` bigint(20) UNSIGNED NOT NULL,
  `descarga_id` bigint(20) UNSIGNED NOT NULL,
  `registrado_por_id` bigint(20) UNSIGNED DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditorias`
--

CREATE TABLE `auditorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `usuario_id` bigint(20) UNSIGNED DEFAULT NULL,
  `responsable_id` bigint(20) UNSIGNED DEFAULT NULL,
  `accion` varchar(80) NOT NULL,
  `entidad` varchar(80) DEFAULT NULL,
  `entidad_id` bigint(20) UNSIGNED DEFAULT NULL,
  `proceso_id` bigint(20) UNSIGNED DEFAULT NULL,
  `area` varchar(20) NOT NULL DEFAULT 'SISTEMA',
  `descripcion` text NOT NULL,
  `datos_adicionales` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`datos_adicionales`)),
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditorias`
--

INSERT INTO `auditorias` (`id`, `usuario_id`, `responsable_id`, `accion`, `entidad`, `entidad_id`, `proceso_id`, `area`, `descripcion`, `datos_adicionales`, `created_at`) VALUES
(9, 10, NULL, 'PROCESO_CREADO', 'proceso_esterilizacion', 1, 1, 'ROJA', 'Proceso #1 iniciado en Area Roja con 16 piezas.', '{\"material_id\":\"12\",\"total_piezas\":16}', '2026-10-16 00:35:59'),
(10, 10, 2, 'MATERIAL_RECIBIDO', 'proceso_esterilizacion', 1, 1, 'ROJA', 'Material recibido para el proceso #1.', '{\"total_piezas\":16}', '2026-10-16 00:35:59'),
(11, 10, 3, 'MATERIAL_ENTREGADO', 'proceso_esterilizacion', 1, 1, 'ROJA', 'Material entregado al proceso #1.', NULL, '2026-10-16 00:35:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `instrumentos`
--

CREATE TABLE `instrumentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(60) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `instrumentos`
--

INSERT INTO `instrumentos` (`id`, `nombre`, `codigo`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Pinza Mosquito Curva', '15-0121', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(2, 'Pinza Mosquito Recta', '15-0120', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(3, 'Pinza Diseccion C/U', '14-0623', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(4, 'Pinza Diseccion S/U', '14-0123', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(5, 'Porta Aguja Mayo Hegar', '18.1080', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(6, 'Tijera Metzembaum Curva', '13-0601', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(7, 'Tijera Mayo Recta', '11-170-14-07', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(8, 'Sonda Acanalada', '19-0322', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(9, 'Mango Bisturi # 3', '12-0103', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(10, 'Mango Bisturi # 4', '12-0104', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(11, 'Estilete', 'CA-018-18', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(12, 'Separador de Farabeuf', '17-0600', 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(13, 'Cubeta de Acero Qx con Tapa', NULL, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `servicio_id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materiales`
--

INSERT INTO `materiales` (`id`, `servicio_id`, `nombre`, `activo`, `creado_en`) VALUES
(1, 1, 'CIRUGIA MAYOR', 1, '2026-10-11 14:26:57'),
(2, 1, 'PAQUETES DE GASA 10X10', 1, '2026-10-11 14:26:57'),
(3, 1, 'MANDILONES', 1, '2026-10-11 14:26:57'),
(4, 2, 'EQUIPO DE PARTO', 1, '2026-10-11 14:26:57'),
(5, 2, 'PINZA ROMPE BOLSA', 1, '2026-10-11 14:26:57'),
(6, 2, 'PINZA FOERSTER CURVA', 1, '2026-10-11 14:26:57'),
(7, 2, 'TIJERA DE EPISIOTOMIA CURVA', 1, '2026-10-11 14:26:57'),
(8, 3, 'EQUIPO DE CURACION', 1, '2026-10-11 14:26:57'),
(9, 4, 'FCO DE ASPIRACION', 1, '2026-10-11 14:26:57'),
(10, 4, 'CONEXIONES', 1, '2026-10-11 14:26:57'),
(11, 5, 'RINONERA', 1, '2026-10-11 14:26:57'),
(12, 1, 'CIRUGIA MENOR I', 1, '2026-10-14 16:02:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material_instrumentos`
--

CREATE TABLE `material_instrumentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `material_id` bigint(20) UNSIGNED NOT NULL,
  `instrumento_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_predeterminada` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `material_instrumentos`
--

INSERT INTO `material_instrumentos` (`id`, `material_id`, `instrumento_id`, `cantidad_predeterminada`, `created_at`, `updated_at`) VALUES
(1, 12, 1, 3, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(2, 12, 2, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(3, 12, 3, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(4, 12, 4, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(5, 12, 5, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(6, 12, 6, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(7, 12, 7, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(8, 12, 8, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(9, 12, 9, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(10, 12, 10, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(11, 12, 11, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(12, 12, 12, 2, '2026-10-14 16:02:53', '2026-10-14 16:02:53'),
(13, 12, 13, 1, '2026-10-14 16:02:53', '2026-10-14 16:02:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_10_11_000001_create_catalogos_esterilizacion_tables', 1),
(2, '2026_10_11_000002_create_proceso_esterilizacion_table', 1),
(3, '2026_10_11_000003_create_area_etapas_tables', 1),
(4, '2026_10_14_000001_add_auth_fields_to_usuarios_table', 2),
(5, '2026_10_14_000002_create_password_reset_tokens_table', 2),
(6, '2026_10_14_000003_create_sessions_table', 2),
(7, '2026_10_14_000004_add_creado_por_to_proceso_esterilizacion_table', 2),
(8, '2026_10_14_000005_create_auditorias_table', 2),
(9, '2026_10_14_000006_create_instrumentos_table', 2),
(10, '2026_10_14_000007_create_material_instrumentos_table', 2),
(11, '2026_10_14_000008_create_proceso_instrumentos_table', 2),
(12, '2026_10_15_000001_add_responsable_id_to_auditorias_table', 3),
(13, '2026_10_15_000002_add_registrado_por_id_to_area_etapas_tables', 3),
(14, '2026_10_15_000003_convertir_empaque_e_ib_a_enum', 4),
(15, '2026_10_15_000004_convertir_campos_fijos_a_enum', 5),
(16, '2026_10_15_000005_cambiar_hora_descarga_por_descarga_id', 6),
(17, '2026_10_15_000006_convertir_equipo_biomedico_a_enum', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `equipo_biomedico` enum('AUTOCLAVE') NOT NULL,
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
(1, 1, '2026-10-16', 'DIURNO', '19:35:00', 2, 3, 12, 16, 'ULTRASONICA', 'AUTOCLAVE', 3, 10, 'ROJA', '2026-10-16 00:35:59', '2026-10-16 00:35:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proceso_instrumentos`
--

CREATE TABLE `proceso_instrumentos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `proceso_id` bigint(20) UNSIGNED NOT NULL,
  `instrumento_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre_instrumento` varchar(150) NOT NULL,
  `codigo_instrumento` varchar(60) DEFAULT NULL,
  `cantidad_predeterminada` int(11) NOT NULL,
  `cantidad_real` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proceso_instrumentos`
--

INSERT INTO `proceso_instrumentos` (`id`, `proceso_id`, `instrumento_id`, `nombre_instrumento`, `codigo_instrumento`, `cantidad_predeterminada`, `cantidad_real`, `created_at`, `updated_at`) VALUES
(14, 1, 13, 'Cubeta de Acero Qx con Tapa', NULL, 1, 1, NULL, NULL),
(15, 1, 11, 'Estilete', 'CA-018-18', 1, 1, NULL, NULL),
(16, 1, 9, 'Mango Bisturi # 3', '12-0103', 1, 1, NULL, NULL),
(17, 1, 10, 'Mango Bisturi # 4', '12-0104', 1, 1, NULL, NULL),
(18, 1, 3, 'Pinza Diseccion C/U', '14-0623', 1, 1, NULL, NULL),
(19, 1, 4, 'Pinza Diseccion S/U', '14-0123', 1, 1, NULL, NULL),
(20, 1, 1, 'Pinza Mosquito Curva', '15-0121', 3, 3, NULL, NULL),
(21, 1, 2, 'Pinza Mosquito Recta', '15-0120', 1, 1, NULL, NULL),
(22, 1, 5, 'Porta Aguja Mayo Hegar', '18.1080', 1, 1, NULL, NULL),
(23, 1, 12, 'Separador de Farabeuf', '17-0600', 2, 2, NULL, NULL),
(24, 1, 8, 'Sonda Acanalada', '19-0322', 1, 1, NULL, NULL),
(25, 1, 7, 'Tijera Mayo Recta', '11-170-14-07', 1, 1, NULL, NULL),
(26, 1, 6, 'Tijera Metzembaum Curva', '13-0601', 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `activo`, `creado_en`) VALUES
(1, 'SOP', 1, '2026-10-11 14:26:57'),
(2, 'CENTRO OBSTETRICO', 1, '2026-10-11 14:26:57'),
(3, 'NEONATOLOGIA', 1, '2026-10-11 14:26:57'),
(4, 'HOSPITALIZACION', 1, '2026-10-11 14:26:57'),
(5, 'GINECO EMERGENCIA', 1, '2026-10-11 14:26:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` text NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'OPERADOR',
  `actualizado_en` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `apellidos`, `email`, `password`, `remember_token`, `rol`, `actualizado_en`, `activo`, `creado_en`) VALUES
(1, 'Zoila', 'Fonseca', NULL, NULL, NULL, 'OPERADOR', NULL, 1, '2026-10-11 14:26:57'),
(2, 'Elizabeth', 'Aguirre', NULL, NULL, NULL, 'OPERADOR', NULL, 1, '2026-10-11 14:26:57'),
(3, 'Maria', 'Rodriguez', NULL, NULL, NULL, 'OPERADOR', NULL, 1, '2026-10-11 14:26:57'),
(4, 'Carlos', 'Mendoza', NULL, NULL, NULL, 'OPERADOR', NULL, 1, '2026-10-11 14:26:57'),
(5, 'Ana', 'Torres', NULL, NULL, NULL, 'OPERADOR', NULL, 1, '2026-10-11 14:26:57'),
(10, 'Administrador', 'del Sistema', 'admin@esterilizacion.test', '$2y$12$24fptiNQmwChyTxZynYIMeyIvqZhreJCmtYvKwuYW7N58DRjCHXRK', 'zU5QwV3TIKbYGErOrWXK8MV4VlawaC1xVf9ZwxaJxemWNOy6VgF7cNoD1Djt', 'ADMIN', NULL, 1, '2026-10-14 11:36:55');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `area_azul`
--
ALTER TABLE `area_azul`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `area_azul_proceso_id_unique` (`proceso_id`),
  ADD KEY `area_azul_operador_id_foreign` (`operador_id`),
  ADD KEY `area_azul_registrado_por_id_index` (`registrado_por_id`);

--
-- Indices de la tabla `area_verde`
--
ALTER TABLE `area_verde`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `area_verde_proceso_id_unique` (`proceso_id`),
  ADD KEY `area_verde_entrega_id_foreign` (`entrega_id`),
  ADD KEY `area_verde_recibe_id_foreign` (`recibe_id`),
  ADD KEY `area_verde_registrado_por_id_index` (`registrado_por_id`),
  ADD KEY `area_verde_descarga_id_foreign` (`descarga_id`);

--
-- Indices de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auditorias_usuario_id_index` (`usuario_id`),
  ADD KEY `auditorias_proceso_id_index` (`proceso_id`),
  ADD KEY `auditorias_accion_index` (`accion`),
  ADD KEY `auditorias_area_index` (`area`),
  ADD KEY `auditorias_created_at_index` (`created_at`),
  ADD KEY `auditorias_responsable_id_foreign` (`responsable_id`),
  ADD KEY `auditorias_usuario_fecha_idx` (`usuario_id`,`created_at`),
  ADD KEY `auditorias_accion_fecha_idx` (`accion`,`created_at`),
  ADD KEY `auditorias_area_fecha_idx` (`area`,`created_at`);

--
-- Indices de la tabla `instrumentos`
--
ALTER TABLE `instrumentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `instrumentos_codigo_unique` (`codigo`),
  ADD KEY `instrumentos_nombre_index` (`nombre`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `materiales_servicio_id_nombre_unique` (`servicio_id`,`nombre`);

--
-- Indices de la tabla `material_instrumentos`
--
ALTER TABLE `material_instrumentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_instrumentos_material_id_instrumento_id_unique` (`material_id`,`instrumento_id`),
  ADD KEY `material_instrumentos_instrumento_id_foreign` (`instrumento_id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

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
-- Indices de la tabla `proceso_instrumentos`
--
ALTER TABLE `proceso_instrumentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proceso_instrumentos_instrumento_id_foreign` (`instrumento_id`),
  ADD KEY `proceso_instrumentos_proceso_id_index` (`proceso_id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `servicios_nombre_unique` (`nombre`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuarios_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `area_azul`
--
ALTER TABLE `area_azul`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `area_verde`
--
ALTER TABLE `area_verde`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditorias`
--
ALTER TABLE `auditorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `instrumentos`
--
ALTER TABLE `instrumentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `material_instrumentos`
--
ALTER TABLE `material_instrumentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `proceso_esterilizacion`
--
ALTER TABLE `proceso_esterilizacion`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proceso_instrumentos`
--
ALTER TABLE `proceso_instrumentos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `area_azul`
--
ALTER TABLE `area_azul`
  ADD CONSTRAINT `area_azul_operador_id_foreign` FOREIGN KEY (`operador_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `area_azul_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `proceso_esterilizacion` (`id`),
  ADD CONSTRAINT `area_azul_registrado_por_id_foreign` FOREIGN KEY (`registrado_por_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `area_verde`
--
ALTER TABLE `area_verde`
  ADD CONSTRAINT `area_verde_descarga_id_foreign` FOREIGN KEY (`descarga_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `area_verde_entrega_id_foreign` FOREIGN KEY (`entrega_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `area_verde_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `proceso_esterilizacion` (`id`),
  ADD CONSTRAINT `area_verde_recibe_id_foreign` FOREIGN KEY (`recibe_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `area_verde_registrado_por_id_foreign` FOREIGN KEY (`registrado_por_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `auditorias`
--
ALTER TABLE `auditorias`
  ADD CONSTRAINT `auditorias_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `proceso_esterilizacion` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `auditorias_responsable_id_foreign` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `auditorias_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `materiales_servicio_id_foreign` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`);

--
-- Filtros para la tabla `material_instrumentos`
--
ALTER TABLE `material_instrumentos`
  ADD CONSTRAINT `material_instrumentos_instrumento_id_foreign` FOREIGN KEY (`instrumento_id`) REFERENCES `instrumentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_instrumentos_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE CASCADE;

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

--
-- Filtros para la tabla `proceso_instrumentos`
--
ALTER TABLE `proceso_instrumentos`
  ADD CONSTRAINT `proceso_instrumentos_instrumento_id_foreign` FOREIGN KEY (`instrumento_id`) REFERENCES `instrumentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `proceso_instrumentos_proceso_id_foreign` FOREIGN KEY (`proceso_id`) REFERENCES `proceso_esterilizacion` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
