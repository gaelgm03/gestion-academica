-- ============================================================
--  Proyecto: Gestión Académica - Schema Consolidado
--  Fecha: Noviembre 2025
--  No requiere migraciones adicionales
-- ============================================================

DROP DATABASE IF EXISTS gestion_academica;
CREATE DATABASE gestion_academica;
USE gestion_academica;

-- 1. ROL
CREATE TABLE rol (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rol (nombre) VALUES ('admin'), ('academia'), ('direccion'), ('docente'), ('coordinador');

-- 2. USUARIO
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

-- 3. PERMISO
CREATE TABLE permiso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scope VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO permiso (scope, action) VALUES
('docente', 'crear'), ('docente', 'editar'), ('docente', 'eliminar'), ('docente', 'ver'),
('incidencia', 'registrar'), ('incidencia', 'actualizar'), ('incidencia', 'eliminar'), ('incidencia', 'ver'),
('reporte', 'exportar'), ('reporte', 'ver'), ('academia', 'gestionar'), ('usuario', 'gestionar'), ('rol', 'asignar');

-- 4. ROL_PERMISO
CREATE TABLE rol_permiso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES rol(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permiso(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO rol_permiso (rol_id, permiso_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13),
(2, 4), (2, 5), (2, 6), (2, 8), (2, 9), (2, 10), (2, 11),
(3, 4), (3, 8), (3, 9), (3, 10),
(4, 4), (4, 5), (4, 8),
(5, 1), (5, 2), (5, 4), (5, 5), (5, 6), (5, 8), (5, 9), (5, 10), (5, 11);

-- 5. DOCENTE
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

-- 6. ACADEMIA
CREATE TABLE academia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO academia (nombre) VALUES
('Ingeniería'), ('Psicología'), ('Comunicación'), ('Economía'), ('Arte y Humanidades'),
('Ciencias Exactas'), ('Ciencias Naturales'), ('Ciencias Sociales'), ('Idiomas'), ('Negocios y Administración');

-- 7. DOCENTE_ACADEMIA
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
(1, 1), (1, 6), (2, 1), (3, 2), (3, 8), (4, 3), (5, 4), (5, 10), (6, 5), (7, 1), (8, 6);

-- 8. AREA_ESPECIALIDAD
CREATE TABLE area_especialidad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO area_especialidad (nombre, descripcion) VALUES
('Inteligencia Artificial', 'Machine Learning, Deep Learning, NLP'),
('Desarrollo de Software', 'Ingeniería de Software, Arquitectura, DevOps'),
('Bases de Datos', 'Diseño, Administración, Big Data'),
('Redes y Telecomunicaciones', 'Infraestructura, Seguridad de Redes, IoT'),
('Ciberseguridad', 'Seguridad Informática, Ethical Hacking'),
('Ciencia de Datos', 'Análisis de Datos, Estadística'),
('Gestión de Proyectos', 'PMI, Agile, Scrum'),
('Educación', 'Pedagogía, Didáctica, Tecnología Educativa'),
('Investigación', 'Metodología, Publicación Científica'),
('Negocios y Emprendimiento', 'Startups, Innovación'),
('Finanzas', 'Contabilidad, Análisis Financiero'),
('Marketing Digital', 'SEO, SEM, Redes Sociales'),
('Recursos Humanos', 'Gestión del Talento, Capacitación'),
('Derecho', 'Legislación, Propiedad Intelectual'),
('Psicología Organizacional', 'Comportamiento Organizacional'),
('Comunicación', 'Comunicación Corporativa'),
('Diseño', 'UX/UI, Diseño Gráfico'),
('Idiomas', 'Inglés, Francés, Alemán'),
('Matemáticas Aplicadas', 'Modelado, Optimización'),
('Física', 'Física Aplicada, Mecánica');

-- 9. DOCENTE_AREA_ESPECIALIDAD
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
(1, 8, 'experto', 10), (1, 9, 'avanzado', 8), (2, 2, 'avanzado', 6), (2, 3, 'intermedio', 4),
(3, 15, 'avanzado', 5), (3, 8, 'intermedio', 3), (4, 16, 'experto', 12), (4, 12, 'avanzado', 7),
(7, 1, 'experto', 8), (7, 6, 'avanzado', 6), (7, 2, 'intermedio', 4);

-- 10. TIPO_INCIDENCIA (5 categorías requeridas)
CREATE TABLE tipo_incidencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tipo_incidencia (nombre, descripcion, orden) VALUES
('Cambio de calificación', 'Solicitud de modificación de calificación', 1),
('Cambio de fecha de examen', 'Solicitud de reprogramación de fecha de examen', 2),
('Integridad académica', 'Reporte de violación a la integridad académica', 3),
('Reporte disciplinar a profesor', 'Reporte de conducta inapropiada del profesor', 4),
('Incidencia de pago', 'Incidencia de pagos a favor o en contra', 5);

-- 11. PERIODO_ACADEMICO
CREATE TABLE periodo_academico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estatus ENUM('planificacion', 'activo', 'finalizado') DEFAULT 'planificacion',
    INDEX idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO periodo_academico (codigo, nombre, fecha_inicio, fecha_fin, estatus) VALUES
('2024-1', 'Primavera 2024', '2024-01-15', '2024-05-31', 'finalizado'),
('2024-2', 'Otoño 2024', '2024-08-12', '2024-12-15', 'finalizado'),
('2025-1', 'Primavera 2025', '2025-01-13', '2025-05-30', 'activo'),
('2025-2', 'Otoño 2025', '2025-08-11', '2025-12-12', 'planificacion');

-- 12. CURSO
CREATE TABLE curso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    creditos INT DEFAULT 0,
    horas_semana INT DEFAULT 0,
    semestre INT,
    modalidad ENUM('presencial', 'virtual', 'hibrido') DEFAULT 'presencial',
    academia_id INT,
    estatus ENUM('activo', 'inactivo') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academia_id) REFERENCES academia(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_academia (academia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO curso (codigo, nombre, descripcion, creditos, horas_semana, semestre, modalidad, academia_id) VALUES
('SIS101', 'Introducción a la Programación', 'Fundamentos de programación', 6, 4, 1, 'presencial', 1),
('SIS201', 'Estructuras de Datos', 'Análisis de estructuras de datos', 6, 4, 3, 'presencial', 1),
('SIS301', 'Base de Datos', 'Diseño de bases de datos', 6, 4, 4, 'hibrido', 1),
('SIS401', 'Ingeniería de Software', 'Metodologías de desarrollo', 6, 4, 5, 'presencial', 1),
('SIS501', 'Inteligencia Artificial', 'Fundamentos de IA', 6, 4, 7, 'hibrido', 1),
('DER101', 'Introducción al Derecho', 'Conceptos fundamentales', 6, 4, 1, 'presencial', 2),
('ADM101', 'Fundamentos de Administración', 'Principios administrativos', 6, 4, 1, 'presencial', 3),
('ADM201', 'Contabilidad Financiera', 'Estados financieros', 6, 4, 2, 'hibrido', 3),
('ADM301', 'Mercadotecnia', 'Estrategias de marketing', 6, 4, 4, 'presencial', 3),
('COM101', 'Teoría de la Comunicación', 'Fundamentos teóricos', 6, 4, 1, 'presencial', 5);

-- 13. DOCENTE_CURSO
CREATE TABLE docente_curso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    curso_id INT NOT NULL,
    periodo VARCHAR(20) NOT NULL,
    grupo VARCHAR(10),
    horario VARCHAR(100),
    aula VARCHAR(50),
    cupo_maximo INT DEFAULT 30,
    inscritos INT DEFAULT 0,
    estatus ENUM('activo', 'finalizado', 'cancelado') DEFAULT 'activo',
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (docente_id) REFERENCES docente(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE CASCADE,
    INDEX idx_docente (docente_id),
    INDEX idx_curso (curso_id),
    UNIQUE KEY uk_docente_curso_periodo_grupo (docente_id, curso_id, periodo, grupo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO docente_curso (docente_id, curso_id, periodo, grupo, horario, aula, cupo_maximo, inscritos, estatus) VALUES
(1, 1, '2025-1', 'A', 'Lun-Mié 08:00-10:00', 'A-101', 35, 32, 'activo'),
(1, 2, '2025-1', 'A', 'Mar-Jue 10:00-12:00', 'A-102', 30, 28, 'activo'),
(2, 3, '2025-1', 'A', 'Lun-Mié 14:00-16:00', 'Lab-1', 25, 25, 'activo'),
(2, 4, '2025-1', 'B', 'Mar-Jue 16:00-18:00', 'A-201', 30, 22, 'activo'),
(3, 6, '2025-1', 'A', 'Lun-Mié 10:00-12:00', 'B-101', 40, 38, 'activo'),
(7, 5, '2025-1', 'A', 'Vie 09:00-13:00', 'Lab-2', 20, 18, 'activo');

-- 14. INCIDENCIA
CREATE TABLE incidencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_id INT NOT NULL,
    profesor INT,
    curso VARCHAR(100),
    curso_id INT,
    prioridad ENUM('Alta','Media','Baja') DEFAULT 'Media',
    sla VARCHAR(20),
    asignadoA INT,
    evidencias VARCHAR(255),
    status ENUM('abierto','en proceso','cerrado') DEFAULT 'abierto',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_id) REFERENCES tipo_incidencia(id),
    FOREIGN KEY (profesor) REFERENCES docente(id),
    FOREIGN KEY (asignadoA) REFERENCES usuario(id),
    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO incidencia (tipo_id, profesor, curso, prioridad, sla, asignadoA, status) VALUES
(2, 1, 'Cálculo Integral', 'Alta', '48h', 2, 'abierto'),
(3, 2, 'Bases de Datos', 'Media', '72h', 1, 'en proceso'),
(5, 3, 'Psicología General', 'Alta', '24h', 3, 'cerrado'),
(1, 4, 'Comunicación Oral', 'Media', '48h', 2, 'abierto'),
(4, 5, 'Microeconomía', 'Baja', '72h', 1, 'en proceso'),
(1, 6, 'Historia del Arte', 'Alta', '24h', 3, 'cerrado'),
(1, 7, 'Programación Avanzada', 'Baja', '96h', 2, 'abierto'),
(2, 8, 'Álgebra Lineal', 'Media', '48h', 9, 'en proceso'),
(1, 1, 'Cálculo Diferencial', 'Baja', '120h', 2, 'abierto'),
(1, 2, 'Estructuras de Datos', 'Alta', '24h', 1, 'cerrado'),
(4, 4, 'Redacción Periodística', 'Alta', '12h', 1, 'en proceso'),
(2, 7, 'Circuitos Eléctricos', 'Media', '96h', 2, 'abierto');

-- 15. INCIDENCIA_HISTORIAL
CREATE TABLE incidencia_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL,
    usuario_id INT NOT NULL,
    campo_modificado VARCHAR(50) NOT NULL,
    valor_anterior TEXT,
    valor_nuevo TEXT,
    accion ENUM('crear', 'editar', 'eliminar', 'cambio_status', 'asignar') DEFAULT 'editar',
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (incidencia_id) REFERENCES incidencia(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE RESTRICT,
    INDEX idx_incidencia (incidencia_id),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. CRITERIO_EVALUACION
CREATE TABLE criterio_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria ENUM('conocimiento', 'metodologia', 'comunicacion', 'puntualidad', 'material', 'evaluacion', 'otro') DEFAULT 'otro',
    peso DECIMAL(3,2) DEFAULT 1.00,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO criterio_evaluacion (nombre, descripcion, categoria, peso, orden, activo) VALUES
('Dominio del tema', 'Conocimiento profundo de la materia', 'conocimiento', 1.00, 1, TRUE),
('Actualización de contenidos', 'Información actualizada', 'conocimiento', 0.80, 2, TRUE),
('Claridad en la explicación', 'Explicaciones claras', 'metodologia', 1.00, 3, TRUE),
('Uso de ejemplos prácticos', 'Ejemplos útiles', 'metodologia', 0.80, 4, TRUE),
('Dinamismo en clase', 'Mantiene interés', 'metodologia', 0.70, 5, TRUE),
('Disposición para resolver dudas', 'Atiende preguntas', 'comunicacion', 1.00, 6, TRUE),
('Comunicación efectiva', 'Expresión clara', 'comunicacion', 0.90, 7, TRUE),
('Puntualidad', 'Inicia a tiempo', 'puntualidad', 0.90, 8, TRUE),
('Calidad del material', 'Material útil', 'material', 0.80, 9, TRUE),
('Evaluaciones justas', 'Reflejan lo enseñado', 'evaluacion', 1.00, 10, TRUE);

-- 17. PERIODO_EVALUACION
CREATE TABLE periodo_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    periodo_academico_id INT,
    estatus ENUM('programado', 'activo', 'cerrado') DEFAULT 'programado',
    FOREIGN KEY (periodo_academico_id) REFERENCES periodo_academico(id) ON DELETE SET NULL,
    INDEX idx_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO periodo_evaluacion (nombre, fecha_inicio, fecha_fin, periodo_academico_id, estatus) VALUES
('Evaluación Primavera 2024', '2024-05-01', '2024-05-15', 1, 'cerrado'),
('Evaluación Otoño 2024', '2024-12-01', '2024-12-15', 2, 'cerrado'),
('Evaluación Primavera 2025', '2025-05-01', '2025-05-15', 3, 'programado');

-- 18. EVALUACION_DOCENTE
CREATE TABLE evaluacion_docente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    docente_id INT NOT NULL,
    curso_id INT,
    periodo_evaluacion_id INT,
    evaluador_id INT,
    tipo_evaluador ENUM('alumno', 'par', 'coordinador', 'autoevaluacion') DEFAULT 'alumno',
    calificacion_global DECIMAL(4,2),
    comentarios TEXT,
    fortalezas TEXT,
    areas_mejora TEXT,
    recomendaciones TEXT,
    fecha_evaluacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estatus ENUM('borrador', 'completada', 'revisada') DEFAULT 'borrador',
    FOREIGN KEY (docente_id) REFERENCES docente(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE SET NULL,
    FOREIGN KEY (periodo_evaluacion_id) REFERENCES periodo_evaluacion(id) ON DELETE SET NULL,
    FOREIGN KEY (evaluador_id) REFERENCES usuario(id) ON DELETE SET NULL,
    INDEX idx_docente (docente_id),
    INDEX idx_curso (curso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO evaluacion_docente (docente_id, curso_id, periodo_evaluacion_id, tipo_evaluador, calificacion_global, comentarios, fortalezas, areas_mejora, estatus) VALUES
(1, 1, 1, 'alumno', 9.2, 'Excelente docente', 'Dominio del tema', 'Más ejemplos', 'completada'),
(1, 1, 1, 'alumno', 8.8, 'Buena clase', 'Paciencia', 'Puntualidad', 'completada'),
(1, 2, 2, 'alumno', 9.5, 'Excelente', 'Todo', 'Ninguna', 'completada'),
(2, 3, 1, 'alumno', 8.5, 'Buen profesor', 'Conocimiento', 'Más dinámico', 'completada'),
(2, 3, 2, 'alumno', 8.7, 'Mejoró', 'Ejemplos', 'Retroalimentación', 'completada'),
(7, 5, 2, 'alumno', 9.8, 'Excelente en IA', 'Vanguardia', 'Ninguna', 'completada');

-- 19. EVALUACION_DETALLE
CREATE TABLE evaluacion_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    criterio_id INT NOT NULL,
    calificacion DECIMAL(4,2) NOT NULL,
    comentario TEXT,
    FOREIGN KEY (evaluacion_id) REFERENCES evaluacion_docente(id) ON DELETE CASCADE,
    FOREIGN KEY (criterio_id) REFERENCES criterio_evaluacion(id) ON DELETE CASCADE,
    UNIQUE KEY uk_evaluacion_criterio (evaluacion_id, criterio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO evaluacion_detalle (evaluacion_id, criterio_id, calificacion) VALUES
(1, 1, 9.5), (1, 2, 9.0), (1, 3, 9.5), (1, 6, 9.0), (1, 8, 9.0), (1, 10, 9.5),
(2, 1, 9.0), (2, 3, 9.0), (2, 6, 8.5), (2, 8, 8.0), (2, 10, 9.0);

-- 20. VISTAS
CREATE OR REPLACE VIEW vista_dashboard AS
SELECT
    COUNT(DISTINCT d.id) AS total_docentes,
    SUM(CASE WHEN d.sni = 1 THEN 1 ELSE 0 END) AS docentes_sni,
    SUM(CASE WHEN d.estatus = 'activo' THEN 1 ELSE 0 END) AS docentes_activos,
    (SELECT COUNT(*) FROM incidencia) AS total_incidencias,
    (SELECT COUNT(*) FROM incidencia WHERE status = 'abierto') AS incidencias_abiertas
FROM docente d;

CREATE OR REPLACE VIEW vista_cursos_completa AS
SELECT c.id, c.codigo, c.nombre, c.descripcion, c.creditos, c.horas_semana, c.semestre, c.modalidad, c.estatus,
    a.id as academia_id, a.nombre as academia_nombre,
    (SELECT COUNT(*) FROM docente_curso dc WHERE dc.curso_id = c.id AND dc.estatus = 'activo') as docentes_asignados
FROM curso c LEFT JOIN academia a ON c.academia_id = a.id;

CREATE OR REPLACE VIEW vista_docente_curso AS
SELECT dc.id, dc.periodo, dc.grupo, dc.horario, dc.aula, dc.cupo_maximo, dc.inscritos, dc.estatus,
    c.id as curso_id, c.codigo as curso_codigo, c.nombre as curso_nombre,
    d.id as docente_id, u.nombre as docente_nombre, u.email as docente_email
FROM docente_curso dc
INNER JOIN curso c ON dc.curso_id = c.id
INNER JOIN docente d ON dc.docente_id = d.id
INNER JOIN usuario u ON d.id_usuario = u.id;

CREATE OR REPLACE VIEW vista_evaluaciones_docente AS
SELECT d.id as docente_id, u.nombre as docente_nombre, u.email as docente_email,
    COUNT(ed.id) as total_evaluaciones, ROUND(AVG(ed.calificacion_global), 2) as promedio_global
FROM docente d
INNER JOIN usuario u ON d.id_usuario = u.id
LEFT JOIN evaluacion_docente ed ON d.id = ed.docente_id AND ed.estatus = 'completada'
GROUP BY d.id, u.nombre, u.email;

CREATE OR REPLACE VIEW vista_incidencia_historial AS
SELECT ih.id, ih.incidencia_id, ih.campo_modificado, ih.valor_anterior, ih.valor_nuevo, ih.accion,
    ih.fecha_cambio, u.nombre as usuario_nombre, ti.nombre as tipo_incidencia
FROM incidencia_historial ih
INNER JOIN usuario u ON ih.usuario_id = u.id
LEFT JOIN incidencia i ON ih.incidencia_id = i.id
LEFT JOIN tipo_incidencia ti ON i.tipo_id = ti.id;

-- 21. ÍNDICES ADICIONALES
CREATE INDEX idx_usuario_email ON usuario(email);
CREATE INDEX idx_usuario_rol ON usuario(rol_id);
CREATE INDEX idx_docente_usuario ON docente(id_usuario);
CREATE INDEX idx_docente_estatus ON docente(estatus);
CREATE INDEX idx_incidencia_tipo ON incidencia(tipo_id);
CREATE INDEX idx_incidencia_profesor ON incidencia(profesor);
CREATE INDEX idx_incidencia_asignado ON incidencia(asignadoA);
CREATE INDEX idx_incidencia_status ON incidencia(status);
CREATE INDEX idx_incidencia_fecha ON incidencia(fecha_creacion);
CREATE INDEX idx_area_nombre ON area_especialidad(nombre);
CREATE INDEX idx_incidencia_curso_id ON incidencia(curso_id);

-- FIN DEL SCHEMA
