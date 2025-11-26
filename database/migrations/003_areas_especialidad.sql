-- ============================================================
-- Migración: Áreas de Especialidad para Docentes
-- Sistema de Gestión Académica
-- 
-- Agrega el catálogo de áreas de especialidad y su relación
-- con docentes (muchos a muchos)
-- 
-- PREREQUISITO: Ejecutar primero database/schema.sql
-- ============================================================

-- Usar la base de datos correcta
USE gestion_academica;

-- 1. Crear tabla catálogo de áreas de especialidad
CREATE TABLE IF NOT EXISTS area_especialidad (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar áreas de especialidad comunes en entornos académicos
INSERT IGNORE INTO area_especialidad (nombre, descripcion) VALUES
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

-- 3. Crear tabla de relación docente-área (muchos a muchos)
-- Primero crear la tabla sin FKs para evitar errores si docente no existe
CREATE TABLE IF NOT EXISTS docente_area_especialidad (
    id INT PRIMARY KEY AUTO_INCREMENT,
    docente_id INT NOT NULL,
    area_id INT NOT NULL,
    nivel ENUM('básico', 'intermedio', 'avanzado', 'experto') DEFAULT 'intermedio',
    anios_experiencia INT DEFAULT 0,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_docente_area (docente_id, area_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3b. Agregar foreign keys (solo si las tablas referenciadas existen)
-- FK hacia docente
ALTER TABLE docente_area_especialidad
    ADD CONSTRAINT fk_dae_docente 
    FOREIGN KEY (docente_id) REFERENCES docente(id) ON DELETE CASCADE;

-- FK hacia area_especialidad
ALTER TABLE docente_area_especialidad
    ADD CONSTRAINT fk_dae_area 
    FOREIGN KEY (area_id) REFERENCES area_especialidad(id) ON DELETE CASCADE;

-- 4. Crear índices para mejorar performance
CREATE INDEX idx_area_especialidad_nombre ON area_especialidad(nombre);
CREATE INDEX idx_area_especialidad_activo ON area_especialidad(activo);
CREATE INDEX idx_docente_area_docente ON docente_area_especialidad(docente_id);
CREATE INDEX idx_docente_area_area ON docente_area_especialidad(area_id);
CREATE INDEX idx_docente_area_nivel ON docente_area_especialidad(nivel);

-- 5. Asignar algunas áreas a docentes existentes (datos de ejemplo)
-- Docente 1 (Ana López - Doctorado en Educación)
INSERT IGNORE INTO docente_area_especialidad (docente_id, area_id, nivel, anios_experiencia) VALUES
(1, 8, 'experto', 10),   -- Educación
(1, 9, 'avanzado', 8);   -- Investigación

-- Docente 2 (Carlos Jiménez - Maestría en Sistemas)
INSERT IGNORE INTO docente_area_especialidad (docente_id, area_id, nivel, anios_experiencia) VALUES
(2, 2, 'avanzado', 6),   -- Desarrollo de Software
(2, 3, 'intermedio', 4); -- Bases de Datos

-- Docente 3 (Sofía Torres - Licenciatura en Psicología)
INSERT IGNORE INTO docente_area_especialidad (docente_id, area_id, nivel, anios_experiencia) VALUES
(3, 15, 'avanzado', 5),  -- Psicología Organizacional
(3, 8, 'intermedio', 3); -- Educación

-- Docente 4 (Marco Hernández - Doctorado en Comunicación)
INSERT IGNORE INTO docente_area_especialidad (docente_id, area_id, nivel, anios_experiencia) VALUES
(4, 16, 'experto', 12),  -- Comunicación
(4, 12, 'avanzado', 7);  -- Marketing Digital

-- Docente 7 (Laura Martínez - Doctorado en Ingeniería)
INSERT IGNORE INTO docente_area_especialidad (docente_id, area_id, nivel, anios_experiencia) VALUES
(7, 1, 'experto', 8),    -- Inteligencia Artificial
(7, 6, 'avanzado', 6),   -- Ciencia de Datos
(7, 2, 'intermedio', 4); -- Desarrollo de Software

-- ============================================================
-- Verificación de migración
-- ============================================================
SELECT 
    'Áreas de especialidad creadas:' as info,
    COUNT(*) as total 
FROM area_especialidad;

SELECT 
    'Asignaciones docente-área creadas:' as info,
    COUNT(*) as total 
FROM docente_area_especialidad;

SELECT 
    ae.nombre as area,
    COUNT(dae.docente_id) as docentes_asignados
FROM area_especialidad ae
LEFT JOIN docente_area_especialidad dae ON ae.id = dae.area_id
GROUP BY ae.id, ae.nombre
HAVING docentes_asignados > 0
ORDER BY docentes_asignados DESC;
