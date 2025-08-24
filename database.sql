-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 20-08-2025 a las 05:55:54
-- Versión del servidor: 8.2.0
-- Versión de PHP: 8.3.0

CREATE DATABASE IF NOT EXISTS hr365_db;
USE hr365_db;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hr365_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adelantos`
--

CREATE TABLE `adelantos` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `request_date` date NOT NULL,
  `approval_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('SOLICITADO','APROBADO','RECHAZADO','PAGADO') COLLATE utf8mb4_unicode_ci DEFAULT 'SOLICITADO',
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `adelantos`
--

INSERT INTO `adelantos` (`id`, `personal_id`, `amount`, `request_date`, `approval_date`, `payment_date`, `status`, `reason`, `approved_by`, `created_at`) VALUES
(2, 2, 800.00, '2025-07-29', '2025-08-03', NULL, 'APROBADO', 'Reparación de vehículo', NULL, '2025-08-18 19:13:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `date` date NOT NULL,
  `entry_time` time DEFAULT NULL,
  `exit_time` time DEFAULT NULL,
  `late_minutes` int DEFAULT '0',
  `early_exit_minutes` int DEFAULT '0',
  `status` enum('PRESENTE','AUSENTE','TARDANZA','SALIDA_TEMPRANA','LICENCIA') COLLATE utf8mb4_unicode_ci DEFAULT 'PRESENTE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruc` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_hours` text COLLATE utf8mb4_unicode_ci,
  `policies` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id`, `name`, `ruc`, `address`, `phone`, `email`, `website`, `logo`, `business_hours`, `policies`, `created_at`) VALUES
(1, 'Mi Empresa S.A.', '20123456789', 'Av. Principal 123, Ciudad', '+51 1 234 5678', 'info@miempresa.com', NULL, NULL, NULL, NULL, '2025-08-18 19:06:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extras`
--

CREATE TABLE `extras` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `date` date NOT NULL,
  `hours` decimal(4,2) NOT NULL,
  `rate_type` enum('NORMAL','DOBLE','TRIPLE') COLLATE utf8mb4_unicode_ci DEFAULT 'NORMAL',
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_by` int DEFAULT NULL,
  `status` enum('PENDIENTE','APROBADO','RECHAZADO','PAGADO') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jornadas`
--

CREATE TABLE `jornadas` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `date` date NOT NULL,
  `entry_time` time DEFAULT NULL,
  `exit_time` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `total_hours` decimal(4,2) DEFAULT NULL,
  `status` enum('COMPLETA','INCOMPLETA','AUSENTE','TARDANZA') COLLATE utf8mb4_unicode_ci DEFAULT 'COMPLETA',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `jornadas`
--

INSERT INTO `jornadas` (`id`, `personal_id`, `date`, `entry_time`, `exit_time`, `break_start`, `break_end`, `total_hours`, `status`, `created_at`) VALUES
(4, 4, '2025-08-18', '21:52:00', '22:52:00', '22:55:00', '19:52:00', 4.05, 'COMPLETA', '2025-08-18 21:49:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `period_month` int NOT NULL,
  `period_year` int NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `bonuses` decimal(10,2) DEFAULT '0.00',
  `deductions` decimal(10,2) DEFAULT '0.00',
  `net_salary` decimal(10,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('PENDIENTE','PAGADO','ANULADO') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `personal_id`, `period_month`, `period_year`, `base_salary`, `bonuses`, `deductions`, `net_salary`, `payment_date`, `status`, `created_at`) VALUES
(3, 7, 11, 2025, 3000.00, 0.00, 0.00, 3000.00, NULL, 'PENDIENTE', '2025-08-18 21:50:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `panel_control`
--

CREATE TABLE `panel_control` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `date` date NOT NULL,
  `total_hours` decimal(4,2) DEFAULT '0.00',
  `overtime_hours` decimal(4,2) DEFAULT '0.00',
  `late_count` int DEFAULT '0',
  `absence_count` int DEFAULT '0',
  `vacation_days_used` int DEFAULT '0',
  `vacation_days_remaining` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `total_hours` decimal(4,2) DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_type` enum('PERSONAL','MEDICO','ESTUDIO','OTRO') COLLATE utf8mb4_unicode_ci DEFAULT 'PERSONAL',
  `status` enum('SOLICITADO','APROBADO','RECHAZADO','COMPLETADO') COLLATE utf8mb4_unicode_ci DEFAULT 'SOLICITADO',
  `approved_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `employee_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `hire_date` date NOT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` enum('ACTIVO','INACTIVO','VACACIONES','LICENCIA') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `emergency_contact` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `user_id`, `employee_code`, `first_name`, `last_name`, `dni`, `birth_date`, `hire_date`, `position`, `department`, `salary`, `status`, `phone`, `address`, `emergency_contact`, `emergency_phone`, `created_at`) VALUES
(2, NULL, 'EMP002', 'Ana María', 'Rodríguez Silva', '23456789', '1990-07-22', '2021-03-01', 'Analista de Recursos Humanos', 'RRHH', 3800.00, 'ACTIVO', '+51 999 234 567', 'Jr. Huancavelica 456, Lima', 'Carlos Rodríguez', '+51 999 234 568', '2025-08-18 19:07:14'),
(3, NULL, 'EMP003', 'Luis Alberto', 'Martínez Torres', '34567890', '1988-11-08', '2019-08-10', 'Contador', 'Contabilidad', 4200.00, 'ACTIVO', '+51 999 345 678', 'Av. Tacna 789, Lima', 'Rosa Martínez', '+51 999 345 679', '2025-08-18 19:07:14'),
(4, NULL, 'EMP004', 'Carmen Elena', 'Flores Vargas', '45678901', '1992-04-30', '2022-01-20', 'Vendedora', 'Ventas', 3200.00, 'ACTIVO', '+51 999 456 789', 'Jr. Ayacucho 321, Lima', 'Pedro Flores', '+51 999 456 790', '2025-08-18 19:07:14'),
(5, NULL, 'EMP005', 'Roberto Carlos', 'Herrera Díaz', '56789012', '1983-12-12', '2018-05-15', 'Supervisor de Producción', 'Producción', 4800.00, 'ACTIVO', '+51 999 567 890', 'Av. La Marina 654, Lima', 'Lucía Herrera', '+51 999 567 891', '2025-08-18 19:07:14'),
(6, NULL, 'EMP006', 'Patricia Isabel', 'Castro Ruiz', '67890123', '1995-09-18', '2023-02-10', 'Recepcionista', 'Administración', 2800.00, 'ACTIVO', '+51 999 678 901', 'Jr. Cusco 147, Lima', 'Fernando Castro', '+51 999 678 902', '2025-08-18 19:07:14'),
(7, NULL, 'EMP007', 'Miguel Ángel', 'Torres Mendoza', '78901234', '1987-06-25', '2020-11-30', 'Chofer', 'Logística', 3000.00, 'ACTIVO', '+51 999 789 012', 'Av. Javier Prado 258, Lima', 'Sofía Torres', '+51 999 789 013', '2025-08-18 19:07:14'),
(8, NULL, 'EMP008', 'Elena Beatriz', 'Morales Paredes', '89012345', '1993-01-14', '2022-06-15', 'Asistente Administrativa', 'Administración', 3100.00, 'ACTIVO', '+51 999 890 123', 'Jr. Huánuco 369, Lima', 'Ricardo Morales', '+51 999 890 124', '2025-08-18 19:07:14'),
(9, NULL, 'EMP009', 'Carlos Eduardo', 'Ríos Salazar', '90123456', '1986-08-03', '2021-09-01', 'Ingeniero de Sistemas', 'Tecnología', 5200.00, 'ACTIVO', '+51 999 901 234', 'Av. San Isidro 741, Lima', 'Ana Ríos', '+51 999 901 235', '2025-08-18 19:07:14'),
(10, NULL, 'EMP010', 'Rosa María', 'Vega Campos', '01234567', '1991-12-07', '2023-01-15', 'Auxiliar Contable', 'Contabilidad', 2900.00, 'ACTIVO', '+51 999 012 345', 'Jr. Junín 852, Lima', 'Manuel Vega', '+51 999 012 346', '2025-08-18 19:07:14'),
(14, NULL, 'EMP012', 'Pedross', 'López', '5457454', NULL, '2023-01-01', 'Desarrollador', 'IT', 5000.00, 'ACTIVO', '123456789', 'Calle Test', 'Maríaa', '987654321', '2025-08-18 21:11:30'),
(15, NULL, 'EMP011', 'Shakira', 'Mebarakkk', '64556465', '2025-08-01', '2025-08-28', 'Jefa de Audio', 'Musica', 546.00, 'VACACIONES', '456456', 'dfgdfg', 'Manuela', '74877456465', '2025-08-18 21:19:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `report_type` enum('ASISTENCIA','PAGOS','VACACIONES','EXTRAS','GENERAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` text COLLATE utf8mb4_unicode_ci,
  `generated_by` int NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('PENDIENTE','GENERADO','ERROR') COLLATE utf8mb4_unicode_ci DEFAULT 'PENDIENTE',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resumen_general`
--

CREATE TABLE `resumen_general` (
  `id` int NOT NULL,
  `period_month` int NOT NULL,
  `period_year` int NOT NULL,
  `total_employees` int DEFAULT '0',
  `total_hours_worked` decimal(8,2) DEFAULT '0.00',
  `total_overtime_hours` decimal(8,2) DEFAULT '0.00',
  `total_salary_paid` decimal(12,2) DEFAULT '0.00',
  `total_vacations_taken` int DEFAULT '0',
  `total_absences` int DEFAULT '0',
  `generated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `permissions` text COLLATE utf8mb4_unicode_ci,
  `status` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `permissions`, `status`, `created_at`) VALUES
(1, 'SUPER USER', 'Usuario con acceso completo al sistema', 'ALL', 'ACTIVO', '2025-08-18 19:06:52'),
(2, 'TRABAJADOR', 'Empleado básico del sistema', 'VIEW_OWN_DATA', 'ACTIVO', '2025-08-18 19:06:52'),
(3, 'VENDEDOR', 'Personal de ventas', 'VIEW_OWN_DATA,VIEW_SALES', 'ACTIVO', '2025-08-18 19:06:52'),
(4, 'RECEPCIONISTA', 'Personal de recepción', 'VIEW_OWN_DATA,VIEW_RECEPTION', 'ACTIVO', '2025-08-18 19:06:52'),
(5, 'CHOFER', 'Personal de transporte', 'VIEW_OWN_DATA,VIEW_TRANSPORT', 'ACTIVO', '2025-08-18 19:06:52'),
(6, 'PROGRAMADOR', 'Desarrollador del sistema', 'VIEW_OWN_DATA,VIEW_DEVELOPMENT', 'ACTIVO', '2025-08-18 19:06:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id`, `name`, `start_time`, `end_time`, `break_start`, `break_end`, `description`, `status`, `created_at`) VALUES
(1, 'Mañana', '08:00:00', '17:00:00', '12:00:00', '13:00:00', 'Turno de mañana estándar', 'ACTIVO', '2025-08-18 19:06:52'),
(2, 'Tarde', '14:00:00', '23:00:00', '18:00:00', '19:00:00', 'Turno de tarde', 'ACTIVO', '2025-08-18 19:06:52'),
(3, 'Noche', '22:00:00', '07:00:00', '02:00:00', '03:00:00', 'Turno nocturno', 'ACTIVO', '2025-08-18 19:06:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turno_asignaciones`
--

CREATE TABLE `turno_asignaciones` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `turno_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('ACTIVO','INACTIVO') COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVO',
  `code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `role`, `status`, `code`, `avatar`, `created_at`) VALUES
(1, 'kiaraluvie', 'kiararelpz@gmail.com', '$2y$10$RO2ClqxU5Mu4wfvXAXpnXuhzzpEvIGzmT3s0fY3Nk2//5ptNdYAFu', 'Kiara', 'Reyes', 'SUPER USER', 'ACTIVO', '1654656', '/Recursos/assets/img/avatars/user_1.png', '2025-08-18 19:13:50'),
(2, 'shaki', 'shakira@gmail.com', '$2y$10$mx6gKmY/Z1EwLziB5hqJdeiUeE17FNbE5vuXsX4nM33LGLokUdwFi', 'Shakira', 'Mebarak', 'PROGRAMADOR', 'INACTIVO', '16546567', '/Recursos/assets/img/avatars/user_2.jpg', '2025-08-18 19:23:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones`
--

CREATE TABLE `vacaciones` (
  `id` int NOT NULL,
  `personal_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_requested` int NOT NULL,
  `days_approved` int DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('SOLICITADO','APROBADO','RECHAZADO','EN_CURSO','COMPLETADO') COLLATE utf8mb4_unicode_ci DEFAULT 'SOLICITADO',
  `approved_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adelantos`
--
ALTER TABLE `adelantos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_asistencia` (`personal_id`,`date`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `extras`
--
ALTER TABLE `extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indices de la tabla `jornadas`
--
ALTER TABLE `jornadas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_jornada` (`personal_id`,`date`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pago` (`personal_id`,`period_month`,`period_year`);

--
-- Indices de la tabla `panel_control`
--
ALTER TABLE `panel_control`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_panel` (`personal_id`,`date`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indices de la tabla `resumen_general`
--
ALTER TABLE `resumen_general`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_resumen` (`period_month`,`period_year`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `turno_asignaciones`
--
ALTER TABLE `turno_asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `turno_id` (`turno_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `adelantos`
--
ALTER TABLE `adelantos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `extras`
--
ALTER TABLE `extras`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `jornadas`
--
ALTER TABLE `jornadas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `panel_control`
--
ALTER TABLE `panel_control`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `resumen_general`
--
ALTER TABLE `resumen_general`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `turno_asignaciones`
--
ALTER TABLE `turno_asignaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `adelantos`
--
ALTER TABLE `adelantos`
  ADD CONSTRAINT `adelantos_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `adelantos_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `extras`
--
ALTER TABLE `extras`
  ADD CONSTRAINT `extras_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `extras_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `jornadas`
--
ALTER TABLE `jornadas`
  ADD CONSTRAINT `jornadas_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `panel_control`
--
ALTER TABLE `panel_control`
  ADD CONSTRAINT `panel_control_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permisos_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `personal`
--
ALTER TABLE `personal`
  ADD CONSTRAINT `personal_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resumen_general`
--
ALTER TABLE `resumen_general`
  ADD CONSTRAINT `resumen_general_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `turno_asignaciones`
--
ALTER TABLE `turno_asignaciones`
  ADD CONSTRAINT `turno_asignaciones_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `turno_asignaciones_ibfk_2` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD CONSTRAINT `vacaciones_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vacaciones_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;