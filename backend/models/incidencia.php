<?php
/**
 * Modelo Incidencia
 * Sistema de Gestión Académica
 * 
 * Maneja todas las operaciones CRUD relacionadas con incidencias
 */

class Incidencia {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtener todas las incidencias con filtros opcionales
     * 
     * @param array $filters Filtros: status, prioridad, profesor, asignadoA, tipo_id, fecha_desde, fecha_hasta
     * @return array Lista de incidencias
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT 
                i.id,
                i.tipo_id,
                ti.nombre as tipo,
                i.profesor,
                i.curso,
                i.prioridad,
                i.sla,
                i.asignadoA,
                i.evidencias,
                i.status,
                i.fecha_creacion,
                CONCAT(u_prof.nombre, ' (', u_prof.email, ')') as profesor_nombre,
                u_prof.email as profesor_email,
                u_asig.nombre as asignado_nombre
            FROM incidencia i
            INNER JOIN tipo_incidencia ti ON i.tipo_id = ti.id
            LEFT JOIN docente d ON i.profesor = d.id
            LEFT JOIN usuario u_prof ON d.id_usuario = u_prof.id
            LEFT JOIN usuario u_asig ON i.asignadoA = u_asig.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Filtro por status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND i.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Filtro por prioridad
        if (isset($filters['prioridad']) && $filters['prioridad'] !== '') {
            $sql .= " AND i.prioridad = :prioridad";
            $params[':prioridad'] = $filters['prioridad'];
        }
        
        // Filtro por profesor
        if (isset($filters['profesor']) && $filters['profesor'] !== '') {
            $sql .= " AND i.profesor = :profesor";
            $params[':profesor'] = (int)$filters['profesor'];
        }
        
        // Filtro por asignado a
        if (isset($filters['asignadoA']) && $filters['asignadoA'] !== '') {
            $sql .= " AND i.asignadoA = :asignadoA";
            $params[':asignadoA'] = (int)$filters['asignadoA'];
        }
        
        // Filtro por tipo (acepta tipo_id)
        if (isset($filters['tipo_id']) && $filters['tipo_id'] !== '') {
            $sql .= " AND i.tipo_id = :tipo_id";
            $params[':tipo_id'] = (int)$filters['tipo_id'];
        }
        
        // Filtro por nombre de tipo (búsqueda)
        if (isset($filters['tipo']) && $filters['tipo'] !== '') {
            $sql .= " AND ti.nombre LIKE :tipo";
            $params[':tipo'] = '%' . $filters['tipo'] . '%';
        }
        
        // Filtro por fecha desde
        if (isset($filters['fecha_desde']) && $filters['fecha_desde'] !== '') {
            $sql .= " AND DATE(i.fecha_creacion) >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }
        
        // Filtro por fecha hasta
        if (isset($filters['fecha_hasta']) && $filters['fecha_hasta'] !== '') {
            $sql .= " AND DATE(i.fecha_creacion) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }
        
        $sql .= " ORDER BY i.fecha_creacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener una incidencia por ID
     * 
     * @param int $id ID de la incidencia
     * @return array|false Datos de la incidencia o false si no existe
     */
    public function getById($id) {
        $sql = "
            SELECT 
                i.id,
                i.tipo_id,
                ti.nombre as tipo,
                i.profesor,
                i.curso,
                i.prioridad,
                i.sla,
                i.asignadoA,
                i.evidencias,
                i.status,
                i.fecha_creacion,
                CONCAT(u_prof.nombre, ' (', u_prof.email, ')') as profesor_nombre,
                u_prof.email as profesor_email,
                u_asig.nombre as asignado_nombre
            FROM incidencia i
            INNER JOIN tipo_incidencia ti ON i.tipo_id = ti.id
            LEFT JOIN docente d ON i.profesor = d.id
            LEFT JOIN usuario u_prof ON d.id_usuario = u_prof.id
            LEFT JOIN usuario u_asig ON i.asignadoA = u_asig.id
            WHERE i.id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear una nueva incidencia
     * 
     * @param array $data Datos de la incidencia (debe incluir tipo_id)
     * @return int ID de la incidencia creada
     */
    public function create($data) {
        $sql = "
            INSERT INTO incidencia (tipo_id, profesor, curso, prioridad, sla, asignadoA, evidencias, status) 
            VALUES (:tipo_id, :profesor, :curso, :prioridad, :sla, :asignadoA, :evidencias, :status)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tipo_id' => $data['tipo_id'],
            ':profesor' => $data['profesor'] ?? null,
            ':curso' => $data['curso'] ?? '',
            ':prioridad' => $data['prioridad'] ?? 'Media',
            ':sla' => $data['sla'] ?? '72h',
            ':asignadoA' => $data['asignadoA'] ?? null,
            ':evidencias' => $data['evidencias'] ?? null,
            ':status' => $data['status'] ?? 'abierto'
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Actualizar una incidencia existente
     * 
     * @param int $id ID de la incidencia
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data) {
        $sql = "UPDATE incidencia SET ";
        $params = [];
        $updates = [];
        
        if (isset($data['tipo_id'])) {
            $updates[] = "tipo_id = :tipo_id";
            $params[':tipo_id'] = (int)$data['tipo_id'];
        }
        
        if (isset($data['profesor'])) {
            $updates[] = "profesor = :profesor";
            $params[':profesor'] = $data['profesor'];
        }
        
        if (isset($data['curso'])) {
            $updates[] = "curso = :curso";
            $params[':curso'] = $data['curso'];
        }
        
        if (isset($data['prioridad'])) {
            $updates[] = "prioridad = :prioridad";
            $params[':prioridad'] = $data['prioridad'];
        }
        
        if (isset($data['sla'])) {
            $updates[] = "sla = :sla";
            $params[':sla'] = $data['sla'];
        }
        
        if (isset($data['asignadoA'])) {
            $updates[] = "asignadoA = :asignadoA";
            $params[':asignadoA'] = $data['asignadoA'];
        }
        
        if (isset($data['evidencias'])) {
            $updates[] = "evidencias = :evidencias";
            // Convertir string vacío a null
            $evidencias = trim($data['evidencias'] ?? '');
            $params[':evidencias'] = $evidencias !== '' ? $evidencias : null;
        }
        
        if (isset($data['status'])) {
            $updates[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $sql .= implode(', ', $updates) . " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Eliminar una incidencia
     * 
     * @param int $id ID de la incidencia
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $sql = "DELETE FROM incidencia WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Obtener estadísticas de incidencias
     * 
     * @return array Estadísticas
     */
    public function getStats() {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'abierto' THEN 1 ELSE 0 END) as abiertas,
                SUM(CASE WHEN status = 'en proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN status = 'cerrado' THEN 1 ELSE 0 END) as cerradas,
                SUM(CASE WHEN prioridad = 'Alta' THEN 1 ELSE 0 END) as alta_prioridad,
                SUM(CASE WHEN prioridad = 'Media' THEN 1 ELSE 0 END) as media_prioridad,
                SUM(CASE WHEN prioridad = 'Baja' THEN 1 ELSE 0 END) as baja_prioridad
            FROM incidencia
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener incidencias por estado
     * 
     * @return array Agrupación por status
     */
    public function getByStatus() {
        $sql = "
            SELECT status, COUNT(*) as cantidad
            FROM incidencia
            GROUP BY status
            ORDER BY cantidad DESC
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener incidencias por prioridad
     * 
     * @return array Agrupación por prioridad
     */
    public function getByPriority() {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todos los tipos de incidencia disponibles
     * 
     * @return array Lista de tipos de incidencia
     */
    public function getTipos() {
        $sql = "
            SELECT id, nombre, descripcion
            FROM tipo_incidencia
            WHERE activo = 1
            ORDER BY orden ASC, nombre ASC
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
