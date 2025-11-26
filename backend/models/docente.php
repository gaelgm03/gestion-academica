<?php
/**
 * Modelo Docente
 * Sistema de Gestión Académica
 * 
 * Maneja todas las operaciones CRUD relacionadas con docentes
 */

class Docente {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtener todos los docentes con filtros opcionales
     * 
     * @param array $filters Filtros: estatus, sni, academia_id, search
     * @return array Lista de docentes
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT 
                d.id,
                d.id_usuario,
                u.nombre,
                u.email,
                d.grados,
                d.idioma,
                d.sni,
                d.cvlink,
                d.estatus,
                GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') as academias,
                GROUP_CONCAT(DISTINCT da.academia_id ORDER BY da.academia_id) as academia_ids
            FROM docente d
            INNER JOIN usuario u ON d.id_usuario = u.id
            LEFT JOIN docente_academia da ON d.id = da.docente_id
            LEFT JOIN academia a ON da.academia_id = a.id
            WHERE 1=1
        ";
        
        $params = [];
        
        // Filtro por estatus
        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $sql .= " AND d.estatus = :estatus";
            $params[':estatus'] = $filters['estatus'];
        }
        
        // Filtro por SNI
        if (isset($filters['sni']) && $filters['sni'] !== '') {
            $sql .= " AND d.sni = :sni";
            $params[':sni'] = (int)$filters['sni'];
        }
        
        // Filtro por academia
        if (isset($filters['academia_id']) && $filters['academia_id'] !== '') {
            $sql .= " AND da.academia_id = :academia_id";
            $params[':academia_id'] = (int)$filters['academia_id'];
        }
        
        $sql .= " GROUP BY d.id, d.id_usuario, u.nombre, u.email, d.grados, d.idioma, d.sni, d.cvlink, d.estatus";
        
        // Filtro de búsqueda (debe ir después del GROUP BY para usar HAVING)
        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " HAVING 
                CONCAT(
                    COALESCE(u.nombre, ''), ' ', 
                    COALESCE(u.email, ''), ' ', 
                    COALESCE(d.grados, ''), ' ', 
                    COALESCE(academias, '')
                ) LIKE :search
            ";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY u.nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener un docente por ID
     * 
     * @param int $id ID del docente
     * @return array|false Datos del docente o false si no existe
     */
    public function getById($id) {
        $sql = "
            SELECT 
                d.id,
                d.id_usuario,
                u.nombre,
                u.email,
                d.grados,
                d.idioma,
                d.sni,
                d.cvlink,
                d.estatus,
                GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') as academias,
                GROUP_CONCAT(DISTINCT da.academia_id ORDER BY da.academia_id) as academia_ids
            FROM docente d
            INNER JOIN usuario u ON d.id_usuario = u.id
            LEFT JOIN docente_academia da ON d.id = da.docente_id
            LEFT JOIN academia a ON da.academia_id = a.id
            WHERE d.id = :id
            GROUP BY d.id, d.id_usuario, u.nombre, u.email, d.grados, d.idioma, d.sni, d.cvlink, d.estatus
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear un nuevo docente
     * 
     * @param array $data Datos del docente
     * @return int ID del docente creado
     */
    public function create($data) {
        $this->pdo->beginTransaction();
        
        try {
            // 1. Crear usuario
            $sqlUsuario = "
                INSERT INTO usuario (email, nombre, rol_id) 
                VALUES (:email, :nombre, :rol_id)
            ";
            
            $stmtUsuario = $this->pdo->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':email' => $data['email'],
                ':nombre' => $data['nombre'],
                ':rol_id' => $data['rol_id'] ?? 4 // 4 = docente por defecto
            ]);
            
            $usuarioId = $this->pdo->lastInsertId();
            
            // 2. Crear docente
            $sqlDocente = "
                INSERT INTO docente (id_usuario, grados, idioma, sni, cvlink, estatus) 
                VALUES (:id_usuario, :grados, :idioma, :sni, :cvlink, :estatus)
            ";
            
            $stmtDocente = $this->pdo->prepare($sqlDocente);
            $stmtDocente->execute([
                ':id_usuario' => $usuarioId,
                ':grados' => $data['grados'] ?? '',
                ':idioma' => $data['idioma'] ?? '',
                ':sni' => isset($data['sni']) ? (int)$data['sni'] : 0,
                ':cvlink' => $data['cvlink'] ?? '',
                ':estatus' => $data['estatus'] ?? 'activo'
            ]);
            
            $docenteId = $this->pdo->lastInsertId();
            
            // 3. Asignar academias si se proporcionaron
            if (isset($data['academia_ids']) && is_array($data['academia_ids'])) {
                $this->assignAcademias($docenteId, $data['academia_ids']);
            }
            
            $this->pdo->commit();
            return $docenteId;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Actualizar un docente existente
     * 
     * @param int $id ID del docente
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data) {
        $this->pdo->beginTransaction();
        
        try {
            // 1. Obtener id_usuario del docente
            $docente = $this->getById($id);
            if (!$docente) {
                throw new Exception("Docente no encontrado");
            }
            
            // 2. Actualizar usuario si hay datos diferentes
            $sqlUsuario = "UPDATE usuario SET ";
            $paramsUsuario = [];
            $updates = [];
            
            // Solo actualizar email si cambió
            if (isset($data['email']) && $data['email'] !== $docente['email']) {
                $updates[] = "email = :email";
                $paramsUsuario[':email'] = $data['email'];
            }
            
            // Solo actualizar nombre si cambió
            if (isset($data['nombre']) && $data['nombre'] !== $docente['nombre']) {
                $updates[] = "nombre = :nombre";
                $paramsUsuario[':nombre'] = $data['nombre'];
            }
            
            // Ejecutar UPDATE solo si hay cambios
            if (!empty($updates)) {
                $sqlUsuario .= implode(', ', $updates) . " WHERE id = :id";
                $paramsUsuario[':id'] = $docente['id_usuario'];
                
                $stmtUsuario = $this->pdo->prepare($sqlUsuario);
                $stmtUsuario->execute($paramsUsuario);
            }
            
            // 3. Actualizar docente
            $sqlDocente = "UPDATE docente SET ";
            $paramsDocente = [];
            $updates = [];
            
            if (isset($data['grados'])) {
                $updates[] = "grados = :grados";
                $paramsDocente[':grados'] = $data['grados'];
            }
            
            if (isset($data['idioma'])) {
                $updates[] = "idioma = :idioma";
                $paramsDocente[':idioma'] = $data['idioma'];
            }
            
            if (isset($data['sni'])) {
                $updates[] = "sni = :sni";
                $paramsDocente[':sni'] = (int)$data['sni'];
            }
            
            if (isset($data['cvlink'])) {
                $updates[] = "cvlink = :cvlink";
                $paramsDocente[':cvlink'] = $data['cvlink'];
            }
            
            if (isset($data['estatus'])) {
                $updates[] = "estatus = :estatus";
                $paramsDocente[':estatus'] = $data['estatus'];
            }
            
            if (!empty($updates)) {
                $sqlDocente .= implode(', ', $updates) . " WHERE id = :id";
                $paramsDocente[':id'] = $id;
                
                $stmtDocente = $this->pdo->prepare($sqlDocente);
                $stmtDocente->execute($paramsDocente);
            }
            
            // 4. Actualizar academias si se proporcionaron
            if (isset($data['academia_ids']) && is_array($data['academia_ids'])) {
                // Eliminar asignaciones actuales
                $this->pdo->prepare("DELETE FROM docente_academia WHERE docente_id = ?")->execute([$id]);
                // Asignar nuevas academias
                $this->assignAcademias($id, $data['academia_ids']);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * Eliminar un docente (soft delete - cambiar estatus a inactivo)
     * 
     * @param int $id ID del docente
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $sql = "UPDATE docente SET estatus = 'inactivo' WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Asignar academias a un docente
     * 
     * @param int $docenteId ID del docente
     * @param array $academiaIds Array de IDs de academias
     */
    private function assignAcademias($docenteId, $academiaIds) {
        $sql = "INSERT INTO docente_academia (docente_id, academia_id) VALUES (:docente_id, :academia_id)";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($academiaIds as $academiaId) {
            $stmt->execute([
                ':docente_id' => $docenteId,
                ':academia_id' => $academiaId
            ]);
        }
    }
    
    /**
     * Obtener estadísticas de docentes
     * 
     * @return array Estadísticas
     */
    public function getStats() {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estatus = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estatus = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                SUM(CASE WHEN sni = 1 THEN 1 ELSE 0 END) as con_sni
            FROM docente
        ";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
