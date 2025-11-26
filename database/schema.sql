-- ============================================================
--  Proyecto Final: Gestión Académica
--  Schema Completo con todas las tablas
--  Fecha: Noviembre 2025
--  
--  Incluye: Roles, Usuarios, Permisos, Docentes, Academias,
--           Tipos de Incidencia, Incidencias, Áreas de Especialidad
-- ============================================================

DROP DATABASE IF EXISTS gestion_academica;
CREATE DATABASE gestion_academica;
USE gestion_academica;

-- ============================================================
-- 1. Tabla ROL
-- ============================================================
CREATE TABLE rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rol (nombre) VALUES
('admin'),
('academia'),
('direccion'),
('docente'),
('coordinador');

-- ============================================================
-- 2. Tabla USUARIO
-- ============================================================
CREATE TABLE usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    rol_id INT,
    FOREIGN KEY (rol_id) REFERENCES rol(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuario (email, nombre, rol_id) VALUES
('ana.lopez@up.edu.mx', 'Ana López', 1),
('carlos.jimenez@up.edu.mx', 'Carlos Jiménez', 2),
('sofia.torres@up.edu.mx', 'Sofía Torres', 3),
('marco.hernandez@up.edu.mx', 'Marco Hernández', 2),
('isabel.gomez@up.edu.mx', 'Isabel Gómez', 1),
('daniel.rosas@up.edu.mx', 'Daniel Rosas', 3),
('laura.martinez@up.edu.mx', 'Laura Martínez', 4),
('pedro.sanchez@up.edu.mx', 'Pedro Sánchez', 4),
('maria.rodriguez@up.edu.mx', 'María Rodríguez', 5),
('juan.perez@up.edu.mx', 'Juan Pérez', 4),
('carmen.diaz@up.edu.mx', 'Carmen Díaz', 2),
('roberto.garcia@up.edu.mx', 'Roberto García', 4),
('elena.morales@up.edu.mx', 'Elena Morales', 4),
('luis.vargas@up.edu.mx', 'Luis Vargas', 5),
('patricia.cruz@up.edu.mx', 'Patricia Cruz', 4),
('fernando.ruiz@up.edu.mx', 'Fernando Ruiz', 3),
('diana.castillo@up.edu.mx', 'Diana Castillo', 4),
('jorge.mendoza@up.edu.mx', 'Jorge Mendoza', 4),
('andrea.luna@up.edu.mx', 'Andrea Luna', 2),
('miguel.reyes@up.edu.mx', 'Miguel Reyes', 4);

-- ============================================================
-- 3. Tabla PERMISO
-- ============================================================
CREATE TABLE permiso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scope VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permiso (scope, action) VALUES
('docente', 'crear'),
('docente', 'editar'),
('docente', 'eliminar'),
('docente', 'ver'),
('incidencia', 'registrar'),
('incidencia', 'actualizar'),
('incidencia', 'eliminar'),
('incidencia', 'ver'),
('reporte', 'exportar'),
('reporte', 'ver'),
('academia', 'gestionar'),
('usuario', 'gestionar'),
('rol', 'asignar');

-- ============================================================
-- 3B. Tabla ROL_PERMISO (Relación muchos a muchos)
-- ============================================================
CREATE TABLE rol_permiso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES rol(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permiso(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asignación de permisos a roles
INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
-- Admin: todos los permisos
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13),
-- Academia: gestión de incidencias y reportes
(2, 4), (2, 5), (2, 6), (2, 8), (2, 9), (2, 10), (2, 11),
-- Dirección: ver todo y exportar reportes
(3, 4), (3, 8), (3, 9), (3, 10),
-- Docente: ver y registrar incidencias propias
(4, 4), (4, 5), (4, 8),
-- Coordinador: gestión completa de academia
(5, 1), (5, 2), (5, 4), (5, 5), (5, 6), (5, 8), (5, 9), (5, 10), (5, 11);

-- ============================================================
-- 4. Tabla DOCENTE
-- ============================================================
CREATE TABLE docente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    grados VARCHAR(100),
    idioma VARCHAR(50),
    sni BOOLEAN DEFAULT FALSE,
    cvlink VARCHAR(255),
    estatus ENUM('activo', 'inactivo') DEFAULT 'activo',
    FOREIGN KEY (id_usuario) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO docente (id_usuario, grados, idioma, sni, cvlink, estatus) VALUES
(1, 'Doctorado en Educación', 'Inglés', 1, 'https://cvup.mx/ana-lopez', 'activo'),
(2, 'Maestría en Sistemas', 'Francés', 0, 'https://cvup.mx/carlos-jimenez', 'activo'),
(3, 'Licenciatura en Psicología', 'Inglés', 0, 'https://cvup.mx/sofia-torres', 'activo'),
(4, 'Doctorado en Comunicación', 'Alemán', 1, 'https://cvup.mx/marco-hernandez', 'activo'),
(5, 'Maestría en Economía', 'Español', 0, 'https://cvup.mx/isabel-gomez', 'inactivo'),
(6, 'Licenciatura en Arte', 'Inglés', 0, 'https://cvup.mx/daniel-rosas', 'activo'),
(7, 'Doctorado en Ingeniería', 'Inglés', 1, 'https://cvup.mx/laura-martinez', 'activo'),
(8, 'Maestría en Matemáticas', 'Inglés', 0, 'https://cvup.mx/pedro-sanchez', 'activo'),
(10, 'Doctorado en Física', 'Italiano', 1, 'https://cvup.mx/juan-perez', 'activo'),
(12, 'Maestría en Química', 'Inglés', 0, 'https://cvup.mx/roberto-garcia', 'activo'),
(13, 'Licenciatura en Biología', 'Español', 0, 'https://cvup.mx/elena-morales', 'activo'),
(15, 'Doctorado en Literatura', 'Francés', 1, 'https://cvup.mx/patricia-cruz', 'activo'),
(17, 'Maestría en Filosofía', 'Inglés', 0, 'https://cvup.mx/diana-castillo', 'activo'),
(18, 'Licenciatura en Historia', 'Español', 0, 'https://cvup.mx/jorge-mendoza', 'activo'),
(20, 'Doctorado en Sociología', 'Portugués', 1, 'https://cvup.mx/miguel-reyes', 'activo');

-- ============================================================
-- 5. Tabla ACADEMIA
-- ============================================================
CREATE TABLE academia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO academia (nombre) VALUES
('Ingeniería'),
('Psicología'),
('Comunicación'),
('Economía'),
('Arte y Humanidades'),
('Ciencias Exactas'),
('Ciencias Naturales'),
('Ciencias Sociales'),
('Idiomas'),
('Negocios y Administración');

-- ============================================================
-- 5B. Tabla DOCENTE_ACADEMIA (Relación muchos a muchos)
-- ============================================================
CREATE TABLE docente_academia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    academia_id INT NOT NULL,
    fecha_asignacion DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (docente_id) REFERENCES docente(id) ON DELETE CASCADE,
    FOREIGN KEY (academia_id) REFERENCES academia(id) ON DELETE CASCADE,
    UNIQUE KEY unique_docente_academia (docente_id, academia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO docente_academia (docente_id, academia_id) VALUES
(1, 1), (1, 6),
(2, 1),
(3, 2), (3, 8),
(4, 3),
(5, 4), (5, 10),
(6, 5),
(7, 1),
(8, 6),
(9, 6),
(10, 7),
(11, 7),
(12, 5), (12, 9),
(13, 5),
(14, 8),
(15, 8), (15, 2);

-- ============================================================
-- 6. Tabla AREA_ESPECIALIDAD (Catálogo)
-- ============================================================
CREATE TABLE area_especialidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO area_especialidad (nombre, descripcion) VALUES
('Inteligencia Artificial', 'Machine Learning, Deep Learning, NLP, Visión por Computadora'),
('Desarrollo de Software', 'Ingeniería de Software, Arquitectura, DevOps, Testing'),
('Bases de Datos', 'Diseño, Administración, Big Data, Data Warehousing'),
('Redes y Telecomunicaciones', 'Infraestructura, Seguridad de Redes, IoT'),
('Ciberseguridad', 'Seguridad Informática, Ethical Hacking, Criptografía'),
('Ciencia de Datos', 'Análisis de Datos, Estadística, Visualización'),
('Gestión de Proyectos', 'PMI, Agile, Scrum, Gestión de TI'),
('Educación', 'Pedagogía, Didáctica, Tecnología Educativa'),
('Investigación', 'Metodología, Publicación Científica, I+D'),
('Negocios y Emprendimiento', 'Startups, Innovación, Modelos de Negocio'),
('Finanzas', 'Contabilidad, Análisis Financiero, Economía'),
('Marketing Digital', 'SEO, SEM, Redes Sociales, E-commerce'),
('Recursos Humanos', 'Gestión del Talento, Capacitación, Desarrollo Organizacional'),
('Derecho', 'Legislación, Propiedad Intelectual, Derecho Corporativo'),
('Psicología Organizacional', 'Comportamiento Organizacional, Liderazgo'),
('Comunicación', 'Comunicación Corporativa, Relaciones Públicas'),
('Diseño', 'UX/UI, Diseño Gráfico, Diseño Industrial'),
('Idiomas', 'Inglés, Francés, Alemán, Traducción'),
('Matemáticas Aplicadas', 'Modelado, Optimización, Simulación'),
('Física', 'Física Aplicada, Mecánica, Termodinámica');

-- ============================================================
-- 6B. Tabla DOCENTE_AREA_ESPECIALIDAD (Relación muchos a muchos)
-- ============================================================
CREATE TABLE docente_area_especialidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    area_id INT NOT NULL,
    nivel ENUM('básico', 'intermedio', 'avanzado', 'experto') DEFAULT 'intermedio',
    anios_experiencia INT DEFAULT 0,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (docente_id) REFERENCES docente(id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES area_especialidad(id) ON DELETE CASCADE,
    UNIQUE KEY unique_docente_area (docente_id, area_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO docente_area_especialidad (docente_id, area_id, nivel, anios_experiencia) VALUES
(1, 8, 'experto', 10),
(1, 9, 'avanzado', 8),
(2, 2, 'avanzado', 6),
(2, 3, 'intermedio', 4),
(3, 15, 'avanzado', 5),
(3, 8, 'intermedio', 3),
(4, 16, 'experto', 12),
(4, 12, 'avanzado', 7),
(7, 1, 'experto', 8),
(7, 6, 'avanzado', 6),
(7, 2, 'intermedio', 4);

-- ============================================================
-- 7. Tabla TIPO_INCIDENCIA (Catálogo de 5 categorías)
-- ============================================================
CREATE TABLE tipo_incidencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tipo_incidencia (nombre, descripcion, orden) VALUES
('Cambio de calificación', 'Solicitud de modificación de calificación en el sistema', 1),
('Cambio de fecha de examen', 'Solicitud de reprogramación de fecha de examen', 2),
('Integridad académica', 'Reporte de violación a la integridad académica (plagio, fraude, etc.)', 3),
('Reporte disciplinar a profesor', 'Reporte de conducta inapropiada o falta disciplinaria del profesor', 4),
('Incidencia de pago', 'Incidencia relacionada con pagos (a favor o en contra del docente)', 5);

-- ============================================================
-- 8. Tabla INCIDENCIA (con tipo_id en lugar de tipo)
-- ============================================================
CREATE TABLE incidencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_id INT NOT NULL,
    profesor INT,
    curso VARCHAR(100),
    prioridad ENUM('Alta','Media','Baja') DEFAULT 'Media',
    sla VARCHAR(20),
    asignadoA INT,
    evidencias VARCHAR(255),
    status ENUM('abierto','en proceso','cerrado') DEFAULT 'abierto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_id) REFERENCES tipo_incidencia(id),
    FOREIGN KEY (profesor) REFERENCES docente(id),
    FOREIGN KEY (asignadoA) REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO incidencia (tipo_id, profesor, curso, prioridad, sla, asignadoA, evidencias, status) VALUES
(2, 1, 'Cálculo Integral', 'Alta', '48h', 2, NULL, 'abierto'),
(3, 2, 'Bases de Datos', 'Media', '72h', 1, NULL, 'en proceso'),
(5, 3, 'Psicología General', 'Alta', '24h', 3, NULL, 'cerrado'),
(1, 4, 'Comunicación Oral', 'Media', '48h', 2, NULL, 'abierto'),
(4, 5, 'Microeconomía', 'Baja', '72h', 1, NULL, 'en proceso'),
(1, 6, 'Historia del Arte', 'Alta', '24h', 3, NULL, 'cerrado'),
(1, 7, 'Programación Avanzada', 'Baja', '96h', 2, NULL, 'abierto'),
(2, 8, 'Álgebra Lineal', 'Media', '48h', 9, NULL, 'en proceso'),
(2, 9, 'Termodinámica', 'Alta', '24h', 1, NULL, 'abierto'),
(1, 10, 'Química Orgánica', 'Media', '72h', 11, NULL, 'abierto'),
(4, 11, 'Biología Celular', 'Alta', '24h', 2, NULL, 'en proceso'),
(1, 12, 'Literatura Contemporánea', 'Baja', '96h', 9, NULL, 'cerrado'),
(1, 13, 'Filosofía Moderna', 'Media', '48h', 1, NULL, 'abierto'),
(2, 14, 'Historia Universal', 'Alta', '24h', 16, NULL, 'en proceso'),
(3, 15, 'Antropología Social', 'Alta', '48h', 1, NULL, 'abierto'),
(1, 1, 'Cálculo Diferencial', 'Baja', '120h', 2, NULL, 'abierto'),
(1, 2, 'Estructuras de Datos', 'Alta', '24h', 1, NULL, 'cerrado'),
(1, 3, 'Psicología Clínica', 'Media', '72h', 3, NULL, 'cerrado'),
(4, 4, 'Redacción Periodística', 'Alta', '12h', 1, NULL, 'en proceso'),
(2, 7, 'Circuitos Eléctricos', 'Media', '96h', 2, NULL, 'abierto');

-- ============================================================
-- 9. VISTA DE DASHBOARD
-- ============================================================
CREATE OR REPLACE VIEW vista_dashboard AS
SELECT
    COUNT(DISTINCT docente.id) AS total_docentes,
    SUM(CASE WHEN docente.sni = 1 THEN 1 ELSE 0 END) AS docentes_sni,
    SUM(CASE WHEN docente.estatus = 'activo' THEN 1 ELSE 0 END) AS docentes_activos,
    COUNT(incidencia.id) AS total_incidencias,
    SUM(CASE WHEN incidencia.status = 'abierto' THEN 1 ELSE 0 END) AS incidencias_abiertas
FROM docente
LEFT JOIN incidencia ON docente.id = incidencia.profesor;

-- ============================================================
-- 10. ÍNDICES PARA OPTIMIZACIÓN
-- ============================================================
CREATE INDEX idx_usuario_email ON usuario(email);
CREATE INDEX idx_usuario_rol ON usuario(rol_id);
CREATE INDEX idx_docente_usuario ON docente(id_usuario);
CREATE INDEX idx_docente_estatus ON docente(estatus);
CREATE INDEX idx_incidencia_tipo ON incidencia(tipo_id);
CREATE INDEX idx_incidencia_profesor ON incidencia(profesor);
CREATE INDEX idx_incidencia_asignado ON incidencia(asignadoA);
CREATE INDEX idx_incidencia_status ON incidencia(status);
CREATE INDEX idx_incidencia_prioridad ON incidencia(prioridad);
CREATE INDEX idx_incidencia_fecha ON incidencia(fecha_creacion);
CREATE INDEX idx_area_especialidad_nombre ON area_especialidad(nombre);
CREATE INDEX idx_docente_area_docente ON docente_area_especialidad(docente_id);
CREATE INDEX idx_docente_area_area ON docente_area_especialidad(area_id);

-- ============================================================
-- FIN DEL SCHEMA COMPLETO
-- ============================================================

