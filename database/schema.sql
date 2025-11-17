-- =====================================================
-- SISTEMA DE TRIAGE MÉDICO ADOM
-- Base de Datos Completa
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-05:00"; -- Zona horaria Bogotá

-- =====================================================
-- TABLA: PACIENTE
-- =====================================================
CREATE TABLE IF NOT EXISTS `PACIENTE` (
  `paciente_id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `edad` INT(3) NOT NULL,
  `sexo` ENUM('M','F') NOT NULL,
  `direccion` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(15) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`paciente_id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_telefono` (`telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: CASO_TRIAGE
-- =====================================================
CREATE TABLE IF NOT EXISTS `CASO_TRIAGE` (
  `caso_id` INT(11) NOT NULL AUTO_INCREMENT,
  `paciente_id` INT(11) NOT NULL,
  `sintomas` TEXT NOT NULL,
  `nivel_triage_ia` INT(1) NULL COMMENT 'Nivel sugerido por IA (1-5)',
  `nivel_triage_final` INT(1) NULL COMMENT 'Nivel validado por profesional (1-5)',
  `justificacion_ia` TEXT NULL,
  `justificacion_final` TEXT NULL COMMENT 'Si profesional edita',
  `estado` ENUM('pendiente','aprobado','editado','rechazado','emergencia') NOT NULL DEFAULT 'pendiente',
  `motivo_rechazo` TEXT NULL,
  `fecha_atencion` DATE NULL,
  `hora_atencion` TIME NULL,
  `profesional_atencion_id` INT(11) NULL COMMENT 'Quién atenderá',
  `usuario_validador_id` INT(11) NULL COMMENT 'Quién validó',
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_validacion` DATETIME NULL,
  PRIMARY KEY (`caso_id`),
  FOREIGN KEY (`paciente_id`) REFERENCES `PACIENTE`(`paciente_id`) ON DELETE CASCADE,
  INDEX `idx_estado` (`estado`),
  INDEX `idx_fecha_atencion` (`fecha_atencion`),
  INDEX `idx_nivel_ia` (`nivel_triage_ia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: SIGNOS_VITALES
-- =====================================================
CREATE TABLE IF NOT EXISTS `SIGNOS_VITALES` (
  `signos_id` INT(11) NOT NULL AUTO_INCREMENT,
  `caso_id` INT(11) NOT NULL,
  `tiene_fiebre` BOOLEAN DEFAULT FALSE,
  `temperatura` DECIMAL(4,1) NULL COMMENT 'Temperatura en °C',
  `tiene_dolor` BOOLEAN DEFAULT FALSE,
  `nivel_dolor` INT(2) NULL COMMENT 'Escala 1-10',
  `tiene_dificultad_respiratoria` BOOLEAN DEFAULT FALSE,
  `tiene_vomito` BOOLEAN DEFAULT FALSE,
  `tiene_perdida_consciencia` BOOLEAN DEFAULT FALSE,
  `esta_embarazada` BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (`signos_id`),
  FOREIGN KEY (`caso_id`) REFERENCES `CASO_TRIAGE`(`caso_id`) ON DELETE CASCADE,
  INDEX `idx_caso` (`caso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: USUARIO (Profesionales)
-- =====================================================
CREATE TABLE IF NOT EXISTS `USUARIO` (
  `usuario_id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `rol` ENUM('profesional','admin') NOT NULL DEFAULT 'profesional',
  `especialidad` VARCHAR(100) NULL,
  `activo` BOOLEAN DEFAULT TRUE,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` DATETIME NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `unique_email` (`email`),
  INDEX `idx_rol` (`rol`),
  INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: LOG_AUDITORIA
-- =====================================================
CREATE TABLE IF NOT EXISTS `LOG_AUDITORIA` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `caso_id` INT(11) NULL,
  `usuario_id` INT(11) NULL,
  `accion` VARCHAR(100) NOT NULL COMMENT 'crear_caso, aprobar, editar, rechazar, login, etc',
  `detalles` TEXT NULL COMMENT 'JSON con detalles adicionales',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `fecha_accion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`caso_id`) REFERENCES `CASO_TRIAGE`(`caso_id`) ON DELETE SET NULL,
  FOREIGN KEY (`usuario_id`) REFERENCES `USUARIO`(`usuario_id`) ON DELETE SET NULL,
  INDEX `idx_accion` (`accion`),
  INDEX `idx_fecha` (`fecha_accion`),
  INDEX `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: SIGNOS_ALARMA (nuevos, para guardar los de la IA)
-- =====================================================
CREATE TABLE IF NOT EXISTS `SIGNOS_ALARMA` (
  `alarma_id` INT(11) NOT NULL AUTO_INCREMENT,
  `caso_id` INT(11) NOT NULL,
  `signo_alarma` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`alarma_id`),
  FOREIGN KEY (`caso_id`) REFERENCES `CASO_TRIAGE`(`caso_id`) ON DELETE CASCADE,
  INDEX `idx_caso` (`caso_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;