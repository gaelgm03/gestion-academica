<?php
/**
 * Modelo Reporte
 * Sistema de Gestión Académica
 * 
 * Maneja la generación de reportes y estadísticas del sistema
 */

class Reporte {
    private $pdo;
    
    // Períodos disponibles
    const PERIODO_HOY = 'hoy';
    const PERIODO_AYER = 'ayer';
    const PERIODO_ESTA_SEMANA = 'esta_semana';
    const PERIODO_SEMANA_PASADA = 'semana_pasada';
    const PERIODO_ESTE_MES = 'este_mes';
    const PERIODO_MES_PASADO = 'mes_pasado';
    const PERIODO_ESTE_ANIO = 'este_anio';
    const PERIODO_ULTIMOS_7_DIAS = 'ultimos_7_dias';
    const PERIODO_ULTIMOS_30_DIAS = 'ultimos_30_dias';
    const PERIODO_ULTIMOS_90_DIAS = 'ultimos_90_dias';
    const PERIODO_TODO = 'todo';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Convertir período a rango de fechas
     * 
     * @param string $periodo Código del período
     * @param string|null $fechaInicio Fecha inicio personalizada
     * @param string|null $fechaFin Fecha fin personalizada
     * @return array ['inicio' => date, 'fin' => date]
     */
    public function getPeriodoFechas($periodo, $fechaInicio = null, $fechaFin = null) {
        $hoy = new DateTime();
        $inicio = null;
        $fin = $hoy->format('Y-m-d');
        
        switch ($periodo) {
            case self::PERIODO_HOY:
                $inicio = $hoy->format('Y-m-d');
                break;
                
            case self::PERIODO_AYER:
                $ayer = (clone $hoy)->modify('-1 day');
                $inicio = $ayer->format('Y-m-d');
                $fin = $ayer->format('Y-m-d');
                break;
                
            case self::PERIODO_ESTA_SEMANA:
                $inicioSemana = (clone $hoy)->modify('monday this week');
                $inicio = $inicioSemana->format('Y-m-d');
                break;
                
            case self::PERIODO_SEMANA_PASADA:
                $inicioSemana = (clone $hoy)->modify('monday last week');
                $finSemana = (clone $hoy)->modify('sunday last week');
                $inicio = $inicioSemana->format('Y-m-d');
                $fin = $finSemana->format('Y-m-d');
                break;
                
            case self::PERIODO_ESTE_MES:
                $inicio = $hoy->format('Y-m-01');
                break;
                
            case self::PERIODO_MES_PASADO:
                $mesAnterior = (clone $hoy)->modify('first day of last month');
                $finMesAnterior = (clone $hoy)->modify('last day of last month');
                $inicio = $mesAnterior->format('Y-m-d');
                $fin = $finMesAnterior->format('Y-m-d');
                break;
                
            case self::PERIODO_ESTE_ANIO:
                $inicio = $hoy->format('Y-01-01');
                break;
                
            case self::PERIODO_ULTIMOS_7_DIAS:
                $inicio = (clone $hoy)->modify('-7 days')->format('Y-m-d');
                break;
                
            case self::PERIODO_ULTIMOS_30_DIAS:
                $inicio = (clone $hoy)->modify('-30 days')->format('Y-m-d');
                break;
                
            case self::PERIODO_ULTIMOS_90_DIAS:
                $inicio = (clone $hoy)->modify('-90 days')->format('Y-m-d');
                break;
                
            case 'personalizado':
                $inicio = $fechaInicio ?: $hoy->format('Y-m-01');
                $fin = $fechaFin ?: $hoy->format('Y-m-d');
                break;
                
            case self::PERIODO_TODO:
            default:
                $inicio = '2000-01-01';
                break;
        }
        
        return [
            'inicio' => $inicio,
            'fin' => $fin,
            'periodo' => $periodo,
            'descripcion' => $this->getDescripcionPeriodo($periodo, $inicio, $fin)
        ];
    }
    
    /**
     * Obtener descripción legible del período
     */
    private function getDescripcionPeriodo($periodo, $inicio, $fin) {
        $descripciones = [
            'hoy' => 'Hoy',
            'ayer' => 'Ayer',
            'esta_semana' => 'Esta semana',
            'semana_pasada' => 'Semana pasada',
            'este_mes' => 'Este mes',
            'mes_pasado' => 'Mes pasado',
            'este_anio' => 'Este año',
            'ultimos_7_dias' => 'Últimos 7 días',
            'ultimos_30_dias' => 'Últimos 30 días',
            'ultimos_90_dias' => 'Últimos 90 días',
            'todo' => 'Todo el historial'
        ];
        
        if ($periodo === 'personalizado') {
            return "Del $inicio al $fin";
        }
        
        return $descripciones[$periodo] ?? "Del $inicio al $fin";
    }
    
    /**
     * Obtener lista de períodos disponibles
     */
    public function getPeriodosDisponibles() {
        return [
            ['id' => 'hoy', 'nombre' => 'Hoy'],
            ['id' => 'ayer', 'nombre' => 'Ayer'],
            ['id' => 'esta_semana', 'nombre' => 'Esta semana'],
            ['id' => 'semana_pasada', 'nombre' => 'Semana pasada'],
            ['id' => 'este_mes', 'nombre' => 'Este mes'],
            ['id' => 'mes_pasado', 'nombre' => 'Mes pasado'],
            ['id' => 'ultimos_7_dias', 'nombre' => 'Últimos 7 días'],
            ['id' => 'ultimos_30_dias', 'nombre' => 'Últimos 30 días'],
            ['id' => 'ultimos_90_dias', 'nombre' => 'Últimos 90 días'],
            ['id' => 'este_anio', 'nombre' => 'Este año'],
            ['id' => 'todo', 'nombre' => 'Todo'],
            ['id' => 'personalizado', 'nombre' => 'Personalizado']
        ];
    }
    
    /**
     * Obtener datos del dashboard principal con filtro de período
     * 
     * @param string $periodo Código del período
     * @param string|null $fechaInicio Fecha inicio (para período personalizado)
     * @param string|null $fechaFin Fecha fin (para período personalizado)
     * @return array Dashboard con todas las estadísticas
     */
    public function getDashboard($periodo = 'todo', $fechaInicio = null, $fechaFin = null) {
        $dashboard = [];
        $fechas = $this->getPeriodoFechas($periodo, $fechaInicio, $fechaFin);
        $dashboard['periodo'] = $fechas;
        
        // 1. Estadísticas generales (docentes no se filtran por fecha)
        $sql = "SELECT * FROM vista_dashboard";
        $stmt = $this->pdo->query($sql);
        $dashboard['dashboard'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 2. Incidencias por estado (filtrado por período)
        $sql = "
            SELECT status, COUNT(*) as cantidad
            FROM incidencia
            WHERE DATE(fecha_creacion) BETWEEN :inicio AND :fin
            GROUP BY status
            ORDER BY cantidad DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inicio' => $fechas['inicio'], ':fin' => $fechas['fin']]);
        $dashboard['incidencias_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Incidencias por prioridad (filtrado por período)
        $sql = "
            SELECT prioridad, COUNT(*) as cantidad
            FROM incidencia
            WHERE DATE(fecha_creacion) BETWEEN :inicio AND :fin
            GROUP BY prioridad
            ORDER BY 
                CASE prioridad
                    WHEN 'Alta' THEN 1
                    WHEN 'Media' THEN 2
                    WHEN 'Baja' THEN 3
                END
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inicio' => $fechas['inicio'], ':fin' => $fechas['fin']]);
        $dashboard['incidencias_por_prioridad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Docentes por estatus (no se filtra por fecha)
        $sql = "
            SELECT estatus, COUNT(*) as cantidad
            FROM docente
            GROUP BY estatus
            ORDER BY cantidad DESC
        ";
        $stmt = $this->pdo->query($sql);
        $dashboard['docentes_por_estatus'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 5. Conteo de incidencias en el período
        $sql = "
            SELECT COUNT(*) as total_periodo
            FROM incidencia
            WHERE DATE(fecha_creacion) BETWEEN :inicio AND :fin
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inicio' => $fechas['inicio'], ':fin' => $fechas['fin']]);
        $dashboard['incidencias_periodo'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_periodo'];
        
        // 6. Incidencias por día (para gráfica de tendencia)
        $sql = "
            SELECT 
                DATE(fecha_creacion) as fecha,
                COUNT(*) as cantidad
            FROM incidencia
            WHERE DATE(fecha_creacion) BETWEEN :inicio AND :fin
            GROUP BY DATE(fecha_creacion)
            ORDER BY fecha ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inicio' => $fechas['inicio'], ':fin' => $fechas['fin']]);
        $dashboard['tendencia_diaria'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
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
    
    // ========================================================================
    // REPORTE POR MATERIA (Requisito MVP)
    // ========================================================================
    
    /**
     * Reporte de estadísticas por materia/curso
     * Incluye: incidencias, docentes asignados, evaluaciones promedio
     * 
     * @param string $periodo Código del período
     * @param string|null $fechaInicio Fecha inicio personalizada
     * @param string|null $fechaFin Fecha fin personalizada
     * @return array Estadísticas agrupadas por materia
     */
    public function getReportePorMateria($periodo = 'todo', $fechaInicio = null, $fechaFin = null) {
        $fechas = $this->getPeriodoFechas($periodo, $fechaInicio, $fechaFin);
        
        // 1. Estadísticas generales de cursos
        $sqlResumen = "
            SELECT 
                COUNT(*) as total_cursos,
                SUM(CASE WHEN estatus = 'activo' THEN 1 ELSE 0 END) as cursos_activos,
                SUM(CASE WHEN estatus = 'inactivo' THEN 1 ELSE 0 END) as cursos_inactivos
            FROM curso
        ";
        $stmt = $this->pdo->query($sqlResumen);
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 2. Cursos con más incidencias
        $sqlIncidencias = "
            SELECT 
                c.id,
                c.codigo,
                c.nombre as materia,
                a.nombre as academia,
                c.semestre,
                c.modalidad,
                COUNT(i.id) as total_incidencias,
                SUM(CASE WHEN i.status = 'abierto' THEN 1 ELSE 0 END) as incidencias_abiertas,
                SUM(CASE WHEN i.status = 'en proceso' THEN 1 ELSE 0 END) as incidencias_en_proceso,
                SUM(CASE WHEN i.status = 'cerrado' THEN 1 ELSE 0 END) as incidencias_cerradas,
                SUM(CASE WHEN i.prioridad = 'Alta' THEN 1 ELSE 0 END) as prioridad_alta,
                SUM(CASE WHEN i.prioridad = 'Media' THEN 1 ELSE 0 END) as prioridad_media,
                SUM(CASE WHEN i.prioridad = 'Baja' THEN 1 ELSE 0 END) as prioridad_baja
            FROM curso c
            LEFT JOIN academia a ON c.academia_id = a.id
            LEFT JOIN incidencia i ON (c.id = i.curso_id OR c.nombre = i.curso)
                AND DATE(i.fecha_creacion) BETWEEN :inicio AND :fin
            WHERE c.estatus = 'activo'
            GROUP BY c.id, c.codigo, c.nombre, a.nombre, c.semestre, c.modalidad
            ORDER BY total_incidencias DESC
            LIMIT 20
        ";
        $stmt = $this->pdo->prepare($sqlIncidencias);
        $stmt->execute([':inicio' => $fechas['inicio'], ':fin' => $fechas['fin']]);
        $cursosConIncidencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Cursos con docentes asignados y evaluaciones
        $sqlDocentesCurso = "
            SELECT 
                c.id,
                c.codigo,
                c.nombre as materia,
                a.nombre as academia,
                COUNT(DISTINCT dc.docente_id) as total_docentes,
                ROUND(AVG(ed.calificacion_global), 2) as promedio_evaluacion,
                COUNT(DISTINCT ed.id) as total_evaluaciones
            FROM curso c
            LEFT JOIN academia a ON c.academia_id = a.id
            LEFT JOIN docente_curso dc ON c.id = dc.curso_id AND dc.estatus = 'activo'
            LEFT JOIN evaluacion_docente ed ON c.id = ed.curso_id AND ed.estatus = 'completada'
            WHERE c.estatus = 'activo'
            GROUP BY c.id, c.codigo, c.nombre, a.nombre
            HAVING total_docentes > 0 OR total_evaluaciones > 0
            ORDER BY promedio_evaluacion DESC, total_docentes DESC
            LIMIT 20
        ";
        $stmt = $this->pdo->query($sqlDocentesCurso);
        $cursosConDocentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 4. Distribución por modalidad
        $sqlModalidad = "
            SELECT 
                modalidad,
                COUNT(*) as cantidad
            FROM curso
            WHERE estatus = 'activo'
            GROUP BY modalidad
            ORDER BY cantidad DESC
        ";
        $stmt = $this->pdo->query($sqlModalidad);
        $porModalidad = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 5. Distribución por academia
        $sqlAcademia = "
            SELECT 
                COALESCE(a.nombre, 'Sin academia') as academia,
                COUNT(c.id) as cantidad
            FROM curso c
            LEFT JOIN academia a ON c.academia_id = a.id
            WHERE c.estatus = 'activo'
            GROUP BY a.nombre
            ORDER BY cantidad DESC
        ";
        $stmt = $this->pdo->query($sqlAcademia);
        $porAcademia = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 6. Distribución por semestre
        $sqlSemestre = "
            SELECT 
                COALESCE(semestre, 0) as semestre,
                COUNT(*) as cantidad
            FROM curso
            WHERE estatus = 'activo'
            GROUP BY semestre
            ORDER BY semestre ASC
        ";
        $stmt = $this->pdo->query($sqlSemestre);
        $porSemestre = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 7. Top materias por evaluación docente
        $sqlTopEvaluaciones = "
            SELECT 
                c.codigo,
                c.nombre as materia,
                ROUND(AVG(ed.calificacion_global), 2) as promedio,
                COUNT(ed.id) as total_evaluaciones,
                MIN(ed.calificacion_global) as calificacion_min,
                MAX(ed.calificacion_global) as calificacion_max
            FROM curso c
            INNER JOIN evaluacion_docente ed ON c.id = ed.curso_id
            WHERE ed.estatus = 'completada'
            GROUP BY c.id, c.codigo, c.nombre
            HAVING total_evaluaciones >= 1
            ORDER BY promedio DESC
            LIMIT 10
        ";
        $stmt = $this->pdo->query($sqlTopEvaluaciones);
        $topPorEvaluacion = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'periodo' => $fechas,
            'resumen' => $resumen,
            'cursos_con_incidencias' => $cursosConIncidencias,
            'cursos_con_docentes' => $cursosConDocentes,
            'distribucion_modalidad' => $porModalidad,
            'distribucion_academia' => $porAcademia,
            'distribucion_semestre' => $porSemestre,
            'top_por_evaluacion' => $topPorEvaluacion
        ];
    }
    
    /**
     * Exportar reporte por materia a CSV
     * 
     * @param string $periodo Código del período
     * @param string|null $fechaInicio Fecha inicio personalizada
     * @param string|null $fechaFin Fecha fin personalizada
     * @return array Datos para exportación
     */
    public function getReportePorMateriaParaExportar($periodo = 'todo', $fechaInicio = null, $fechaFin = null) {
        $fechas = $this->getPeriodoFechas($periodo, $fechaInicio, $fechaFin);
        
        $sql = "
            SELECT 
                c.codigo,
                c.nombre as materia,
                COALESCE(a.nombre, 'Sin academia') as academia,
                c.semestre,
                c.modalidad,
                c.creditos,
                c.horas_semana,
                COUNT(DISTINCT dc.docente_id) as docentes_asignados,
                COUNT(DISTINCT i.id) as total_incidencias,
                SUM(CASE WHEN i.status = 'abierto' THEN 1 ELSE 0 END) as incidencias_abiertas,
                ROUND(AVG(ed.calificacion_global), 2) as promedio_evaluacion
            FROM curso c
            LEFT JOIN academia a ON c.academia_id = a.id
            LEFT JOIN docente_curso dc ON c.id = dc.curso_id
            LEFT JOIN incidencia i ON (c.id = i.curso_id OR c.nombre = i.curso)
                AND DATE(i.fecha_creacion) BETWEEN :inicio AND :fin
            LEFT JOIN evaluacion_docente ed ON c.id = ed.curso_id AND ed.estatus = 'completada'
            WHERE c.estatus = 'activo'
            GROUP BY c.id, c.codigo, c.nombre, a.nombre, c.semestre, c.modalidad, c.creditos, c.horas_semana
            ORDER BY c.nombre ASC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inicio' => $fechas['inicio'], ':fin' => $fechas['fin']]);
        
        return [
            'periodo' => $fechas,
            'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'headers' => ['Código', 'Materia', 'Academia', 'Semestre', 'Modalidad', 'Créditos', 'Hrs/Semana', 'Docentes', 'Incidencias', 'Abiertas', 'Prom. Evaluación']
        ];
    }
    
    // ========================================================================
    // EXPORTACIÓN CSV
    // ========================================================================
    
    /**
     * Obtener datos de incidencias para exportación CSV
     * 
     * @param string $periodo Código del período
     * @param string|null $fechaInicio Fecha inicio personalizada
     * @param string|null $fechaFin Fecha fin personalizada
     * @return array Datos de incidencias
     */
    public function getIncidenciasParaExportar($periodo = 'todo', $fechaInicio = null, $fechaFin = null) {
        $fechas = $this->getPeriodoFechas($periodo, $fechaInicio, $fechaFin);
        
        $sql = "
            SELECT 
                i.id,
                ti.nombre as tipo,
                COALESCE(u_prof.nombre, 'N/A') as profesor,
                i.curso,
                i.prioridad,
                i.sla,
                COALESCE(u_asig.nombre, 'N/A') as asignado_a,
                i.status,
                DATE_FORMAT(i.fecha_creacion, '%Y-%m-%d %H:%i:%s') as fecha_creacion
            FROM incidencia i
            INNER JOIN tipo_incidencia ti ON i.tipo_id = ti.id
            LEFT JOIN docente d ON i.profesor = d.id
            LEFT JOIN usuario u_prof ON d.id_usuario = u_prof.id
            LEFT JOIN usuario u_asig ON i.asignadoA = u_asig.id
            WHERE DATE(i.fecha_creacion) BETWEEN :inicio AND :fin
            ORDER BY i.fecha_creacion DESC
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':inicio' => $fechas['inicio'], ':fin' => $fechas['fin']]);
        
        return [
            'periodo' => $fechas,
            'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'headers' => ['ID', 'Tipo', 'Profesor', 'Curso', 'Prioridad', 'SLA', 'Asignado a', 'Estado', 'Fecha Creación']
        ];
    }
    
    /**
     * Obtener datos de docentes para exportación CSV
     * 
     * @return array Datos de docentes
     */
    public function getDocentesParaExportar() {
        $sql = "
            SELECT 
                d.id,
                u.nombre,
                u.email,
                d.grados,
                d.idioma,
                CASE WHEN d.sni = 1 THEN 'Sí' ELSE 'No' END as sni,
                d.estatus,
                GROUP_CONCAT(DISTINCT a.nombre SEPARATOR ', ') as academias,
                GROUP_CONCAT(DISTINCT ae.nombre SEPARATOR ', ') as areas_especialidad
            FROM docente d
            INNER JOIN usuario u ON d.id_usuario = u.id
            LEFT JOIN docente_academia da ON d.id = da.docente_id
            LEFT JOIN academia a ON da.academia_id = a.id
            LEFT JOIN docente_area_especialidad dae ON d.id = dae.docente_id
            LEFT JOIN area_especialidad ae ON dae.area_id = ae.id
            GROUP BY d.id, u.nombre, u.email, d.grados, d.idioma, d.sni, d.estatus
            ORDER BY u.nombre ASC
        ";
        
        $stmt = $this->pdo->query($sql);
        
        return [
            'datos' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'headers' => ['ID', 'Nombre', 'Email', 'Grados', 'Idioma', 'SNI', 'Estatus', 'Academias', 'Áreas de Especialidad']
        ];
    }
    
    /**
     * Obtener estadísticas generales para exportación CSV
     * 
     * @param string $periodo Código del período
     * @param string|null $fechaInicio Fecha inicio personalizada
     * @param string|null $fechaFin Fecha fin personalizada
     * @return array Datos de estadísticas
     */
    public function getEstadisticasParaExportar($periodo = 'todo', $fechaInicio = null, $fechaFin = null) {
        $dashboard = $this->getDashboard($periodo, $fechaInicio, $fechaFin);
        
        $datos = [];
        
        // Estadísticas generales
        $datos[] = ['Métrica', 'Valor'];
        $datos[] = ['Total Docentes', $dashboard['dashboard']['total_docentes']];
        $datos[] = ['Docentes Activos', $dashboard['dashboard']['docentes_activos']];
        $datos[] = ['Docentes SNI', $dashboard['dashboard']['docentes_sni']];
        $datos[] = ['Total Incidencias', $dashboard['dashboard']['total_incidencias']];
        $datos[] = ['Incidencias Abiertas', $dashboard['dashboard']['incidencias_abiertas']];
        $datos[] = ['Incidencias en Período', $dashboard['incidencias_periodo']];
        $datos[] = [''];
        
        // Incidencias por estado
        $datos[] = ['Estado', 'Cantidad'];
        foreach ($dashboard['incidencias_por_estado'] as $item) {
            $datos[] = [$item['status'], $item['cantidad']];
        }
        $datos[] = [''];
        
        // Incidencias por prioridad
        $datos[] = ['Prioridad', 'Cantidad'];
        foreach ($dashboard['incidencias_por_prioridad'] as $item) {
            $datos[] = [$item['prioridad'], $item['cantidad']];
        }
        
        return [
            'periodo' => $dashboard['periodo'],
            'datos' => $datos,
            'headers' => null // Los headers están incluidos en los datos
        ];
    }
    
    /**
     * Convertir array de datos a formato CSV
     * 
     * @param array $data Datos a convertir
     * @param array|null $headers Encabezados (opcional)
     * @return string Contenido CSV
     */
    public static function arrayToCsv($data, $headers = null) {
        $output = fopen('php://temp', 'r+');
        
        // BOM para UTF-8 (para que Excel reconozca los caracteres especiales)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir headers si existen
        if ($headers) {
            fputcsv($output, $headers, ';');
        }
        
        // Escribir datos
        foreach ($data as $row) {
            if (is_array($row)) {
                fputcsv($output, $row, ';');
            }
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
