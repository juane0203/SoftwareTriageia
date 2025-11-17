-- =====================================================
-- DATOS INICIALES - USUARIOS PROFESIONALES
-- =====================================================

-- Insertar usuarios de prueba (contraseña: "admin123" para todos)
-- Hash generado con: password_hash('admin123', PASSWORD_BCRYPT)

INSERT INTO `USUARIO` (`nombre`, `email`, `password_hash`, `rol`, `especialidad`, `activo`) VALUES
('Dr. Juan Pérez', 'juan.perez@adom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesional', 'Medicina General', 1),
('Dra. María González', 'maria.gonzalez@adom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesional', 'Medicina Interna', 1),
('Dr. Carlos Ramírez', 'carlos.ramirez@adom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'profesional', 'Medicina Familiar', 1),
('Admin Sistema', 'admin@adom.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administración', 1);

-- =====================================================
-- PACIENTES DE PRUEBA (Opcional)
-- =====================================================

INSERT INTO `PACIENTE` (`nombre`, `apellido`, `edad`, `sexo`, `direccion`, `telefono`, `email`) VALUES
('Pedro', 'López', 45, 'M', 'Calle 100 #15-20, Bogotá', '3001234567', 'pedro.lopez@email.com'),
('Ana', 'Martínez', 32, 'F', 'Carrera 7 #45-30, Bogotá', '3109876543', 'ana.martinez@email.com'),
('Luis', 'Rodríguez', 28, 'M', 'Avenida 68 #20-15, Bogotá', '3201112233', 'luis.rodriguez@email.com');