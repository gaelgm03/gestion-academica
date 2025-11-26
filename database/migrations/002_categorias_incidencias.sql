-- ============================================================
-- Migración: Categorías Predefinidas de Incidencias
-- Sistema de Gestión Académica
-- 
-- Convierte el campo 'tipo' de VARCHAR a FK hacia tabla catálogo
-- ============================================================

-- 1. Crear tabla de tipos de incidencia
CREATE TABLE IF NOT EXISTS tipo_incidencia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar las 5 categorías especificadas en los requisitos (ignorar si ya existen)
INSERT IGNORE INTO tipo_incidencia (nombre, descripcion, orden) VALUES
('Cambio de calificación', 'Solicitud de modificación de calificación en el sistema', 1),
('Cambio de fecha de examen', 'Solicitud de reprogramación de fecha de examen', 2),
('Integridad académica', 'Reporte de violación a la integridad académica (plagio, fraude, etc.)', 3),
('Reporte disciplinar a profesor', 'Reporte de conducta inapropiada o falta disciplinaria del profesor', 4),
('Incidencia de pago', 'Incidencia relacionada con pagos (a favor o en contra del docente)', 5);

-- 3. Agregar columna temporal para el nuevo tipo_id (si no existe)
SET @dbname = DATABASE();
SET @tablename = "incidencia";
SET @columnname = "tipo_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE incidencia ADD COLUMN tipo_id INT NULL AFTER tipo"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 4. Migrar datos existentes: mapear tipos de texto a IDs
-- Intentar hacer match inteligente con LIKE para datos existentes
UPDATE incidencia i 
SET tipo_id = (
    SELECT ti.id 
    FROM tipo_incidencia ti 
    WHERE LOWER(i.tipo) LIKE CONCAT('%', LOWER(SUBSTRING_INDEX(ti.nombre, ' ', 2)), '%')
    LIMIT 1
)
WHERE i.tipo IS NOT NULL;

-- 5. Para registros que no pudieron mapearse, asignarlos a "Cambio de calificación" (el más común)
UPDATE incidencia 
SET tipo_id = 1 
WHERE tipo_id IS NULL AND tipo IS NOT NULL;

-- 6. Renombrar columna antigua y hacer la nueva obligatoria
ALTER TABLE incidencia 
    CHANGE COLUMN tipo tipo_old VARCHAR(200) NULL,
    MODIFY COLUMN tipo_id INT NOT NULL;

-- 7. Agregar foreign key
ALTER TABLE incidencia 
    ADD CONSTRAINT fk_incidencia_tipo 
    FOREIGN KEY (tipo_id) REFERENCES tipo_incidencia(id)
    ON DELETE RESTRICT 
    ON UPDATE CASCADE;

-- 8. Crear índice para mejorar performance
CREATE INDEX idx_incidencia_tipo ON incidencia(tipo_id);

-- ============================================================
-- NOTA: La columna 'tipo_old' se mantiene temporalmente
-- para referencia. Puede eliminarse después de verificar
-- que la migración fue exitosa con:
-- ALTER TABLE incidencia DROP COLUMN tipo_old;
-- ============================================================

-- Verificación de migración
SELECT 
    'Tipos de incidencia creados:' as info,
    COUNT(*) as total 
FROM tipo_incidencia;

SELECT 
    'Incidencias migradas correctamente:' as info,
    COUNT(*) as total 
FROM incidencia 
WHERE tipo_id IS NOT NULL;

SELECT 
    ti.nombre as tipo,
    COUNT(i.id) as cantidad
FROM tipo_incidencia ti
LEFT JOIN incidencia i ON ti.id = i.tipo_id
GROUP BY ti.id, ti.nombre
ORDER BY ti.orden;
