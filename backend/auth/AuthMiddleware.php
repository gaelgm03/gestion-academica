<?php
/**
 * Middleware de Autenticación
 * Sistema de Gestión Académica
 * 
 * Verifica que las peticiones incluyan un token JWT válido
 * y carga la información del usuario autenticado
 */

require_once __DIR__ . '/JWTHandler.php';
require_once __DIR__ . '/../config/db.php';

class AuthMiddleware {
    private $jwtHandler;
    private $pdo;
    public $user;
    public $permissions;
    
    public function __construct($pdo) {
        $this->jwtHandler = new JWTHandler();
        $this->pdo = $pdo;
    }
    
    /**
     * Verificar autenticación y cargar usuario
     * 
     * @return bool True si está autenticado, false en caso contrario
     */
    public function authenticate() {
        // Extraer token del header
        $token = $this->jwtHandler->extractFromHeader();
        
        if (!$token) {
            return false;
        }
        
        // Validar token
        $payload = $this->jwtHandler->validate($token);
        
        if (!$payload) {
            return false;
        }
        
        // No permitir refresh tokens en autenticación normal
        if ($this->jwtHandler->isRefreshToken($payload)) {
            return false;
        }
        
        // Cargar datos completos del usuario desde la BD
        $userId = $payload['user_id'] ?? null;
        
        if (!$userId) {
            return false;
        }
        
        $this->user = $this->loadUser($userId);
        
        if (!$this->user) {
            return false;
        }
        
        // Cargar permisos del usuario
        $this->permissions = $this->loadPermissions($this->user['rol_id']);
        
        return true;
    }
    
    /**
     * Cargar datos del usuario desde la base de datos
     * 
     * @param int $userId
     * @return array|false
     */
    private function loadUser($userId) {
        $sql = "
            SELECT 
                u.id,
                u.email,
                u.nombre,
                u.rol_id,
                r.nombre as rol_nombre,
                d.id as docente_id,
                d.estatus as docente_estatus
            FROM usuario u
            INNER JOIN rol r ON u.rol_id = r.id
            LEFT JOIN docente d ON u.id = d.id_usuario
            WHERE u.id = :id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cargar permisos del rol del usuario
     * 
     * @param int $rolId
     * @return array
     */
    private function loadPermissions($rolId) {
        $sql = "
            SELECT p.scope, p.action
            FROM permiso p
            INNER JOIN rol_permiso rp ON p.id = rp.permiso_id
            WHERE rp.rol_id = :rol_id
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':rol_id' => $rolId]);
        
        $permissions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $scope = $row['scope'];
            $action = $row['action'];
            
            if (!isset($permissions[$scope])) {
                $permissions[$scope] = [];
            }
            
            $permissions[$scope][] = $action;
        }
        
        return $permissions;
    }
    
    /**
     * Verificar si el usuario tiene un permiso específico
     * 
     * @param string $scope Scope del permiso (ej: 'docente', 'incidencia')
     * @param string $action Acción del permiso (ej: 'crear', 'editar')
     * @return bool
     */
    public function hasPermission($scope, $action) {
        if (!$this->permissions) {
            return false;
        }
        
        // Admin tiene todos los permisos
        if ($this->user['rol_nombre'] === 'admin') {
            return true;
        }
        
        return isset($this->permissions[$scope]) && 
               in_array($action, $this->permissions[$scope]);
    }
    
    /**
     * Verificar si el usuario tiene alguno de los roles especificados
     * 
     * @param array $roles Array de nombres de roles
     * @return bool
     */
    public function hasRole($roles) {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($this->user['rol_nombre'], $roles);
    }
    
    /**
     * Requerir autenticación (lanzar error 401 si no está autenticado)
     * 
     * @return void
     */
    public function requireAuth() {
        if (!$this->authenticate()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'No autenticado. Token inválido o expirado.',
                'data' => null
            ]);
            exit();
        }
    }
    
    /**
     * Requerir permiso específico (lanzar error 403 si no lo tiene)
     * 
     * @param string $scope
     * @param string $action
     * @return void
     */
    public function requirePermission($scope, $action) {
        $this->requireAuth();
        
        if (!$this->hasPermission($scope, $action)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción.',
                'data' => null
            ]);
            exit();
        }
    }
    
    /**
     * Requerir rol específico (lanzar error 403 si no lo tiene)
     * 
     * @param array|string $roles
     * @return void
     */
    public function requireRole($roles) {
        $this->requireAuth();
        
        if (!$this->hasRole($roles)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tienes el rol necesario para acceder a este recurso.',
                'data' => null
            ]);
            exit();
        }
    }
}
