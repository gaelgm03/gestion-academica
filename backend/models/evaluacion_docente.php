<?php
/**
 * Modelo EvaluacionDocente
 * Sistema de Gestión Académica
 * 
 * Maneja todas las operaciones relacionadas con evaluaciones docentes
 */

class EvaluacionDocente {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtener todas las evaluaciones con filtros
     * 
     * @param array $filters Filtros opcionales
     * @return array Lista de evaluaciones
     */
    public function getAll($filters = []) {
        $sql = "
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
                ed.fecha_evaluacion,
                ed.estatus
            FROM evaluacion_docente ed
            INNER JOIN docente d ON ed.docente_id = d.id
            INNER JOIN usuario ud ON d.id_usuario = ud.id
            LEFT JOIN curso c ON ed.curso_id = c.id
            LEFT JOIN periodo_evaluacion pe ON ed.periodo_evaluacion_id = pe.id
            LEFT JOIN usuario ue ON ed.evaluador_id = ue.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($filters['docente_id']) && $filters['docente_id'] !== '') {
            $sql .= " AND ed.docente_id = :docente_id";
            $params[':docente_id'] = (int)$filters['docente_id'];
        }
        
        if (isset($filters['curso_id']) && $filters['curso_id'] !== '') {
            $sql .= " AND ed.curso_id = :curso_id";
            $params[':curso_id'] = (int)$filters['curso_id'];
        }
        
        if (isset($filters['periodo_id']) && $filters['periodo_id'] !== '') {
            $sql .= " AND ed.periodo_evaluacion_id = :periodo_id";
            $params[':periodo_id'] = (int)$filters['periodo_id'];
        }
        
        if (isset($filters['tipo_evaluador']) && $filters['tipo_evaluador'] !== '') {
            $sql .= " AND ed.tipo_evaluador = :tipo_evaluador";
            $params[':tipo_evaluador'] = $filters['tipo_evaluador'];
        }
        
        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $sql .= " AND ed.estatus = :estatus";
            $params[':estatus'] = $filters['estatus'];
        }
        
        $sql .= " ORDER BY ed.fecha_evaluacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una evaluación por ID
     * 
     * @param int $id ID de la evaluación
     * @return array|false Datos de la evaluación
     */
    public function getById($id) {
        $sql = "
            SELECT 
                ed.*,
                ud.nombre as docente_nombre,
                ud.email as docente_email,
                c.codigo as curso_codigo,
                c.nombre as curso_nombre,
                pe.nombre as periodo_nombre,
                ue.nombre as evaluador_nombre
            FROM evaluacion_docente ed
            INNER JOIN docente d ON ed.docente_id = d.id
            INNER JOIN usuario ud ON d.id_usuario = ud.id
            LEFT JOIN curso c ON ed.curso_id = c.id
            LEFT JOIN periodo_evaluacion pe ON ed.periodo_evaluacion_id = pe.id
            LEFT JOIN usuario ue ON ed.evaluador_id = ue.id
            WHERE ed.id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $evaluacion = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($evaluacion) {
            $evaluacion['detalles'] = $this->getDetalles($id);
        }
        
        return $evaluacion;
    }
    
    /**
     * Obtener detalles de una evaluación (calificaciones por criterio)
     * 
     * @param int $evaluacionId ID de la evaluación
     * @return array Detalles de la evaluación
     */
    public function getDetalles($evaluacionId) {
        $sql = "
            SELECT 
                edt.id,
                edt.criterio_id,
                ce.nombre as criterio_nombre,
                ce.categoria,
                ce.peso,
                edt.calificacion,
                edt.comentario
            FROM evaluacion_detalle edt
            INNER JOIN criterio_evaluacion ce ON edt.criterio_id = ce.id
            WHERE edt.evaluacion_id = :evaluacion_id
            ORDER BY ce.orden ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':evaluacion_id' => $evaluacionId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear una nueva evaluación
     * 
     * @param array $data Datos de la evaluación
     * @return int ID de la evaluación creada
     */
    public function create($data) {
        $this->pdo->beginTransaction();
        
        try {
            // Insertar evaluación principal
            $sql = "
                INSERT INTO evaluacion_docente 
                (docente_id, curso_id, periodo_evaluacion_id, evaluador_id, tipo_evaluador, 
                 calificacion_global, comentarios, fortalezas, areas_mejora, recomendaciones, estatus)
                VALUES 
                (:docente_id, :curso_id, :periodo_id, :evaluador_id, :tipo_evaluador,
                 :calificacion_global, :comentarios, :fortalezas, :areas_mejora, :recomendaciones, :estatus)
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':docente_id' => $data['docente_id'],
                ':curso_id' => $data['curso_id'] ?? null,
                ':periodo_id' => $data['periodo_evaluacion_id'] ?? null,
                ':evaluador_id' => $data['evaluador_id'] ?? null,
                ':tipo_evaluador' => $data['tipo_evaluador'] ?? 'alumno',
                ':calificacion_global' => $data['calificacion_global'] ?? null,
                ':comentarios' => $data['comentarios'] ?? '',
                ':fortalezas' => $data['fortalezas'] ?? '',
                ':areas_mejora' => $data['areas_mejora'] ?? '',
                ':recomendaciones' => $data['recomendaciones'] ?? '',
                ':estatus' => $data['estatus'] ?? 'borrador'
            ]);
            
            $evaluacionId = $this->pdo->lastInsertId();
            
            // Insertar detalles si se proporcionan
            if (isset($data['detalles']) && is_array($data['detalles'])) {
                foreach ($data['detalles'] as $detalle) {
                    $this->addDetalle($evaluacionId, $detalle);
                }
                
                // Calcular calificación global ponderada si no se proporcionó
                if (empty($data['calificacion_global'])) {
                    $this->recalcularGlobal($evaluacionId);
                }
            }
            
            $this->pdo->commit();
            return $evaluacionId;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Agregar detalle de evaluación
     * 
     * @param int $evaluacionId ID de la evaluación
     * @param array $detalle Datos del detalle
     * @return int ID del detalle
     */
    public function addDetalle($evaluacionId, $detalle) {
        $sql = "
            INSERT INTO evaluacion_detalle (evaluacion_id, criterio_id, calificacion, comentario)
            VALUES (:evaluacion_id, :criterio_id, :calificacion, :comentario)
            ON DUPLICATE KEY UPDATE calificacion = :calificacion2, comentario = :comentario2
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':evaluacion_id' => $evaluacionId,
            ':criterio_id' => $detalle['criterio_id'],
            ':calificacion' => $detalle['calificacion'],
            ':comentario' => $detalle['comentario'] ?? null,
            ':calificacion2' => $detalle['calificacion'],
            ':comentario2' => $detalle['comentario'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Recalcular calificación global ponderada
     * 
     * @param int $evaluacionId ID de la evaluación
     */
    public function recalcularGlobal($evaluacionId) {
        $sql = "
            UPDATE evaluacion_docente ed
            SET calificacion_global = (
                SELECT ROUND(SUM(edt.calificacion * ce.peso) / SUM(ce.peso), 2)
                FROM evaluacion_detalle edt
                INNER JOIN criterio_evaluacion ce ON edt.criterio_id = ce.id
                WHERE edt.evaluacion_id = :evaluacion_id
            )
            WHERE ed.id = :evaluacion_id2
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':evaluacion_id' => $evaluacionId,
            ':evaluacion_id2' => $evaluacionId
        ]);
    }
    
    /**
     * Actualizar evaluación
     * 
     * @param int $id ID de la evaluación
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó
     */
    public function update($id, $data) {
        $sql = "
            UPDATE evaluacion_docente SET
                calificacion_global = :calificacion_global,
                comentarios = :comentarios,
                fortalezas = :fortalezas,
                areas_mejora = :areas_mejora,
                recomendaciones = :recomendaciones,
                estatus = :estatus
            WHERE id = :id
        ";
        
        $evaluacion = $this->getById($id);
        if (!$evaluacion) {
            throw new Exception("Evaluación no encontrada");
        }
        
        $stmt = $this->pdo->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':calificacion_global' => $data['calificacion_global'] ?? $evaluacion['calificacion_global'],
            ':comentarios' => $data['comentarios'] ?? $evaluacion['comentarios'],
            ':fortalezas' => $data['fortalezas'] ?? $evaluacion['fortalezas'],
            ':areas_mejora' => $data['areas_mejora'] ?? $evaluacion['areas_mejora'],
            ':recomendaciones' => $data['recomendaciones'] ?? $evaluacion['recomendaciones'],
            ':estatus' => $data['estatus'] ?? $evaluacion['estatus']
        ]);
        
        // Actualizar detalles si se proporcionan
        if (isset($data['detalles']) && is_array($data['detalles'])) {
            foreach ($data['detalles'] as $detalle) {
                $this->addDetalle($id, $detalle);
            }
            $this->recalcularGlobal($id);
        }
        
        return $result;
    }
    
    /**
     * Eliminar evaluación
     * 
     * @param int $id ID de la evaluación
     * @return bool True si se eliminó
     */
    public function delete($id) {
        $sql = "DELETE FROM evaluacion_docente WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Obtener resumen de evaluaciones de un docente
     * 
     * @param int $docenteId ID del docente
     * @return array Resumen de evaluaciones
     */
    public function getResumenDocente($docenteId) {
        $sql = "
            SELECT 
                COUNT(*) as total_evaluaciones,
                ROUND(AVG(calificacion_global), 2) as promedio_global,
                MIN(calificacion_global) as calificacion_minima,
                MAX(calificacion_global) as calificacion_maxima,
                SUM(CASE WHEN tipo_evaluador = 'alumno' THEN 1 ELSE 0 END) as eval_alumnos,
                SUM(CASE WHEN tipo_evaluador = 'par' THEN 1 ELSE 0 END) as eval_pares,
                SUM(CASE WHEN tipo_evaluador = 'coordinador' THEN 1 ELSE 0 END) as eval_coordinadores,
                SUM(CASE WHEN tipo_evaluador = 'autoevaluacion' THEN 1 ELSE 0 END) as autoevaluaciones
            FROM evaluacion_docente
            WHERE docente_id = :docente_id AND estatus = 'completada'
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':docente_id' => $docenteId]);
        
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Obtener promedio por criterio
        $resumen['promedios_criterios'] = $this->getPromediosPorCriterio($docenteId);
        
        // Obtener evolución temporal
        $resumen['evolucion'] = $this->getEvolucionTemporal($docenteId);
        
        return $resumen;
    }
    
    /**
     * Obtener promedios por criterio de un docente
     * 
     * @param int $docenteId ID del docente
     * @return array Promedios por criterio
     */
    public function getPromediosPorCriterio($docenteId) {
        $sql = "
            SELECT 
                ce.id as criterio_id,
                ce.nombre as criterio,
                ce.categoria,
                ROUND(AVG(edt.calificacion), 2) as promedio,
                COUNT(*) as total_respuestas
            FROM evaluacion_detalle edt
            INNER JOIN criterio_evaluacion ce ON edt.criterio_id = ce.id
            INNER JOIN evaluacion_docente ed ON edt.evaluacion_id = ed.id
            WHERE ed.docente_id = :docente_id AND ed.estatus = 'completada'
            GROUP BY ce.id, ce.nombre, ce.categoria
            ORDER BY ce.orden ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':docente_id' => $docenteId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener evolución temporal de evaluaciones
     * 
     * @param int $docenteId ID del docente
     * @return array Evolución por período
     */
    public function getEvolucionTemporal($docenteId) {
        $sql = "
            SELECT 
                pe.nombre as periodo,
                ROUND(AVG(ed.calificacion_global), 2) as promedio,
                COUNT(*) as total_evaluaciones
            FROM evaluacion_docente ed
            INNER JOIN periodo_evaluacion pe ON ed.periodo_evaluacion_id = pe.id
            WHERE ed.docente_id = :docente_id AND ed.estatus = 'completada'
            GROUP BY pe.id, pe.nombre
            ORDER BY pe.fecha_inicio ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':docente_id' => $docenteId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener criterios de evaluación activos
     * 
     * @return array Lista de criterios
     */
    public function getCriterios() {
        $sql = "
            SELECT id, nombre, descripcion, categoria, peso, orden
            FROM criterio_evaluacion
            WHERE activo = TRUE
            ORDER BY orden ASC
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener períodos de evaluación
     * 
     * @param string|null $estatus Filtrar por estatus
     * @return array Lista de períodos
     */
    public function getPeriodos($estatus = null) {
        $sql = "SELECT * FROM periodo_evaluacion";
        $params = [];
        
        if ($estatus) {
            $sql .= " WHERE estatus = :estatus";
            $params[':estatus'] = $estatus;
        }
        
        $sql .= " ORDER BY fecha_inicio DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas generales de evaluaciones
     * 
     * @return array Estadísticas
     */
    public function getStats() {
        $stats = [];
        
        // Total evaluaciones
        $sql = "SELECT COUNT(*) as total FROM evaluacion_docente WHERE estatus = 'completada'";
        $stmt = $this->pdo->query($sql);
        $stats['total_evaluaciones'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Promedio general
        $sql = "SELECT ROUND(AVG(calificacion_global), 2) as promedio FROM evaluacion_docente WHERE estatus = 'completada'";
        $stmt = $this->pdo->query($sql);
        $stats['promedio_general'] = (float)$stmt->fetch(PDO::FETCH_ASSOC)['promedio'];
        
        // Por tipo de evaluador
        $sql = "
            SELECT tipo_evaluador, COUNT(*) as cantidad, ROUND(AVG(calificacion_global), 2) as promedio
            FROM evaluacion_docente 
            WHERE estatus = 'completada'
            GROUP BY tipo_evaluador
        ";
        $stmt = $this->pdo->query($sql);
        $stats['por_tipo_evaluador'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Top 5 docentes mejor evaluados
        $sql = "
            SELECT 
                u.nombre as docente,
                ROUND(AVG(ed.calificacion_global), 2) as promedio,
                COUNT(*) as total_evaluaciones
            FROM evaluacion_docente ed
            INNER JOIN docente d ON ed.docente_id = d.id
            INNER JOIN usuario u ON d.id_usuario = u.id
            WHERE ed.estatus = 'completada'
            GROUP BY d.id, u.nombre
            ORDER BY promedio DESC
            LIMIT 5
        ";
        $stmt = $this->pdo->query($sql);
        $stats['top_docentes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Distribución por rango de calificación
        $sql = "
            SELECT 
                CASE 
                    WHEN calificacion_global >= 9 THEN 'Excelente (9-10)'
                    WHEN calificacion_global >= 8 THEN 'Muy Bueno (8-9)'
                    WHEN calificacion_global >= 7 THEN 'Bueno (7-8)'
                    WHEN calificacion_global >= 6 THEN 'Regular (6-7)'
                    ELSE 'Bajo (<6)'
                END as rango,
                COUNT(*) as cantidad
            FROM evaluacion_docente
            WHERE estatus = 'completada'
            GROUP BY rango
            ORDER BY MIN(calificacion_global) DESC
        ";
        $stmt = $this->pdo->query($sql);
        $stats['distribucion_calificaciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
}
