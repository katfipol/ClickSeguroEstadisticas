-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2026 a las 07:55:38
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
-- Base de datos: `click_seguro`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `analisis`
--

CREATE TABLE `analisis` (
  `id_analisis` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_url` bigint(20) UNSIGNED NOT NULL,
  `id_url_final` bigint(20) UNSIGNED DEFAULT NULL,
  `id_tipo_riesgo` bigint(20) UNSIGNED DEFAULT NULL,
  `id_analisis_anterior` bigint(20) UNSIGNED DEFAULT NULL,
  `id_documento` bigint(20) UNSIGNED DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `error` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `analisis`
--

INSERT INTO `analisis` (`id_analisis`, `id_usuario`, `id_url`, `id_url_final`, `id_tipo_riesgo`, `id_analisis_anterior`, `id_documento`, `estado`, `error`, `created_at`) VALUES
(1, 1, 1, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-01 08:33:45'),
(2, 1, 2, NULL, 3, NULL, NULL, 'completado', NULL, '2026-10-01 08:34:30'),
(3, 1, 2, NULL, 3, 2, NULL, 'completado', NULL, '2026-10-01 08:35:55'),
(4, 2, 2, NULL, 3, NULL, NULL, 'completado', NULL, '2026-10-06 08:22:19'),
(5, 2, 3, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-06 08:25:22'),
(7, 3, 4, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-16 05:00:23'),
(8, 3, 4, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-16 05:01:02'),
(9, 3, 4, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-16 05:01:33'),
(10, 3, 5, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-16 05:02:12'),
(11, 3, 4, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-16 05:02:49'),
(12, 3, 6, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-16 05:50:28'),
(14, 3, 7, NULL, 2, NULL, NULL, 'completado', NULL, '2026-10-17 00:36:36'),
(16, 3, 8, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-17 00:59:11'),
(17, 3, 9, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-22 07:13:41'),
(18, 4, 7, NULL, 2, NULL, NULL, 'completado', NULL, '2026-10-22 09:00:44'),
(19, 4, 11, NULL, 1, NULL, NULL, 'completado', NULL, '2026-10-22 09:03:46'),
(20, 4, 12, NULL, 1, NULL, 1, 'completado', NULL, '2026-10-22 09:14:59'),
(21, 4, 13, NULL, 3, NULL, 1, 'completado', NULL, '2026-10-22 09:14:59'),
(22, 4, 14, NULL, 1, NULL, 1, 'completado', NULL, '2026-10-22 09:14:59'),
(23, 4, 15, NULL, 2, NULL, 1, 'completado', NULL, '2026-10-22 09:14:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivo_verificado`
--

CREATE TABLE `archivo_verificado` (
  `id_archivo` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tamano` int(10) UNSIGNED NOT NULL,
  `sha256` char(64) NOT NULL,
  `id_tipo_riesgo` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `archivo_verificado`
--

INSERT INTO `archivo_verificado` (`id_archivo`, `id_usuario`, `nombre`, `tamano`, `sha256`, `id_tipo_riesgo`, `created_at`) VALUES
(1, 3, 'REVISTA+CIENTIFICA+VOL5+N7+WEB-155-172.pdf', 184802, '70c618717a2f776d1ec3af17228483fe0861b6dbe326f77aeca0622084bbf202', NULL, '2026-10-17 00:43:25'),
(2, 3, 'REVISTA+CIENTIFICA+VOL5+N7+WEB-155-172.pdf', 184802, '70c618717a2f776d1ec3af17228483fe0861b6dbe326f77aeca0622084bbf202', NULL, '2026-10-17 01:47:46'),
(3, 3, 'REVISTA+CIENTIFICA+VOL5+N7+WEB-155-172.pdf', 184802, '70c618717a2f776d1ec3af17228483fe0861b6dbe326f77aeca0622084bbf202', NULL, '2026-10-17 03:42:00'),
(4, 4, 'REVISTA+CIENTIFICA+VOL5+N7+WEB-155-172.pdf', 184802, '70c618717a2f776d1ec3af17228483fe0861b6dbe326f77aeca0622084bbf202', NULL, '2026-10-22 09:26:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_analisis`
--

CREATE TABLE `detalle_analisis` (
  `id_detalle` bigint(20) UNSIGNED NOT NULL,
  `id_analisis` bigint(20) UNSIGNED NOT NULL,
  `id_fuente` bigint(20) UNSIGNED NOT NULL,
  `motor` varchar(100) DEFAULT NULL,
  `resultado` varchar(20) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_analisis`
--

INSERT INTO `detalle_analisis` (`id_detalle`, `id_analisis`, `id_fuente`, `motor`, `resultado`, `categoria`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(2, 2, 1, 'Fortinet', 'malicioso', 'phishing', 'Fortinet la marcó como phishing', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(3, 2, 1, 'G-Data', 'malicioso', 'phishing', 'G-Data la marcó como phishing', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(4, 2, 1, 'BitDefender', 'malicioso', 'phishing', 'BitDefender la marcó como phishing', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(5, 7, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-16 05:00:27', '2026-10-16 05:00:27'),
(6, 8, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-16 05:01:32', '2026-10-16 05:01:32'),
(7, 9, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-16 05:02:02', '2026-10-16 05:02:02'),
(8, 10, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-16 05:02:27', '2026-10-16 05:02:27'),
(9, 11, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-16 05:02:56', '2026-10-16 05:02:56'),
(10, 12, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-16 05:50:32', '2026-10-16 05:50:32'),
(12, 14, 1, 'ArcSight Threat Intelligence', 'sospechoso', 'suspicious', 'ArcSight Threat Intelligence la marcó como suspicious', '2026-10-17 00:36:39', '2026-10-17 00:36:39'),
(14, 16, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-17 00:59:27', '2026-10-17 00:59:27'),
(15, 17, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-22 07:13:55', '2026-10-22 07:13:55'),
(16, 18, 1, 'ArcSight Threat Intelligence', 'sospechoso', 'suspicious', 'ArcSight Threat Intelligence la marcó como suspicious', '2026-10-22 09:00:45', '2026-10-22 09:00:45'),
(17, 19, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-22 09:03:47', '2026-10-22 09:03:47'),
(18, 20, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-22 09:15:15', '2026-10-22 09:15:15'),
(19, 21, 1, 'ADMINUSLabs', 'malicioso', 'malicious', 'ADMINUSLabs la marcó como malicious', '2026-10-22 09:15:16', '2026-10-22 09:15:16'),
(20, 21, 1, 'alphaMountain.ai', 'sospechoso', 'suspicious', 'alphaMountain.ai la marcó como suspicious', '2026-10-22 09:15:16', '2026-10-22 09:15:16'),
(21, 21, 1, 'Chong Lua Dao', 'malicioso', 'malicious', 'Chong Lua Dao la marcó como malicious', '2026-10-22 09:15:16', '2026-10-22 09:15:16'),
(22, 21, 1, 'Fortinet', 'malicioso', 'phishing', 'Fortinet la marcó como phishing', '2026-10-22 09:15:16', '2026-10-22 09:15:16'),
(23, 21, 1, 'Gridinsoft', 'sospechoso', 'suspicious', 'Gridinsoft la marcó como suspicious', '2026-10-22 09:15:16', '2026-10-22 09:15:16'),
(24, 22, 1, NULL, 'limpio', NULL, 'Ningún motor marcó la URL como riesgosa.', '2026-10-22 09:15:47', '2026-10-22 09:15:47'),
(25, 23, 1, 'Chong Lua Dao', 'malicioso', 'malicious', 'Chong Lua Dao la marcó como malicious', '2026-10-22 09:16:06', '2026-10-22 09:16:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_archivo`
--

CREATE TABLE `detalle_archivo` (
  `id_detalle_archivo` bigint(20) UNSIGNED NOT NULL,
  `id_archivo` bigint(20) UNSIGNED NOT NULL,
  `id_fuente` bigint(20) UNSIGNED NOT NULL,
  `motor` varchar(100) DEFAULT NULL,
  `resultado` varchar(20) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento`
--

CREATE TABLE `documento` (
  `id_documento` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `total_enlaces` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `enlaces_omitidos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documento`
--

INSERT INTO `documento` (`id_documento`, `id_usuario`, `nombre`, `total_enlaces`, `enlaces_omitidos`, `created_at`, `updated_at`) VALUES
(1, 4, 'enlaces.txt', 4, 0, '2026-10-22 09:14:59', '2026-10-22 09:14:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dominio`
--

CREATE TABLE `dominio` (
  `id_dominio` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `fecha_creacion` date DEFAULT NULL,
  `registrador` varchar(150) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `pais` varchar(2) DEFAULT NULL,
  `reputacion` int(11) DEFAULT NULL,
  `consultado_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dominio`
--

INSERT INTO `dominio` (`id_dominio`, `nombre`, `fecha_creacion`, `registrador`, `ip`, `pais`, `reputacion`, `consultado_at`, `created_at`, `updated_at`) VALUES
(1, 'example.com', '1995-08-14', 'IANA', '93.184.216.34', 'US', 100, '2026-10-14 21:31:48', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(2, 'wvwv-apple.services', '2023-10-01', 'NameCheap Inc.', '192.168.100.50', 'RU', 10, '2026-10-14 21:31:48', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(3, 'teams.cloud.microsoft', '2020-05-12', 'Microsoft Corp.', '20.190.150.10', 'US', 95, '2026-10-14 21:31:48', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(4, 'www.canva.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-15 01:59:15', '2026-10-15 01:59:15'),
(5, 'www.google.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-16 05:02:12', '2026-10-16 05:02:12'),
(6, 'www.facebook.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-16 05:50:28', '2026-10-16 05:50:28'),
(7, 'www.ebay.com', '1995-08-04', 'MarkMonitor Inc.', '190.98.160.137', 'AR', 0, '2026-10-17 00:36:57', '2026-10-17 00:36:36', '2026-10-17 00:36:57'),
(8, 'www.juezvirtual.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-17 00:59:11', '2026-10-17 00:59:11'),
(9, 'convertio.co', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 07:13:41', '2026-10-22 07:13:41'),
(10, 'google.com', '1997-09-15', 'MarkMonitor Inc.', '108.177.123.113', 'US', 732, '2026-10-22 07:14:37', '2026-10-22 07:14:36', '2026-10-22 07:14:37'),
(11, 'sitio-falso-prueba.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 07:15:58', '2026-10-22 07:15:58'),
(12, 'www.lasexta.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 09:03:46', '2026-10-22 09:03:46'),
(13, 'www.airbnb.com.co', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 09:14:59', '2026-10-22 09:14:59'),
(14, '4kdownload.to', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 09:14:59', '2026-10-22 09:14:59'),
(15, 'robotuno.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 09:14:59', '2026-10-22 09:14:59'),
(16, 'www.bandlab.com', NULL, NULL, NULL, NULL, NULL, NULL, '2026-10-22 09:14:59', '2026-10-22 09:14:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fuente_verificacion`
--

CREATE TABLE `fuente_verificacion` (
  `id_fuente` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fuente_verificacion`
--

INSERT INTO `fuente_verificacion` (`id_fuente`, `nombre`, `created_at`, `updated_at`) VALUES
(1, 'VirusTotal', '2026-10-14 21:31:48', '2026-10-14 21:31:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"e6cfccaa-f969-4eed-bae7-e1f5c2385ff7\",\"displayName\":\"App\\\\Jobs\\\\ProcesarAnalisis\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":4,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":120,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\ProcesarAnalisis\",\"command\":\"O:25:\\\"App\\\\Jobs\\\\ProcesarAnalisis\\\":1:{s:10:\\\"analisisId\\\";i:6;}\"}}', 0, NULL, 1792015155, 1792015155);

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
(1, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Usuario', 3, 'click-seguro-api', 'f1564395d41cdb65871eb4ace1eab694f9fe48bc62ca387a6364808eac48b25d', '[\"*\"]', '2026-10-15 02:11:55', '2026-10-15 01:58:47', '2026-10-15 02:11:55'),
(3, 'App\\Models\\Usuario', 3, 'click-seguro-api', '3ca66b96f68622ec5ae95af9d0514d02a3646fee6cba3accbe2b1db1af19bb99', '[\"*\"]', NULL, '2026-10-16 05:00:12', '2026-10-16 05:00:12'),
(5, 'App\\Models\\Usuario', 3, 'click-seguro-api', '6711076da11254635a25d0e890aecdef2abf31f233f26af5d2dfba09ea35528a', '[\"*\"]', NULL, '2026-10-16 05:37:33', '2026-10-16 05:37:33'),
(6, 'App\\Models\\Usuario', 3, 'click-seguro-api', '47acf8ffb08030fad3d87ca755a79da8d68f115294a6b954ff06ed4e9f3bb358', '[\"*\"]', NULL, '2026-10-16 05:37:34', '2026-10-16 05:37:34'),
(7, 'App\\Models\\Usuario', 3, 'click-seguro-api', 'a2d9500b2d7e47f4ac30d67ea102ccd9b13de12c91231007ab9275100b9e30ad', '[\"*\"]', NULL, '2026-10-16 05:37:34', '2026-10-16 05:37:34'),
(8, 'App\\Models\\Usuario', 3, 'click-seguro-api', 'd52bc563fc55ffe821432a0e77f5e1fd822e31b0a5e308b62bb1f972f879a74a', '[\"*\"]', '2026-10-16 06:20:27', '2026-10-16 05:48:24', '2026-10-16 06:20:27'),
(10, 'App\\Models\\Usuario', 3, 'click-seguro-api', '8a0dd8f9c8ee4369fc2200160d82fe5731cd47408efec93ecd7585763eae32c4', '[\"*\"]', NULL, '2026-10-17 00:30:03', '2026-10-17 00:30:03'),
(16, 'App\\Models\\Usuario', 4, 'click-seguro-api', '01663f046e0143d2e813105d2b561822881e0ed9b6898b9170f8ab8a8d9f3e90', '[\"*\"]', '2026-10-22 09:26:12', '2026-10-22 08:59:20', '2026-10-22 09:26:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte`
--

CREATE TABLE `reporte` (
  `id_reporte` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `id_url` bigint(20) UNSIGNED NOT NULL,
  `motivo` varchar(500) DEFAULT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reporte`
--

INSERT INTO `reporte` (`id_reporte`, `id_usuario`, `id_url`, `motivo`, `estado`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'Es segura, falso positivo', 'pendiente', '2026-10-06 08:25:45', '2026-10-06 08:25:45'),
(2, 3, 10, 'xxxxxxxx', 'pendiente', '2026-10-22 07:15:58', '2026-10-22 07:15:58'),
(3, 4, 10, NULL, 'pendiente', '2026-10-22 09:24:15', '2026-10-22 09:24:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_riesgo`
--

CREATE TABLE `tipo_riesgo` (
  `id_tipo_riesgo` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_riesgo`
--

INSERT INTO `tipo_riesgo` (`id_tipo_riesgo`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'Seguro', 'Ningún motor de seguridad marcó esta URL como riesgosa.', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(2, 'Sospechoso', 'Pocos motores la marcaron. Evita ingresar datos personales.', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(3, 'Peligroso', 'Varios motores la marcaron como maliciosa. No la abras.', '2026-10-14 21:31:48', '2026-10-14 21:31:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `url`
--

CREATE TABLE `url` (
  `id_url` bigint(20) UNSIGNED NOT NULL,
  `id_dominio` bigint(20) UNSIGNED NOT NULL,
  `url` varchar(2048) NOT NULL,
  `url_hash` char(64) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `url`
--

INSERT INTO `url` (`id_url`, `id_dominio`, `url`, `url_hash`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://example.com/', '0f115db062b7c0dd030b16878c99dea5c354b49dc37b38eb8846179c7783e9d7', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(2, 2, 'https://wvwv-apple.services/dz6', 'b2d96002854079306daa30c8e4a8d87674869f6cfb0e542e26af1331183f5ff7', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(3, 3, 'https://teams.cloud.microsoft/', '81b3821ad2cc86458339959f00e2811664b381ede491afa0c2e5bf81cbd14185', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(4, 4, 'https://www.canva.com/design/DAHX_8tM2Cc/-SzYZDO7nk1HTHs6Vbsa0g/edit', '29f35e0992608d7bc46f41b8e09a0e1909849d255658536c3b6ea0d948538eba', '2026-10-15 01:59:15', '2026-10-15 01:59:15'),
(5, 5, 'https://www.google.com/search?client=opera-gx&q=ver+en+que+familia+de+gogle+estoy&sourceid=opera&ie=UTF-8&oe=UTF-8', 'cb52f1f5dc86732dc7a12eb55b37064728efc6ca04a215212c4b0aad0786bccf', '2026-10-16 05:02:12', '2026-10-16 05:02:12'),
(6, 6, 'https://www.facebook.com/testconnectbelgique', '6efe67d931cea351f43a3ec99d7ba47d7bcfc431947980cb9d8f1b24f245e33b', '2026-10-16 05:50:28', '2026-10-16 05:50:28'),
(7, 7, 'https://www.ebay.com/?mkcid=1&toolid=10001&mkevt=1&mkrid=711-53200-19255-0&campid=5336846384&customid=gx-latam-ebay-ebay-sd-def', '223006737226a3a273391817afdbe63ff11a5677f39ecca420cd81c85015b124', '2026-10-17 00:36:36', '2026-10-17 00:36:36'),
(8, 8, 'https://www.juezvirtual.com/*', '9d9352985482c11e1a1a15e996c8fd4d8337e20e693a316ba255550b900c62b4', '2026-10-17 00:59:11', '2026-10-17 00:59:11'),
(9, 9, 'https://convertio.co/es/download/cb622e5d7877700ec5dbab912e89288f60750a/', '1c7eb35f717efba88fff76dcd77f303f9c5ae5c945e6faa38e4877120c86c98b', '2026-10-22 07:13:41', '2026-10-22 07:13:41'),
(10, 11, 'http://sitio-falso-prueba.com/', '1e8379fec123040085552eaa036f2357515c3ee766dbf7b2a2343cb682a23ffc', '2026-10-22 07:15:58', '2026-10-22 07:15:58'),
(11, 12, 'https://www.lasexta.com/tecnologia-tecnoxplora/', '77d776ae594886dd6f1fc689068d9a8dc314e1dd4689bb1e51df7271c980545c', '2026-10-22 09:03:46', '2026-10-22 09:03:46'),
(12, 13, 'https://www.airbnb.com.co/a/discover?irclid=Xrd2L430CxyZW5uwC2xuzTIBUkr1-Mzm9xZCwo0&irgwc=1&afsrc=1&ircid=53443&sharedid=latam-desktop-gx&af=&iratid=9627&c=.pi73.pk53443_1943907&irparam1=', 'b9499b35b6510f9ebb81333e107f86cb542a966b75d039c19bcba523b3ac5688', '2026-10-22 09:14:59', '2026-10-22 09:14:59'),
(13, 14, 'https://4kdownload.to/enfY/', '984e5a6b9007a1ff4cb2209b76590f907b3c116a46d7245515b723c5b9da32b1', '2026-10-22 09:14:59', '2026-10-22 09:14:59'),
(14, 15, 'https://robotuno.com/minijuego-con-arduino-simon-dice/', '2e91532406ec1e8e9942f3eacc7a89baae24c6fc569b779b07d163efb8f01cb0', '2026-10-22 09:14:59', '2026-10-22 09:14:59'),
(15, 16, 'https://www.bandlab.com/studio?id=fafd8896-4dd7-4c25-9847-32b3d465efc9', 'd0cffab6015550f011a8b155a3a16a81f94aceae136cb3eb5be850b3049f011f', '2026-10-22 09:14:59', '2026-10-22 09:14:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) DEFAULT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido_paterno`, `apellido_materno`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Martin', 'Leaño', 'Mercado', 'martin@gmail.com', '$2y$10$wQ1MD6S3/IqMLmL09CUwlOGleXwku4OawJyarU7wux1KGb4MDxyH2', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(2, 'Jose', 'Leaño', 'Mercado', 'jose@gmail.com', '$2y$10$StWaUyH0ZoetOjNSJxKPzuxidc3/t8OlWjHU/v4zoeCtNXo8psjY6', '2026-10-14 21:31:48', '2026-10-14 21:31:48'),
(3, 'Luis', 'bustillos', 'serrano', 'luis@gmail.com', '$2y$10$uokICXOIBwnTMKrcOwavEeKuPZ.fckX8uGa8vNv6VAM3kRSPpFywG', '2026-10-15 01:53:18', '2026-10-15 01:53:18'),
(4, 'lia', 'Gonzales', 'Lopez', 'lia@gmail.com', '$2y$10$czWgO3wSzzAys7o2DLTdv.Jo2aBseLPSIcn1nIiN4eMffDZqm5n3C', '2026-10-22 08:58:35', '2026-10-22 08:58:35');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `analisis`
--
ALTER TABLE `analisis`
  ADD PRIMARY KEY (`id_analisis`),
  ADD KEY `analisis_id_usuario_created_at_index` (`id_usuario`,`created_at`),
  ADD KEY `analisis_id_usuario_id_tipo_riesgo_index` (`id_usuario`,`id_tipo_riesgo`),
  ADD KEY `analisis_id_url_foreign` (`id_url`),
  ADD KEY `analisis_id_url_final_foreign` (`id_url_final`),
  ADD KEY `analisis_id_tipo_riesgo_foreign` (`id_tipo_riesgo`),
  ADD KEY `analisis_id_analisis_anterior_foreign` (`id_analisis_anterior`),
  ADD KEY `analisis_id_documento_foreign` (`id_documento`);

--
-- Indices de la tabla `archivo_verificado`
--
ALTER TABLE `archivo_verificado`
  ADD PRIMARY KEY (`id_archivo`),
  ADD KEY `archivo_verificado_sha256_index` (`sha256`),
  ADD KEY `archivo_verificado_id_usuario_created_at_index` (`id_usuario`,`created_at`),
  ADD KEY `archivo_verificado_id_tipo_riesgo_foreign` (`id_tipo_riesgo`);

--
-- Indices de la tabla `detalle_analisis`
--
ALTER TABLE `detalle_analisis`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `detalle_analisis_id_analisis_foreign` (`id_analisis`),
  ADD KEY `detalle_analisis_id_fuente_foreign` (`id_fuente`);

--
-- Indices de la tabla `detalle_archivo`
--
ALTER TABLE `detalle_archivo`
  ADD PRIMARY KEY (`id_detalle_archivo`),
  ADD KEY `detalle_archivo_id_archivo_foreign` (`id_archivo`),
  ADD KEY `detalle_archivo_id_fuente_foreign` (`id_fuente`);

--
-- Indices de la tabla `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`id_documento`),
  ADD KEY `documento_id_usuario_foreign` (`id_usuario`);

--
-- Indices de la tabla `dominio`
--
ALTER TABLE `dominio`
  ADD PRIMARY KEY (`id_dominio`),
  ADD UNIQUE KEY `dominio_nombre_unique` (`nombre`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `fuente_verificacion`
--
ALTER TABLE `fuente_verificacion`
  ADD PRIMARY KEY (`id_fuente`),
  ADD UNIQUE KEY `fuente_verificacion_nombre_unique` (`nombre`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indices de la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD UNIQUE KEY `reporte_id_usuario_id_url_unique` (`id_usuario`,`id_url`),
  ADD KEY `reporte_id_url_foreign` (`id_url`);

--
-- Indices de la tabla `tipo_riesgo`
--
ALTER TABLE `tipo_riesgo`
  ADD PRIMARY KEY (`id_tipo_riesgo`),
  ADD UNIQUE KEY `tipo_riesgo_nombre_unique` (`nombre`);

--
-- Indices de la tabla `url`
--
ALTER TABLE `url`
  ADD PRIMARY KEY (`id_url`),
  ADD UNIQUE KEY `url_url_hash_unique` (`url_hash`),
  ADD KEY `url_id_dominio_foreign` (`id_dominio`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `usuario_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `analisis`
--
ALTER TABLE `analisis`
  MODIFY `id_analisis` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `archivo_verificado`
--
ALTER TABLE `archivo_verificado`
  MODIFY `id_archivo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_analisis`
--
ALTER TABLE `detalle_analisis`
  MODIFY `id_detalle` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `detalle_archivo`
--
ALTER TABLE `detalle_archivo`
  MODIFY `id_detalle_archivo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documento`
--
ALTER TABLE `documento`
  MODIFY `id_documento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `dominio`
--
ALTER TABLE `dominio`
  MODIFY `id_dominio` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fuente_verificacion`
--
ALTER TABLE `fuente_verificacion`
  MODIFY `id_fuente` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `reporte`
--
ALTER TABLE `reporte`
  MODIFY `id_reporte` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_riesgo`
--
ALTER TABLE `tipo_riesgo`
  MODIFY `id_tipo_riesgo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `url`
--
ALTER TABLE `url`
  MODIFY `id_url` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `analisis`
--
ALTER TABLE `analisis`
  ADD CONSTRAINT `analisis_id_analisis_anterior_foreign` FOREIGN KEY (`id_analisis_anterior`) REFERENCES `analisis` (`id_analisis`) ON DELETE SET NULL,
  ADD CONSTRAINT `analisis_id_documento_foreign` FOREIGN KEY (`id_documento`) REFERENCES `documento` (`id_documento`) ON DELETE SET NULL,
  ADD CONSTRAINT `analisis_id_tipo_riesgo_foreign` FOREIGN KEY (`id_tipo_riesgo`) REFERENCES `tipo_riesgo` (`id_tipo_riesgo`),
  ADD CONSTRAINT `analisis_id_url_final_foreign` FOREIGN KEY (`id_url_final`) REFERENCES `url` (`id_url`),
  ADD CONSTRAINT `analisis_id_url_foreign` FOREIGN KEY (`id_url`) REFERENCES `url` (`id_url`),
  ADD CONSTRAINT `analisis_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `archivo_verificado`
--
ALTER TABLE `archivo_verificado`
  ADD CONSTRAINT `archivo_verificado_id_tipo_riesgo_foreign` FOREIGN KEY (`id_tipo_riesgo`) REFERENCES `tipo_riesgo` (`id_tipo_riesgo`),
  ADD CONSTRAINT `archivo_verificado_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_analisis`
--
ALTER TABLE `detalle_analisis`
  ADD CONSTRAINT `detalle_analisis_id_analisis_foreign` FOREIGN KEY (`id_analisis`) REFERENCES `analisis` (`id_analisis`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_analisis_id_fuente_foreign` FOREIGN KEY (`id_fuente`) REFERENCES `fuente_verificacion` (`id_fuente`);

--
-- Filtros para la tabla `detalle_archivo`
--
ALTER TABLE `detalle_archivo`
  ADD CONSTRAINT `detalle_archivo_id_archivo_foreign` FOREIGN KEY (`id_archivo`) REFERENCES `archivo_verificado` (`id_archivo`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_archivo_id_fuente_foreign` FOREIGN KEY (`id_fuente`) REFERENCES `fuente_verificacion` (`id_fuente`);

--
-- Filtros para la tabla `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `documento_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD CONSTRAINT `reporte_id_url_foreign` FOREIGN KEY (`id_url`) REFERENCES `url` (`id_url`),
  ADD CONSTRAINT `reporte_id_usuario_foreign` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `url`
--
ALTER TABLE `url`
  ADD CONSTRAINT `url_id_dominio_foreign` FOREIGN KEY (`id_dominio`) REFERENCES `dominio` (`id_dominio`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
