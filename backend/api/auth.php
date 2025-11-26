<?php
/**
 * API Endpoint: Autenticación
 * Sistema de Gestión Académica
 * 
 * Maneja login, logout, refresh token y obtención de usuario actual
 */

// Headers CORS
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar dependencias
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/LDAPAuth.php';
require_once __DIR__ . '/../auth/JWTHandler.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

// Función helper para respuestas JSON
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Inicializar servicios
$ldapAuth = new LDAPAuth();
$jwtHandler = new JWTHandler();
$authMiddleware = new AuthMiddleware($pdo);

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener acción desde query params o path
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'login' || empty($action)) {
                // ============================================================
                // LOGIN
                // ============================================================
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data || !isset($data['email']) || !isset($data['password'])) {
                    jsonResponse(false, 'Email y contraseña son requeridos', null, 400);
                }
                
                $email = trim($data['email']);
                $password = $data['password'];
                
                // Validar formato de email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    jsonResponse(false, 'Email no válido', null, 400);
                }
                
                // Autenticar contra LDAP
                $ldapUser = $ldapAuth->authenticate($email, $password);
                
                if (!$ldapUser) {
                    jsonResponse(false, 'Credenciales inválidas', null, 401);
                }
                
                // Buscar o crear usuario en la base de datos
                $sql = "SELECT id, email, nombre, rol_id FROM usuario WHERE email = :email";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$user) {
                    // Usuario no existe en BD, crearlo con rol docente por defecto
                    $sql = "INSERT INTO usuario (email, nombre, rol_id) VALUES (:email, :nombre, 4)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':email' => $ldapUser['email'],
                        ':nombre' => $ldapUser['nombre']
                    ]);
                    $userId = $pdo->lastInsertId();
                    
                    $user = [
                        'id' => $userId,
                        'email' => $ldapUser['email'],
                        'nombre' => $ldapUser['nombre'],
                        'rol_id' => 4
                    ];
                }
                
                // Obtener rol y permisos
                $sql = "
                    SELECT r.id, r.nombre as rol_nombre
                    FROM rol r
                    WHERE r.id = :rol_id
                ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':rol_id' => $user['rol_id']]);
                $rol = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Generar tokens JWT
                $tokenPayload = [
                    'user_id' => $user['id'],
                    'email' => $user['email'],
                    'rol_id' => $user['rol_id'],
                    'rol_nombre' => $rol['rol_nombre']
                ];
                
                $accessToken = $jwtHandler->generate($tokenPayload);
                $refreshToken = $jwtHandler->generateRefreshToken($tokenPayload);
                
                jsonResponse(true, 'Login exitoso', [
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'nombre' => $user['nombre'],
                        'rol_id' => $user['rol_id'],
                        'rol_nombre' => $rol['rol_nombre']
                    ],
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'expires_in' => (int)env('JWT_EXPIRATION', 3600)
                ], 200);
                
            } elseif ($action === 'refresh') {
                // ============================================================
                // REFRESH TOKEN
                // ============================================================
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (!$data || !isset($data['refresh_token'])) {
                    jsonResponse(false, 'Refresh token requerido', null, 400);
                }
                
                $refreshToken = $data['refresh_token'];
                $payload = $jwtHandler->validate($refreshToken);
                
                if (!$payload || !$jwtHandler->isRefreshToken($payload)) {
                    jsonResponse(false, 'Refresh token inválido o expirado', null, 401);
                }
                
                // Generar nuevo access token
                $tokenPayload = [
                    'user_id' => $payload['user_id'],
                    'email' => $payload['email'],
                    'rol_id' => $payload['rol_id'],
                    'rol_nombre' => $payload['rol_nombre']
                ];
                
                $newAccessToken = $jwtHandler->generate($tokenPayload);
                
                jsonResponse(true, 'Token renovado exitosamente', [
                    'access_token' => $newAccessToken,
                    'expires_in' => (int)env('JWT_EXPIRATION', 3600)
                ], 200);
                
            } elseif ($action === 'logout') {
                // ============================================================
                // LOGOUT
                // ============================================================
                // En JWT, el logout es del lado del cliente (eliminar token)
                // Aquí podríamos implementar una blacklist de tokens si fuera necesario
                
                jsonResponse(true, 'Logout exitoso', null, 200);
                
            } else {
                jsonResponse(false, 'Acción no válida', null, 400);
            }
            break;
            
        case 'GET':
            if ($action === 'me') {
                // ============================================================
                // OBTENER USUARIO ACTUAL
                // ============================================================
                $authMiddleware->requireAuth();
                
                $user = $authMiddleware->user;
                $permissions = $authMiddleware->permissions;
                
                // Obtener información adicional si es docente
                $docenteInfo = null;
                if ($user['docente_id']) {
                    $sql = "
                        SELECT d.*, 
                               GROUP_CONCAT(DISTINCT a.nombre ORDER BY a.nombre SEPARATOR ', ') as academias
                        FROM docente d
                        LEFT JOIN docente_academia da ON d.id = da.docente_id
                        LEFT JOIN academia a ON da.academia_id = a.id
                        WHERE d.id = :docente_id
                        GROUP BY d.id
                    ";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':docente_id' => $user['docente_id']]);
                    $docenteInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                jsonResponse(true, 'Usuario autenticado', [
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'nombre' => $user['nombre'],
                        'rol_id' => $user['rol_id'],
                        'rol_nombre' => $user['rol_nombre'],
                        'docente_id' => $user['docente_id'],
                        'docente_estatus' => $user['docente_estatus']
                    ],
                    'permissions' => $permissions,
                    'docente_info' => $docenteInfo
                ], 200);
                
            } elseif ($action === 'check') {
                // ============================================================
                // VERIFICAR SI EL TOKEN ES VÁLIDO
                // ============================================================
                $isAuthenticated = $authMiddleware->authenticate();
                
                if ($isAuthenticated) {
                    jsonResponse(true, 'Token válido', [
                        'authenticated' => true,
                        'user' => [
                            'id' => $authMiddleware->user['id'],
                            'email' => $authMiddleware->user['email'],
                            'nombre' => $authMiddleware->user['nombre'],
                            'rol_nombre' => $authMiddleware->user['rol_nombre']
                        ]
                    ], 200);
                } else {
                    jsonResponse(false, 'Token inválido o expirado', [
                        'authenticated' => false
                    ], 401);
                }
                
            } else {
                jsonResponse(false, 'Acción no válida', null, 400);
            }
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Auth: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
