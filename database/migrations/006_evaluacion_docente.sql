-- ============================================================
-- Migración 006: Sistema de Evaluación Docente
-- Sistema de Gestión Académica
-- Fecha: Noviembre 2025
-- ============================================================

-- ============================================================
-- 1. Tabla CRITERIO_EVALUACION (Catálogo de criterios)
-- ============================================================
CREATE TABLE IF NOT EXISTS criterio_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria ENUM('conocimiento', 'metodologia', 'comunicacion', 'puntualidad', 'material', 'evaluacion', 'otro') DEFAULT 'otro',
    peso DECIMAL(3,2) DEFAULT 1.00,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. Tabla PERIODO_EVALUACION
-- ============================================================
CREATE TABLE IF NOT EXISTS periodo_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    periodo_academico_id INT,
    estatus ENUM('programado', 'activo', 'cerrado') DEFAULT 'programado',
    
    FOREIGN KEY (periodo_academico_id) REFERENCES periodo_academico(id) ON DELETE SET NULL,
    
    INDEX idx_estatus (estatus),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. Tabla EVALUACION_DOCENTE (Evaluaciones principales)
-- ============================================================
CREATE TABLE IF NOT EXISTS evaluacion_docente (
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
    INDEX idx_curso (curso_id),
    INDEX idx_periodo (periodo_evaluacion_id),
    INDEX idx_tipo (tipo_evaluador),
    INDEX idx_estatus (estatus),
    INDEX idx_fecha (fecha_evaluacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. Tabla EVALUACION_DETALLE (Calificaciones por criterio)
-- ============================================================
CREATE TABLE IF NOT EXISTS evaluacion_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id INT NOT NULL,
    criterio_id INT NOT NULL,
    calificacion DECIMAL(4,2) NOT NULL,
    comentario TEXT,
    
    FOREIGN KEY (evaluacion_id) REFERENCES evaluacion_docente(id) ON DELETE CASCADE,
    FOREIGN KEY (criterio_id) REFERENCES criterio_evaluacion(id) ON DELETE CASCADE,
    
    INDEX idx_evaluacion (evaluacion_id),
    INDEX idx_criterio (criterio_id),
    
    UNIQUE KEY uk_evaluacion_criterio (evaluacion_id, criterio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. Vista para resumen de evaluaciones por docente
-- ============================================================
CREATE OR REPLACE VIEW vista_evaluaciones_docente AS
SELECT 
    d.id as docente_id,
    u.nombre as docente_nombre,
    u.email as docente_email,
    COUNT(ed.id) as total_evaluaciones,
    ROUND(AVG(ed.calificacion_global), 2) as promedio_global,
    MAX(ed.fecha_evaluacion) as ultima_evaluacion,
    SUM(CASE WHEN ed.tipo_evaluador = 'alumno' THEN 1 ELSE 0 END) as eval_alumnos,
    SUM(CASE WHEN ed.tipo_evaluador = 'par' THEN 1 ELSE 0 END) as eval_pares,
    SUM(CASE WHEN ed.tipo_evaluador = 'coordinador' THEN 1 ELSE 0 END) as eval_coordinadores
FROM docente d
INNER JOIN usuario u ON d.id_usuario = u.id
LEFT JOIN evaluacion_docente ed ON d.id = ed.docente_id AND ed.estatus = 'completada'
GROUP BY d.id, u.nombre, u.email;

-- ============================================================
-- 6. Vista para detalle de evaluaciones
-- ============================================================
CREATE OR REPLACE VIEW vista_evaluacion_completa AS
SELECT 
    ed.id,
    ed.docente_id,
    ud.nombre as docente_nombre,
    c.codigo as curso_codigo,
    c.nombre as curso_nombre,
    pe.nombre as periodo_nombre,
    ed.tipo_evaluador,
    ue.nombre as evaluador_nombre,
    ed.calificacion_global,
    ed.comentarios,
    ed.fortalezas,
    ed.areas_mejora,
    ed.recomendaciones,
    ed.fecha_evaluacion,
    ed.estatus
FROM evaluacion_docente ed
INNER JOIN docente d ON ed.docente_id = d.id
INNER JOIN usuario ud ON d.id_usuario = ud.id
LEFT JOIN curso c ON ed.curso_id = c.id
LEFT JOIN periodo_evaluacion pe ON ed.periodo_evaluacion_id = pe.id
LEFT JOIN usuario ue ON ed.evaluador_id = ue.id;

-- ============================================================
-- 7. Datos de ejemplo: Criterios de evaluación
-- ============================================================
INSERT INTO criterio_evaluacion (nombre, descripcion, categoria, peso, orden, activo) VALUES
-- Conocimiento
('Dominio del tema', 'El docente demuestra conocimiento profundo de la materia', 'conocimiento', 1.00, 1, TRUE),
('Actualización de contenidos', 'Incorpora información actualizada y relevante', 'conocimiento', 0.80, 2, TRUE),

-- Metodología
('Claridad en la explicación', 'Explica los temas de manera clara y comprensible', 'metodologia', 1.00, 3, TRUE),
('Uso de ejemplos prácticos', 'Utiliza ejemplos que facilitan la comprensión', 'metodologia', 0.80, 4, TRUE),
('Dinamismo en clase', 'Mantiene el interés y participación de los alumnos', 'metodologia', 0.70, 5, TRUE),
('Uso de tecnología', 'Aprovecha herramientas tecnológicas para la enseñanza', 'metodologia', 0.60, 6, TRUE),

-- Comunicación
('Disposición para resolver dudas', 'Atiende las preguntas con paciencia y claridad', 'comunicacion', 1.00, 7, TRUE),
('Comunicación efectiva', 'Se expresa de forma clara y respetuosa', 'comunicacion', 0.90, 8, TRUE),
('Retroalimentación oportuna', 'Proporciona retroalimentación útil y a tiempo', 'comunicacion', 0.80, 9, TRUE),

-- Puntualidad
('Puntualidad', 'Inicia y termina las clases a tiempo', 'puntualidad', 0.90, 10, TRUE),
('Cumplimiento del programa', 'Cubre los temas programados en el curso', 'puntualidad', 0.80, 11, TRUE),

-- Material
('Calidad del material', 'El material de apoyo es útil y de calidad', 'material', 0.80, 12, TRUE),
('Disponibilidad de recursos', 'Proporciona recursos adicionales de estudio', 'material', 0.60, 13, TRUE),

-- Evaluación
('Evaluaciones justas', 'Las evaluaciones reflejan lo enseñado en clase', 'evaluacion', 1.00, 14, TRUE),
('Criterios claros', 'Los criterios de evaluación son claros desde el inicio', 'evaluacion', 0.90, 15, TRUE);

-- ============================================================
-- 8. Datos de ejemplo: Períodos de evaluación
-- ============================================================
INSERT INTO periodo_evaluacion (nombre, fecha_inicio, fecha_fin, periodo_academico_id, estatus) VALUES
('Evaluación Primavera 2024', '2024-05-01', '2024-05-15', 1, 'cerrado'),
('Evaluación Otoño 2024', '2024-12-01', '2024-12-15', 2, 'cerrado'),
('Evaluación Primavera 2025', '2025-05-01', '2025-05-15', 3, 'programado');

-- ============================================================
-- 9. Datos de ejemplo: Evaluaciones
-- ============================================================
INSERT INTO evaluacion_docente (docente_id, curso_id, periodo_evaluacion_id, evaluador_id, tipo_evaluador, calificacion_global, comentarios, fortalezas, areas_mejora, estatus) VALUES
-- Evaluaciones para docente 1
(1, 1, 1, NULL, 'alumno', 9.2, 'Excelente docente, muy preparada', 'Dominio del tema, claridad', 'Más ejemplos prácticos', 'completada'),
(1, 1, 1, NULL, 'alumno', 8.8, 'Buena clase, explica bien', 'Paciencia, conocimiento', 'Puntualidad', 'completada'),
(1, 2, 2, NULL, 'alumno', 9.5, 'Una de las mejores profesoras', 'Todo excelente', 'Ninguna', 'completada'),
(1, NULL, 2, 9, 'coordinador', 9.0, 'Docente comprometida con la institución', 'Profesionalismo', 'Publicaciones', 'completada'),

-- Evaluaciones para docente 2
(2, 3, 1, NULL, 'alumno', 8.5, 'Buen profesor', 'Conocimiento técnico', 'Más dinámico', 'completada'),
(2, 3, 2, NULL, 'alumno', 8.7, 'Mejoró mucho este semestre', 'Ejemplos prácticos', 'Retroalimentación más rápida', 'completada'),

-- Evaluaciones para docente 3
(3, 6, 1, NULL, 'alumno', 7.8, 'Clase interesante', 'Temas actuales', 'Organización', 'completada'),
(3, 6, 2, NULL, 'alumno', 8.2, 'Ha mejorado bastante', 'Comunicación', 'Material de apoyo', 'completada'),

-- Evaluaciones para docente 7
(7, 5, 2, NULL, 'alumno', 9.8, 'Excelente en IA', 'Conocimiento de vanguardia', 'Ninguna', 'completada'),
(7, 5, 2, 9, 'coordinador', 9.5, 'Docente destacada en investigación', 'Innovación', 'Ninguna', 'completada');

-- ============================================================
-- 10. Datos de ejemplo: Detalles de evaluación (primeras 2)
-- ============================================================
INSERT INTO evaluacion_detalle (evaluacion_id, criterio_id, calificacion, comentario) VALUES
-- Evaluación 1 (docente 1, alumno)
(1, 1, 9.5, 'Excelente dominio'),
(1, 2, 9.0, NULL),
(1, 3, 9.5, 'Muy clara'),
(1, 4, 8.5, NULL),
(1, 7, 9.0, 'Siempre disponible'),
(1, 10, 9.0, NULL),
(1, 14, 9.5, 'Exámenes justos'),

-- Evaluación 2 (docente 1, alumno)
(2, 1, 9.0, NULL),
(2, 3, 9.0, NULL),
(2, 7, 8.5, NULL),
(2, 10, 8.0, 'A veces llega tarde'),
(2, 14, 9.0, NULL);
