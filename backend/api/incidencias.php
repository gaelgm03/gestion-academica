<?php
/**
 * API Endpoint: Incidencias
 * Sistema de Gestión Académica
 * 
 * Maneja todas las peticiones CRUD para incidencias
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
require_once __DIR__ . '/../models/Incidencia.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

// Cargar modelo de historial solo si existe el archivo
$historialModelFile = __DIR__ . '/../models/incidencia_historial.php';
if (file_exists($historialModelFile)) {
    require_once $historialModelFile;
}

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

// Obtener usuario actual para auditoría
$currentUser = $auth->user;
$currentUserId = $currentUser['id'] ?? 1;

// Inicializar modelos
$incidenciaModel = new Incidencia($pdo);

// Verificar si la tabla de historial existe y la clase está disponible
$historialModel = null;
if (class_exists('IncidenciaHistorial')) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'incidencia_historial'");
        $tableExists = $stmt->fetch(); // fetch() funciona mejor que rowCount() para SHOW TABLES
        if ($tableExists) {
            $historialModel = new IncidenciaHistorial($pdo);
            error_log("Historial - Modelo inicializado correctamente");
        } else {
            error_log("Historial - Tabla 'incidencia_historial' NO existe en la BD");
        }
    } catch (Exception $e) {
        error_log("Historial - Error al verificar tabla: " . $e->getMessage());
    }
}

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Determinar ID y acción desde query params o path
$id = null;
$action = null;

// Priorizar query params (incidencias.php?id=123)
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
    
    $incidenciasIndex = array_search('incidencias.php', $pathParts);
    if ($incidenciasIndex === false) {
        $incidenciasIndex = array_search('incidencias', $pathParts);
    }
    
    if ($incidenciasIndex !== false && isset($pathParts[$incidenciasIndex + 1])) {
        $nextPart = $pathParts[$incidenciasIndex + 1];
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
            if ($action === 'tipos') {
                // Obtener catálogo de tipos de incidencia
                $tipos = $incidenciaModel->getTipos();
                jsonResponse(true, 'Tipos de incidencia obtenidos', $tipos);
                
            } elseif ($action === 'stats') {
                // Obtener estadísticas
                $stats = $incidenciaModel->getStats();
                jsonResponse(true, 'Estadísticas obtenidas', $stats);
                
            } elseif ($action === 'historial' && isset($_GET['incidencia_id'])) {
                // Obtener historial de una incidencia
                if (!$historialModel) {
                    jsonResponse(true, 'Historial no disponible (tabla no creada)', []);
                }
                $incidenciaId = (int)$_GET['incidencia_id'];
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $historial = $historialModel->getHistorial($incidenciaId, $limit);
                jsonResponse(true, 'Historial de incidencia', $historial);
                
            } elseif ($action === 'historial_reciente') {
                // Obtener historial reciente del sistema
                if (!$historialModel) {
                    jsonResponse(true, 'Historial no disponible (tabla no creada)', []);
                }
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
                $historial = $historialModel->getHistorialReciente($limit);
                jsonResponse(true, 'Historial reciente', $historial);
                
            } elseif ($id) {
                // Obtener una incidencia específica
                $incidencia = $incidenciaModel->getById($id);
                
                if ($incidencia) {
                    jsonResponse(true, 'Incidencia encontrada', $incidencia);
                } else {
                    jsonResponse(false, 'Incidencia no encontrada', null, 404);
                }
                
            } else {
                // Obtener todas las incidencias con filtros
                $filters = [
                    'status' => $_GET['status'] ?? null,
                    'prioridad' => $_GET['prioridad'] ?? null,
                    'profesor' => $_GET['profesor'] ?? null,
                    'asignadoA' => $_GET['asignadoA'] ?? null,
                    'tipo_id' => $_GET['tipo_id'] ?? null,
                    'tipo' => $_GET['tipo'] ?? null,
                    'fecha_desde' => $_GET['fecha_desde'] ?? null,
                    'fecha_hasta' => $_GET['fecha_hasta'] ?? null
                ];
                
                $incidencias = $incidenciaModel->getAll($filters);
                jsonResponse(true, 'Lista de incidencias', $incidencias);
            }
            break;
            
        case 'POST':
            // Crear una nueva incidencia
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            // Validar campos requeridos
            if (empty($data['tipo_id'])) {
                jsonResponse(false, 'El tipo de incidencia (tipo_id) es requerido', null, 400);
            }
            
            $incidenciaId = $incidenciaModel->create($data);
            $incidencia = $incidenciaModel->getById($incidenciaId);
            
            // Registrar en historial (si la tabla existe)
            if ($historialModel) {
                try {
                    $historialModel->registrarCreacion($incidenciaId, $currentUserId, $data);
                } catch (Exception $e) {
                    error_log("Error al registrar historial de creación: " . $e->getMessage());
                }
            }
            
            jsonResponse(true, 'Incidencia creada exitosamente', $incidencia, 201);
            break;
            
        case 'PUT':
            // Actualizar una incidencia existente
            if (!$id) {
                jsonResponse(false, 'ID de incidencia requerido', null, 400);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            // Obtener datos anteriores para comparar
            $datosAnteriores = $incidenciaModel->getById($id);
            
            $updated = $incidenciaModel->update($id, $data);
            
            if ($updated) {
                $incidencia = $incidenciaModel->getById($id);
                
                // DEBUG: Información para diagnóstico
                $debugInfo = [
                    'historial_model_exists' => $historialModel !== null,
                    'datos_anteriores_keys' => array_keys($datosAnteriores ?: []),
                    'datos_nuevos_keys' => array_keys($data ?: []),
                    'cambios_detectados' => [],
                    'historial_registrado' => false,
                    'error' => null
                ];
                
                // Registrar cambios en historial (si la tabla existe)
                if ($historialModel) {
                    try {
                        $cambios = IncidenciaHistorial::detectarCambios($datosAnteriores, $data);
                        $debugInfo['cambios_detectados'] = $cambios;
                        
                        if (!empty($cambios)) {
                            $historialModel->registrarEdicion($id, $currentUserId, $cambios);
                            $debugInfo['historial_registrado'] = true;
                        }
                    } catch (Exception $e) {
                        $debugInfo['error'] = $e->getMessage();
                    }
                }
                
                // Incluir debug en respuesta temporalmente
                $incidencia['_debug'] = $debugInfo;
                
                jsonResponse(true, 'Incidencia actualizada exitosamente', $incidencia);
            } else {
                jsonResponse(false, 'No se pudo actualizar la incidencia', null, 400);
            }
            break;
            
        case 'DELETE':
            // Eliminar una incidencia
            if (!$id) {
                jsonResponse(false, 'ID de incidencia requerido', null, 400);
            }
            
            // Obtener datos antes de eliminar para el historial
            $datosEliminados = $incidenciaModel->getById($id);
            
            // Registrar eliminación en historial (si la tabla existe)
            if ($historialModel && $datosEliminados) {
                try {
                    $historialModel->registrarEliminacion($id, $currentUserId, $datosEliminados);
                } catch (Exception $e) {
                    error_log("Error al registrar historial de eliminación: " . $e->getMessage());
                }
            }
            
            $deleted = $incidenciaModel->delete($id);
            
            if ($deleted) {
                jsonResponse(true, 'Incidencia eliminada exitosamente', null);
            } else {
                jsonResponse(false, 'No se pudo eliminar la incidencia', null, 400);
            }
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Incidencias: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
