<?php
/**
 * Modelo: IncidenciaHistorial
 * Sistema de Gestión Académica
 * 
 * Maneja el registro de cambios/auditoría de incidencias
 */

class IncidenciaHistorial {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Registrar un cambio en el historial
     * 
     * @param int $incidenciaId ID de la incidencia
     * @param int $usuarioId ID del usuario que hace el cambio
     * @param string $campo Campo modificado
     * @param mixed $valorAnterior Valor antes del cambio
     * @param mixed $valorNuevo Valor después del cambio
     * @param string $accion Tipo de acción (crear, editar, eliminar, cambio_status, asignar)
     * @return int ID del registro de historial creado
     */
    public function registrarCambio($incidenciaId, $usuarioId, $campo, $valorAnterior, $valorNuevo, $accion = 'editar') {
        $sql = "INSERT INTO incidencia_historial 
                (incidencia_id, usuario_id, campo_modificado, valor_anterior, valor_nuevo, accion, ip_address, user_agent)
                VALUES (:incidencia_id, :usuario_id, :campo, :valor_anterior, :valor_nuevo, :accion, :ip, :user_agent)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':incidencia_id' => $incidenciaId,
            ':usuario_id' => $usuarioId,
            ':campo' => $campo,
            ':valor_anterior' => $valorAnterior,
            ':valor_nuevo' => $valorNuevo,
            ':accion' => $accion,
            ':ip' => $this->getClientIP(),
            ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Registrar creación de incidencia
     * 
     * @param int $incidenciaId ID de la incidencia creada
     * @param int $usuarioId ID del usuario que crea
     * @param array $datos Datos de la incidencia creada
     */
    public function registrarCreacion($incidenciaId, $usuarioId, $datos) {
        $this->registrarCambio(
            $incidenciaId,
            $usuarioId,
            'incidencia',
            null,
            json_encode($datos, JSON_UNESCAPED_UNICODE),
            'crear'
        );
    }
    
    /**
     * Registrar múltiples cambios de una edición
     * 
     * @param int $incidenciaId ID de la incidencia
     * @param int $usuarioId ID del usuario
     * @param array $cambios Array de ['campo' => ['anterior' => x, 'nuevo' => y]]
     */
    public function registrarEdicion($incidenciaId, $usuarioId, $cambios) {
        foreach ($cambios as $campo => $valores) {
            // Determinar el tipo de acción
            $accion = 'editar';
            if ($campo === 'status') {
                $accion = 'cambio_status';
            } elseif ($campo === 'asignadoA') {
                $accion = 'asignar';
            }
            
            $this->registrarCambio(
                $incidenciaId,
                $usuarioId,
                $campo,
                $valores['anterior'],
                $valores['nuevo'],
                $accion
            );
        }
    }
    
    /**
     * Registrar eliminación de incidencia
     * 
     * @param int $incidenciaId ID de la incidencia eliminada
     * @param int $usuarioId ID del usuario que elimina
     * @param array $datosEliminados Datos de la incidencia antes de eliminar
     */
    public function registrarEliminacion($incidenciaId, $usuarioId, $datosEliminados) {
        $this->registrarCambio(
            $incidenciaId,
            $usuarioId,
            'incidencia',
            json_encode($datosEliminados, JSON_UNESCAPED_UNICODE),
            null,
            'eliminar'
        );
    }
    
    /**
     * Obtener historial de una incidencia
     * 
     * @param int $incidenciaId ID de la incidencia
     * @param int $limit Límite de resultados
     * @return array Historial de cambios
     */
    public function getHistorial($incidenciaId, $limit = 50) {
        $sql = "
            SELECT 
                ih.id,
                ih.campo_modificado,
                ih.valor_anterior,
                ih.valor_nuevo,
                ih.accion,
                ih.fecha_cambio,
                ih.ip_address,
                u.nombre as usuario_nombre,
                u.email as usuario_email
            FROM incidencia_historial ih
            INNER JOIN usuario u ON ih.usuario_id = u.id
            WHERE ih.incidencia_id = :incidencia_id
            ORDER BY ih.fecha_cambio DESC
            LIMIT :limit
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':incidencia_id', $incidenciaId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener historial reciente del sistema
     * 
     * @param int $limit Límite de resultados
     * @return array Historial reciente
     */
    public function getHistorialReciente($limit = 20) {
        $sql = "
            SELECT 
                ih.id,
                ih.incidencia_id,
                ih.campo_modificado,
                ih.valor_anterior,
                ih.valor_nuevo,
                ih.accion,
                ih.fecha_cambio,
                u.nombre as usuario_nombre,
                ti.nombre as tipo_incidencia
            FROM incidencia_historial ih
            INNER JOIN usuario u ON ih.usuario_id = u.id
            LEFT JOIN incidencia i ON ih.incidencia_id = i.id
            LEFT JOIN tipo_incidencia ti ON i.tipo_id = ti.id
            ORDER BY ih.fecha_cambio DESC
            LIMIT :limit
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Comparar dos arrays y obtener las diferencias
     * 
     * @param array $anterior Datos anteriores
     * @param array $nuevo Datos nuevos
     * @return array Cambios detectados
     */
    public static function detectarCambios($anterior, $nuevo) {
        $cambios = [];
        $camposAComparar = ['tipo_id', 'profesor', 'curso', 'prioridad', 'sla', 'asignadoA', 'status', 'evidencias'];
        
        foreach ($camposAComparar as $campo) {
            // Solo comparar si el campo fue enviado en los datos nuevos
            if (!array_key_exists($campo, $nuevo)) {
                continue;
            }
            
            $valorAnterior = $anterior[$campo] ?? null;
            $valorNuevo = $nuevo[$campo] ?? null;
            
            // Convertir a string para comparación (manejar nulls y vacíos como equivalentes)
            $strAnterior = is_null($valorAnterior) || $valorAnterior === '' ? '' : (string)$valorAnterior;
            $strNuevo = is_null($valorNuevo) || $valorNuevo === '' ? '' : (string)$valorNuevo;
            
            if ($strAnterior !== $strNuevo) {
                $cambios[$campo] = [
                    'anterior' => $valorAnterior,
                    'nuevo' => $valorNuevo
                ];
            }
        }
        
        return $cambios;
    }
    
    /**
     * Obtener IP del cliente
     * 
     * @return string IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Si hay múltiples IPs, tomar la primera
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Formatear acción para mostrar
     * 
     * @param string $accion Código de acción
     * @return string Descripción legible
     */
    public static function formatearAccion($accion) {
        $acciones = [
            'crear' => 'Creación',
            'editar' => 'Edición',
            'eliminar' => 'Eliminación',
            'cambio_status' => 'Cambio de Estado',
            'asignar' => 'Asignación'
        ];
        
        return $acciones[$accion] ?? $accion;
    }
    
    /**
     * Formatear campo para mostrar
     * 
     * @param string $campo Nombre del campo
     * @return string Nombre legible
     */
    public static function formatearCampo($campo) {
        $campos = [
            'tipo_id' => 'Tipo de Incidencia',
            'profesor' => 'Profesor',
            'curso' => 'Curso',
            'prioridad' => 'Prioridad',
            'sla' => 'SLA',
            'asignadoA' => 'Asignado a',
            'status' => 'Estado',
            'evidencias' => 'Evidencias',
            'incidencia' => 'Incidencia'
        ];
        
        return $campos[$campo] ?? $campo;
    }
}
