<?php
/**
 * Modelo Reporte
 * Sistema de Gestión Académica
 * 
 * Maneja la generación de reportes y estadísticas del sistema
 */

class Reporte {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtener datos del dashboard principal
     * 
     * @return array Dashboard con todas las estadísticas
     */
    public function getDashboard() {
        $dashboard = [];
        
        // 1. Estadísticas generales usando la vista
        $sql = "SELECT * FROM vista_dashboard";
        $stmt = $this->pdo->query($sql);
        $dashboard['dashboard'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 2. Incidencias por estado
        $sql = "
            SELECT status, COUNT(*) as cantidad
            FROM incidencia
            GROUP BY status
            ORDER BY cantidad DESC
        ";
        $stmt = $this->pdo->query($sql);
        $dashboard['incidencias_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Incidencias por prioridad
        $sql = "
            SELECT prioridad, COUNT(*) as cantidad
            FROM incidencia
            GROUP BY prioridad
            ORDER BY 
                CASE prioridad
                    WHEN 'Alta' THEN 1
                    WHEN 'Media' THEN 2
                    WHEN 'Baja' THEN 3
                END
        ";
        $stmt = $this->pdo->query($sql);
        $dashboard['incidencias_por_prioridad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Docentes por estatus
        $sql = "
            SELECT estatus, COUNT(*) as cantidad
            FROM docente
            GROUP BY estatus
            ORDER BY cantidad DESC
        ";
        $stmt = $this->pdo->query($sql);
        $dashboard['docentes_por_estatus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $dashboard;
    }
    
    /**
     * Reporte de docentes por academia
     * 
     * @return array Agrupación de docentes por academia
     */
    public function getDocentesPorAcademia() {
        $sql = "
            SELECT 
                a.nombre as academia,
                COUNT(DISTINCT da.docente_id) as total_docentes,
                SUM(CASE WHEN d.estatus = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN d.sni = 1 THEN 1 ELSE 0 END) as con_sni
            FROM academia a
            LEFT JOIN docente_academia da ON a.id = da.academia_id
            LEFT JOIN docente d ON da.docente_id = d.id
            GROUP BY a.id, a.nombre
            ORDER BY total_docentes DESC
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reporte de incidencias por tipo
     * 
     * @return array Agrupación por tipo de incidencia
     */
    public function getIncidenciasPorTipo() {
        $sql = "
            SELECT 
                tipo,
                COUNT(*) as cantidad,
                SUM(CASE WHEN status = 'abierto' THEN 1 ELSE 0 END) as abiertas,
                SUM(CASE WHEN status = 'cerrado' THEN 1 ELSE 0 END) as cerradas
            FROM incidencia
            GROUP BY tipo
            ORDER BY cantidad DESC
            LIMIT 10
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reporte de docentes con más incidencias
     * 
     * @param int $limit Número de resultados a retornar
     * @return array Top docentes con incidencias
     */
    public function getDocentesConMasIncidencias($limit = 10) {
        $sql = "
            SELECT 
                u.nombre as docente,
                u.email,
                COUNT(i.id) as total_incidencias,
                SUM(CASE WHEN i.status = 'abierto' THEN 1 ELSE 0 END) as abiertas,
                SUM(CASE WHEN i.status = 'cerrado' THEN 1 ELSE 0 END) as cerradas
            FROM docente d
            INNER JOIN usuario u ON d.id_usuario = u.id
            LEFT JOIN incidencia i ON d.id = i.profesor
            GROUP BY d.id, u.nombre, u.email
            HAVING total_incidencias > 0
            ORDER BY total_incidencias DESC
            LIMIT :limit
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reporte de incidencias por rango de fechas
     * 
     * @param string $fechaInicio Fecha de inicio (YYYY-MM-DD)
     * @param string $fechaFin Fecha de fin (YYYY-MM-DD)
     * @return array Incidencias en el rango
     */
    public function getIncidenciasPorFecha($fechaInicio, $fechaFin) {
        $sql = "
            SELECT 
                DATE(fecha_creacion) as fecha,
                COUNT(*) as cantidad,
                SUM(CASE WHEN prioridad = 'Alta' THEN 1 ELSE 0 END) as alta,
                SUM(CASE WHEN prioridad = 'Media' THEN 1 ELSE 0 END) as media,
                SUM(CASE WHEN prioridad = 'Baja' THEN 1 ELSE 0 END) as baja
            FROM incidencia
            WHERE DATE(fecha_creacion) BETWEEN :fecha_inicio AND :fecha_fin
            GROUP BY DATE(fecha_creacion)
            ORDER BY fecha DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin' => $fechaFin
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reporte de distribución de grados académicos
     * 
     * @return array Distribución de docentes por grado
     */
    public function getDistribucionGrados() {
        $sql = "
            SELECT 
                CASE 
                    WHEN grados LIKE '%Doctorado%' THEN 'Doctorado'
                    WHEN grados LIKE '%Maestría%' THEN 'Maestría'
                    WHEN grados LIKE '%Licenciatura%' THEN 'Licenciatura'
                    ELSE 'Otro'
                END as grado,
                COUNT(*) as cantidad
            FROM docente
            WHERE estatus = 'activo'
            GROUP BY grado
            ORDER BY cantidad DESC
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reporte de idiomas de los docentes
     * 
     * @return array Distribución por idioma
     */
    public function getDistribucionIdiomas() {
        $sql = "
            SELECT 
                idioma,
                COUNT(*) as cantidad
            FROM docente
            WHERE estatus = 'activo' AND idioma IS NOT NULL AND idioma != ''
            GROUP BY idioma
            ORDER BY cantidad DESC
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Reporte de usuarios asignados con más incidencias
     * 
     * @param int $limit Número de resultados
     * @return array Top usuarios con incidencias asignadas
     */
    public function getUsuariosMasAsignaciones($limit = 10) {
        $sql = "
            SELECT 
                u.nombre,
                u.email,
                r.nombre as rol,
                COUNT(i.id) as total_asignadas,
                SUM(CASE WHEN i.status = 'abierto' THEN 1 ELSE 0 END) as abiertas,
                SUM(CASE WHEN i.status = 'en proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN i.status = 'cerrado' THEN 1 ELSE 0 END) as cerradas
            FROM usuario u
            LEFT JOIN rol r ON u.rol_id = r.id
            LEFT JOIN incidencia i ON u.id = i.asignadoA
            GROUP BY u.id, u.nombre, u.email, r.nombre
            HAVING total_asignadas > 0
            ORDER BY total_asignadas DESC
            LIMIT :limit
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Resumen ejecutivo del sistema
     * 
     * @return array Resumen completo
     */
    public function getResumenEjecutivo() {
        return [
            'dashboard' => $this->getDashboard(),
            'docentes_por_academia' => $this->getDocentesPorAcademia(),
            'incidencias_por_tipo' => $this->getIncidenciasPorTipo(),
            'distribucion_grados' => $this->getDistribucionGrados(),
            'distribucion_idiomas' => $this->getDistribucionIdiomas()
        ];
    }
}
