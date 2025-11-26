-- ============================================================
-- Migración 005: Modelo Completo de Cursos/Materias
-- Sistema de Gestión Académica
-- Fecha: Noviembre 2025
-- ============================================================

-- ============================================================
-- 1. Tabla CURSO (Materias)
-- ============================================================
CREATE TABLE IF NOT EXISTS curso (
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
    INDEX idx_academia (academia_id),
    INDEX idx_estatus (estatus),
    INDEX idx_semestre (semestre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. Tabla DOCENTE_CURSO (Asignaciones de docentes a cursos)
-- ============================================================
CREATE TABLE IF NOT EXISTS docente_curso (
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
    INDEX idx_periodo (periodo),
    INDEX idx_estatus (estatus),
    
    UNIQUE KEY uk_docente_curso_periodo_grupo (docente_id, curso_id, periodo, grupo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. Tabla PERIODO_ACADEMICO
-- ============================================================
CREATE TABLE IF NOT EXISTS periodo_academico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estatus ENUM('planificacion', 'activo', 'finalizado') DEFAULT 'planificacion',
    
    INDEX idx_estatus (estatus),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. Vista para consultas de cursos con información completa
-- ============================================================
CREATE OR REPLACE VIEW vista_cursos_completa AS
SELECT 
    c.id,
    c.codigo,
    c.nombre,
    c.descripcion,
    c.creditos,
    c.horas_semana,
    c.semestre,
    c.modalidad,
    c.estatus,
    a.id as academia_id,
    a.nombre as academia_nombre,
    (SELECT COUNT(*) FROM docente_curso dc WHERE dc.curso_id = c.id AND dc.estatus = 'activo') as docentes_asignados,
    (SELECT SUM(dc.inscritos) FROM docente_curso dc WHERE dc.curso_id = c.id AND dc.estatus = 'activo') as total_inscritos
FROM curso c
LEFT JOIN academia a ON c.academia_id = a.id;

-- ============================================================
-- 5. Vista para asignaciones de docentes a cursos
-- ============================================================
CREATE OR REPLACE VIEW vista_docente_curso AS
SELECT 
    dc.id,
    dc.periodo,
    dc.grupo,
    dc.horario,
    dc.aula,
    dc.cupo_maximo,
    dc.inscritos,
    dc.estatus,
    dc.fecha_asignacion,
    c.id as curso_id,
    c.codigo as curso_codigo,
    c.nombre as curso_nombre,
    c.creditos,
    c.semestre,
    d.id as docente_id,
    u.nombre as docente_nombre,
    u.email as docente_email,
    a.nombre as academia_nombre
FROM docente_curso dc
INNER JOIN curso c ON dc.curso_id = c.id
INNER JOIN docente d ON dc.docente_id = d.id
INNER JOIN usuario u ON d.id_usuario = u.id
LEFT JOIN academia a ON c.academia_id = a.id;

-- ============================================================
-- 6. Datos de ejemplo: Periodos académicos
-- ============================================================
INSERT INTO periodo_academico (codigo, nombre, fecha_inicio, fecha_fin, estatus) VALUES
('2024-1', 'Primavera 2024', '2024-01-15', '2024-05-31', 'finalizado'),
('2024-2', 'Otoño 2024', '2024-08-12', '2024-12-15', 'finalizado'),
('2025-1', 'Primavera 2025', '2025-01-13', '2025-05-30', 'activo'),
('2025-2', 'Otoño 2025', '2025-08-11', '2025-12-12', 'planificacion');

-- ============================================================
-- 7. Datos de ejemplo: Cursos
-- ============================================================
INSERT INTO curso (codigo, nombre, descripcion, creditos, horas_semana, semestre, modalidad, academia_id, estatus) VALUES
-- Cursos de Sistemas (academia_id = 1)
('SIS101', 'Introducción a la Programación', 'Fundamentos de programación y lógica computacional', 6, 4, 1, 'presencial', 1, 'activo'),
('SIS201', 'Estructuras de Datos', 'Análisis y diseño de estructuras de datos', 6, 4, 3, 'presencial', 1, 'activo'),
('SIS301', 'Base de Datos', 'Diseño y administración de bases de datos relacionales', 6, 4, 4, 'hibrido', 1, 'activo'),
('SIS401', 'Ingeniería de Software', 'Metodologías y procesos de desarrollo de software', 6, 4, 5, 'presencial', 1, 'activo'),
('SIS501', 'Inteligencia Artificial', 'Fundamentos de IA y aprendizaje automático', 6, 4, 7, 'hibrido', 1, 'activo'),

-- Cursos de Derecho (academia_id = 2)
('DER101', 'Introducción al Derecho', 'Conceptos fundamentales del derecho', 6, 4, 1, 'presencial', 2, 'activo'),
('DER201', 'Derecho Civil', 'Derecho civil personas y familia', 6, 4, 3, 'presencial', 2, 'activo'),
('DER301', 'Derecho Penal', 'Teoría del delito y sistema penal', 6, 4, 4, 'presencial', 2, 'activo'),

-- Cursos de Administración (academia_id = 3)
('ADM101', 'Fundamentos de Administración', 'Principios y teorías administrativas', 6, 4, 1, 'presencial', 3, 'activo'),
('ADM201', 'Contabilidad Financiera', 'Estados financieros y análisis contable', 6, 4, 2, 'hibrido', 3, 'activo'),
('ADM301', 'Mercadotecnia', 'Estrategias de marketing y comportamiento del consumidor', 6, 4, 4, 'presencial', 3, 'activo'),
('ADM401', 'Finanzas Corporativas', 'Gestión financiera empresarial', 6, 4, 6, 'virtual', 3, 'activo'),

-- Cursos de Arquitectura (academia_id = 4)
('ARQ101', 'Diseño Arquitectónico I', 'Fundamentos del diseño arquitectónico', 8, 6, 1, 'presencial', 4, 'activo'),
('ARQ201', 'Historia de la Arquitectura', 'Evolución histórica de la arquitectura', 4, 3, 2, 'presencial', 4, 'activo'),
('ARQ301', 'Estructuras', 'Análisis estructural para arquitectura', 6, 4, 4, 'presencial', 4, 'activo'),

-- Cursos de Comunicación (academia_id = 5)
('COM101', 'Teoría de la Comunicación', 'Fundamentos teóricos de la comunicación', 6, 4, 1, 'presencial', 5, 'activo'),
('COM201', 'Producción Audiovisual', 'Técnicas de producción de video y audio', 6, 5, 3, 'hibrido', 5, 'activo'),
('COM301', 'Periodismo Digital', 'Prácticas de periodismo en medios digitales', 6, 4, 5, 'virtual', 5, 'activo');

-- ============================================================
-- 8. Datos de ejemplo: Asignaciones docente-curso
-- ============================================================
INSERT INTO docente_curso (docente_id, curso_id, periodo, grupo, horario, aula, cupo_maximo, inscritos, estatus) VALUES
-- Docente 1 (Ana López)
(1, 1, '2025-1', 'A', 'Lun-Mié 08:00-10:00', 'A-101', 35, 32, 'activo'),
(1, 2, '2025-1', 'A', 'Mar-Jue 10:00-12:00', 'A-102', 30, 28, 'activo'),

-- Docente 2 (Carlos Jiménez)
(2, 3, '2025-1', 'A', 'Lun-Mié 14:00-16:00', 'Lab-1', 25, 25, 'activo'),
(2, 4, '2025-1', 'B', 'Mar-Jue 16:00-18:00', 'A-201', 30, 22, 'activo'),

-- Docente 3 (Sofía Torres)
(3, 6, '2025-1', 'A', 'Lun-Mié 10:00-12:00', 'B-101', 40, 38, 'activo'),

-- Docente 7 (Laura Martínez)
(7, 5, '2025-1', 'A', 'Vie 09:00-13:00', 'Lab-2', 20, 18, 'activo'),

-- Docente 8 (Pedro Sánchez)
(8, 9, '2025-1', 'A', 'Lun-Mié 08:00-10:00', 'C-101', 35, 33, 'activo'),
(8, 10, '2025-1', 'A', 'Mar-Jue 08:00-10:00', 'C-102', 35, 30, 'activo');

-- ============================================================
-- 9. Actualizar tabla incidencia para usar curso_id (opcional)
-- Nota: Mantenemos el campo curso (texto) por compatibilidad
-- ============================================================
ALTER TABLE incidencia 
ADD COLUMN curso_id INT NULL AFTER curso,
ADD CONSTRAINT fk_incidencia_curso FOREIGN KEY (curso_id) REFERENCES curso(id) ON DELETE SET NULL;

-- Crear índice para el nuevo campo
CREATE INDEX idx_incidencia_curso_id ON incidencia(curso_id);
