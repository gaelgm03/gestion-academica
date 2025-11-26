-- ============================================================
-- Migración 004: Tabla incidencia_historial para Auditoría
-- Sistema de Gestión Académica
-- ============================================================

-- Crear tabla de historial de incidencias
CREATE TABLE IF NOT EXISTS incidencia_historial (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL,
    usuario_id INT NOT NULL,
    campo_modificado VARCHAR(50) NOT NULL,
    valor_anterior TEXT,
    valor_nuevo TEXT,
    accion ENUM('crear', 'editar', 'eliminar', 'cambio_status', 'asignar') NOT NULL DEFAULT 'editar',
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    
    -- Foreign keys
    FOREIGN KEY (incidencia_id) REFERENCES incidencia(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE RESTRICT,
    
    -- Índices para búsquedas rápidas
    INDEX idx_incidencia (incidencia_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_cambio),
    INDEX idx_accion (accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Datos de prueba (opcional)
-- ============================================================

-- Insertar algunos registros de ejemplo si existen incidencias
INSERT INTO incidencia_historial (incidencia_id, usuario_id, campo_modificado, valor_anterior, valor_nuevo, accion)
SELECT 
    i.id,
    1,
    'status',
    NULL,
    i.status,
    'crear'
FROM incidencia i
WHERE i.id <= 5
ON DUPLICATE KEY UPDATE id = id;

-- ============================================================
-- Vista para consultar historial con nombres
-- ============================================================

CREATE OR REPLACE VIEW vista_incidencia_historial AS
SELECT 
    ih.id,
    ih.incidencia_id,
    ih.campo_modificado,
    ih.valor_anterior,
    ih.valor_nuevo,
    ih.accion,
    ih.fecha_cambio,
    ih.ip_address,
    u.nombre as usuario_nombre,
    u.email as usuario_email,
    ti.nombre as tipo_incidencia
FROM incidencia_historial ih
INNER JOIN usuario u ON ih.usuario_id = u.id
LEFT JOIN incidencia i ON ih.incidencia_id = i.id
LEFT JOIN tipo_incidencia ti ON i.tipo_id = ti.id
ORDER BY ih.fecha_cambio DESC;
