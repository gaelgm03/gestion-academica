<?php
/**
 * Modelo Curso
 * Sistema de Gestión Académica
 * 
 * Maneja todas las operaciones CRUD relacionadas con cursos/materias
 */

class Curso {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtener todos los cursos con filtros opcionales
     * 
     * @param array $filters Filtros: estatus, academia_id, semestre, modalidad, search
     * @return array Lista de cursos
     */
    public function getAll($filters = []) {
        $sql = "
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
                c.fecha_creacion,
                a.id as academia_id,
                a.nombre as academia_nombre,
                (SELECT COUNT(*) FROM docente_curso dc WHERE dc.curso_id = c.id AND dc.estatus = 'activo') as docentes_asignados,
                (SELECT SUM(dc.inscritos) FROM docente_curso dc WHERE dc.curso_id = c.id AND dc.estatus = 'activo') as total_inscritos
            FROM curso c
            LEFT JOIN academia a ON c.academia_id = a.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Filtro por estatus
        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $sql .= " AND c.estatus = :estatus";
            $params[':estatus'] = $filters['estatus'];
        }
        
        // Filtro por academia
        if (isset($filters['academia_id']) && $filters['academia_id'] !== '') {
            $sql .= " AND c.academia_id = :academia_id";
            $params[':academia_id'] = (int)$filters['academia_id'];
        }
        
        // Filtro por semestre
        if (isset($filters['semestre']) && $filters['semestre'] !== '') {
            $sql .= " AND c.semestre = :semestre";
            $params[':semestre'] = (int)$filters['semestre'];
        }
        
        // Filtro por modalidad
        if (isset($filters['modalidad']) && $filters['modalidad'] !== '') {
            $sql .= " AND c.modalidad = :modalidad";
            $params[':modalidad'] = $filters['modalidad'];
        }
        
        // Filtro de búsqueda
        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND (c.codigo LIKE :search OR c.nombre LIKE :search OR c.descripcion LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un curso por ID
     * 
     * @param int $id ID del curso
     * @return array|false Datos del curso o false si no existe
     */
    public function getById($id) {
        $sql = "
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
                c.fecha_creacion,
                c.fecha_actualizacion,
                a.id as academia_id,
                a.nombre as academia_nombre
            FROM curso c
            LEFT JOIN academia a ON c.academia_id = a.id
            WHERE c.id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        $curso = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($curso) {
            // Obtener docentes asignados
            $curso['docentes'] = $this->getDocentesDelCurso($id);
        }
        
        return $curso;
    }
    
    /**
     * Crear un nuevo curso
     * 
     * @param array $data Datos del curso
     * @return int ID del curso creado
     */
    public function create($data) {
        $sql = "
            INSERT INTO curso (codigo, nombre, descripcion, creditos, horas_semana, semestre, modalidad, academia_id, estatus) 
            VALUES (:codigo, :nombre, :descripcion, :creditos, :horas_semana, :semestre, :modalidad, :academia_id, :estatus)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':codigo' => $data['codigo'],
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? '',
            ':creditos' => $data['creditos'] ?? 0,
            ':horas_semana' => $data['horas_semana'] ?? 0,
            ':semestre' => $data['semestre'] ?? null,
            ':modalidad' => $data['modalidad'] ?? 'presencial',
            ':academia_id' => $data['academia_id'] ?? null,
            ':estatus' => $data['estatus'] ?? 'activo'
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Actualizar un curso existente
     * 
     * @param int $id ID del curso
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data) {
        // Verificar que el curso existe
        $curso = $this->getById($id);
        if (!$curso) {
            throw new Exception("Curso no encontrado");
        }
        
        $sql = "
            UPDATE curso SET
                codigo = :codigo,
                nombre = :nombre,
                descripcion = :descripcion,
                creditos = :creditos,
                horas_semana = :horas_semana,
                semestre = :semestre,
                modalidad = :modalidad,
                academia_id = :academia_id,
                estatus = :estatus
            WHERE id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':codigo' => $data['codigo'] ?? $curso['codigo'],
            ':nombre' => $data['nombre'] ?? $curso['nombre'],
            ':descripcion' => $data['descripcion'] ?? $curso['descripcion'],
            ':creditos' => $data['creditos'] ?? $curso['creditos'],
            ':horas_semana' => $data['horas_semana'] ?? $curso['horas_semana'],
            ':semestre' => $data['semestre'] ?? $curso['semestre'],
            ':modalidad' => $data['modalidad'] ?? $curso['modalidad'],
            ':academia_id' => $data['academia_id'] ?? $curso['academia_id'],
            ':estatus' => $data['estatus'] ?? $curso['estatus']
        ]);
    }
    
    /**
     * Eliminar un curso (soft delete)
     * 
     * @param int $id ID del curso
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $sql = "UPDATE curso SET estatus = 'inactivo' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Obtener docentes asignados a un curso
     * 
     * @param int $cursoId ID del curso
     * @param string|null $periodo Filtrar por período
     * @return array Lista de docentes asignados
     */
    public function getDocentesDelCurso($cursoId, $periodo = null) {
        $sql = "
            SELECT 
                dc.id as asignacion_id,
                dc.periodo,
                dc.grupo,
                dc.horario,
                dc.aula,
                dc.cupo_maximo,
                dc.inscritos,
                dc.estatus,
                dc.fecha_asignacion,
                d.id as docente_id,
                u.nombre as docente_nombre,
                u.email as docente_email
            FROM docente_curso dc
            INNER JOIN docente d ON dc.docente_id = d.id
            INNER JOIN usuario u ON d.id_usuario = u.id
            WHERE dc.curso_id = :curso_id
        ";
        
        $params = [':curso_id' => $cursoId];
        
        if ($periodo) {
            $sql .= " AND dc.periodo = :periodo";
            $params[':periodo'] = $periodo;
        }
        
        $sql .= " ORDER BY dc.periodo DESC, dc.grupo ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Asignar docente a un curso
     * 
     * @param array $data Datos de la asignación
     * @return int ID de la asignación creada
     */
    public function asignarDocente($data) {
        $sql = "
            INSERT INTO docente_curso (docente_id, curso_id, periodo, grupo, horario, aula, cupo_maximo, inscritos, estatus)
            VALUES (:docente_id, :curso_id, :periodo, :grupo, :horario, :aula, :cupo_maximo, :inscritos, :estatus)
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':docente_id' => $data['docente_id'],
            ':curso_id' => $data['curso_id'],
            ':periodo' => $data['periodo'],
            ':grupo' => $data['grupo'] ?? 'A',
            ':horario' => $data['horario'] ?? '',
            ':aula' => $data['aula'] ?? '',
            ':cupo_maximo' => $data['cupo_maximo'] ?? 30,
            ':inscritos' => $data['inscritos'] ?? 0,
            ':estatus' => $data['estatus'] ?? 'activo'
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Actualizar asignación de docente
     * 
     * @param int $id ID de la asignación
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function actualizarAsignacion($id, $data) {
        $sql = "
            UPDATE docente_curso SET
                horario = :horario,
                aula = :aula,
                cupo_maximo = :cupo_maximo,
                inscritos = :inscritos,
                estatus = :estatus
            WHERE id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':horario' => $data['horario'] ?? '',
            ':aula' => $data['aula'] ?? '',
            ':cupo_maximo' => $data['cupo_maximo'] ?? 30,
            ':inscritos' => $data['inscritos'] ?? 0,
            ':estatus' => $data['estatus'] ?? 'activo'
        ]);
    }
    
    /**
     * Eliminar asignación de docente
     * 
     * @param int $id ID de la asignación
     * @return bool True si se eliminó correctamente
     */
    public function eliminarAsignacion($id) {
        $sql = "DELETE FROM docente_curso WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Obtener cursos de un docente
     * 
     * @param int $docenteId ID del docente
     * @param string|null $periodo Filtrar por período
     * @return array Lista de cursos del docente
     */
    public function getCursosDelDocente($docenteId, $periodo = null) {
        $sql = "
            SELECT 
                dc.id as asignacion_id,
                dc.periodo,
                dc.grupo,
                dc.horario,
                dc.aula,
                dc.cupo_maximo,
                dc.inscritos,
                dc.estatus as asignacion_estatus,
                c.id as curso_id,
                c.codigo,
                c.nombre,
                c.creditos,
                c.semestre,
                c.modalidad,
                a.nombre as academia_nombre
            FROM docente_curso dc
            INNER JOIN curso c ON dc.curso_id = c.id
            LEFT JOIN academia a ON c.academia_id = a.id
            WHERE dc.docente_id = :docente_id
        ";
        
        $params = [':docente_id' => $docenteId];
        
        if ($periodo) {
            $sql .= " AND dc.periodo = :periodo";
            $params[':periodo'] = $periodo;
        }
        
        $sql .= " ORDER BY dc.periodo DESC, c.codigo ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener períodos académicos
     * 
     * @param string|null $estatus Filtrar por estatus
     * @return array Lista de períodos
     */
    public function getPeriodos($estatus = null) {
        $sql = "SELECT * FROM periodo_academico";
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
     * Obtener período activo
     * 
     * @return array|false Período activo o false si no hay
     */
    public function getPeriodoActivo() {
        $sql = "SELECT * FROM periodo_academico WHERE estatus = 'activo' LIMIT 1";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener estadísticas de cursos
     * 
     * @return array Estadísticas
     */
    public function getStats() {
        $stats = [];
        
        // Total de cursos
        $sql = "SELECT COUNT(*) as total FROM curso";
        $stmt = $this->pdo->query($sql);
        $stats['total_cursos'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Cursos activos
        $sql = "SELECT COUNT(*) as total FROM curso WHERE estatus = 'activo'";
        $stmt = $this->pdo->query($sql);
        $stats['cursos_activos'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de asignaciones activas
        $sql = "SELECT COUNT(*) as total FROM docente_curso WHERE estatus = 'activo'";
        $stmt = $this->pdo->query($sql);
        $stats['asignaciones_activas'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de alumnos inscritos
        $sql = "SELECT COALESCE(SUM(inscritos), 0) as total FROM docente_curso WHERE estatus = 'activo'";
        $stmt = $this->pdo->query($sql);
        $stats['total_inscritos'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Cursos por modalidad
        $sql = "SELECT modalidad, COUNT(*) as cantidad FROM curso WHERE estatus = 'activo' GROUP BY modalidad";
        $stmt = $this->pdo->query($sql);
        $stats['cursos_por_modalidad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cursos por academia
        $sql = "
            SELECT a.nombre as academia, COUNT(c.id) as cantidad 
            FROM curso c 
            LEFT JOIN academia a ON c.academia_id = a.id 
            WHERE c.estatus = 'activo' 
            GROUP BY a.id, a.nombre
        ";
        $stmt = $this->pdo->query($sql);
        $stats['cursos_por_academia'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    /**
     * Buscar cursos para selector (dropdown)
     * Solo devuelve id, codigo y nombre
     * 
     * @param string|null $search Término de búsqueda
     * @return array Lista simplificada de cursos
     */
    public function getCursosParaSelector($search = null) {
        $sql = "SELECT id, codigo, nombre FROM curso WHERE estatus = 'activo'";
        $params = [];
        
        if ($search) {
            $sql .= " AND (codigo LIKE :search OR nombre LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY codigo ASC LIMIT 50";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
