<?php
/**
 * API Endpoint: Docentes
 * Sistema de Gestión Académica
 * 
 * Maneja todas las peticiones CRUD para docentes
 */

// Headers CORS
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar dependencias
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Docente.php';
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

// Inicializar autenticación
$auth = new AuthMiddleware($pdo);
$auth->requireAuth(); // Requiere estar autenticado para acceder a este endpoint

// Inicializar modelo
$docenteModel = new Docente($pdo);

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Determinar ID y acción desde query params o path
$id = null;
$action = null;

// Priorizar query params (docentes.php?id=123)
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

// Si no hay query params, parsear el path (para URL rewriting)
if ($id === null && $action === null) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    $docentesIndex = array_search('docentes.php', $pathParts);
    if ($docentesIndex === false) {
        $docentesIndex = array_search('docentes', $pathParts);
    }
    
    if ($docentesIndex !== false && isset($pathParts[$docentesIndex + 1])) {
        $nextPart = $pathParts[$docentesIndex + 1];
        if (is_numeric($nextPart)) {
            $id = (int)$nextPart;
        } else {
            $action = $nextPart;
        }
    }
}

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                // Obtener estadísticas
                $stats = $docenteModel->getStats();
                jsonResponse(true, 'Estadísticas obtenidas', $stats);
                
            } elseif ($action === 'areas') {
                // Obtener áreas de especialidad
                if ($id) {
                    // Áreas de un docente específico
                    $areas = $docenteModel->getAreasDelDocente($id);
                    jsonResponse(true, 'Áreas del docente obtenidas', $areas);
                } else {
                    // Catálogo completo de áreas
                    $areas = $docenteModel->getAreasEspecialidad();
                    jsonResponse(true, 'Catálogo de áreas de especialidad', $areas);
                }
                
            } elseif ($id) {
                // Obtener un docente específico
                $docente = $docenteModel->getById($id);
                
                if ($docente) {
                    jsonResponse(true, 'Docente encontrado', $docente);
                } else {
                    jsonResponse(false, 'Docente no encontrado', null, 404);
                }
                
            } else {
                // Obtener todos los docentes con filtros
                $filters = [
                    'estatus' => $_GET['estatus'] ?? null,
                    'sni' => $_GET['sni'] ?? null,
                    'academia_id' => $_GET['academia_id'] ?? null,
                    'area_id' => $_GET['area_id'] ?? null,
                    'search' => $_GET['search'] ?? null
                ];
                
                $docentes = $docenteModel->getAll($filters);
                jsonResponse(true, 'Lista de docentes', $docentes);
            }
            break;
            
        case 'POST':
            // Verificar permiso para crear docentes
            if (!$auth->hasPermission('docente', 'crear')) {
                jsonResponse(false, 'No tienes permiso para crear docentes', null, 403);
            }
            
            // Crear un nuevo docente
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            // Validar campos requeridos
            if (empty($data['nombre']) || empty($data['email'])) {
                jsonResponse(false, 'Nombre y email son requeridos', null, 400);
            }
            
            try {
                $docenteId = $docenteModel->create($data);
                $docente = $docenteModel->getById($docenteId);
                jsonResponse(true, 'Docente creado exitosamente', $docente, 201);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    jsonResponse(false, 'El email ya está registrado', null, 409);
                }
                throw $e;
            }
            break;
            
        case 'PUT':
            // Verificar permiso para editar docentes
            if (!$auth->hasPermission('docente', 'editar')) {
                jsonResponse(false, 'No tienes permiso para editar docentes', null, 403);
            }
            
            // Actualizar un docente existente
            if (!$id) {
                jsonResponse(false, 'ID de docente requerido', null, 400);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            try {
                $updated = $docenteModel->update($id, $data);
                
                if ($updated) {
                    $docente = $docenteModel->getById($id);
                    jsonResponse(true, 'Docente actualizado exitosamente', $docente);
                } else {
                    jsonResponse(false, 'No se pudo actualizar el docente', null, 400);
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    jsonResponse(false, 'El email ya está registrado por otro usuario', null, 409);
                }
                throw $e;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'no encontrado') !== false) {
                    jsonResponse(false, $e->getMessage(), null, 404);
                }
                throw $e;
            }
            break;
            
        case 'DELETE':
            // Verificar permiso para eliminar docentes
            if (!$auth->hasPermission('docente', 'eliminar')) {
                jsonResponse(false, 'No tienes permiso para eliminar docentes', null, 403);
            }
            
            // Eliminar (soft delete) un docente
            if (!$id) {
                jsonResponse(false, 'ID de docente requerido', null, 400);
            }
            
            $deleted = $docenteModel->delete($id);
            
            if ($deleted) {
                jsonResponse(true, 'Docente eliminado exitosamente', null);
            } else {
                jsonResponse(false, 'No se pudo eliminar el docente', null, 400);
            }
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Docentes: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
