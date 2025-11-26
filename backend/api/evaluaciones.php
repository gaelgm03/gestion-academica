<?php
/**
 * API Endpoint: Evaluaciones Docentes
 * Sistema de Gestión Académica
 * 
 * Maneja todas las peticiones para evaluaciones de docentes
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
require_once __DIR__ . '/../models/evaluacion_docente.php';
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
$auth->requireAuth();

// Inicializar modelo
$evaluacionModel = new EvaluacionDocente($pdo);

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Determinar ID y acción
$id = null;
$action = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

try {
    switch ($method) {
        case 'GET':
            if ($action === 'stats') {
                // Estadísticas generales de evaluaciones
                $stats = $evaluacionModel->getStats();
                jsonResponse(true, 'Estadísticas de evaluaciones', $stats);
                
            } elseif ($action === 'criterios') {
                // Obtener criterios de evaluación
                $criterios = $evaluacionModel->getCriterios();
                jsonResponse(true, 'Criterios de evaluación', $criterios);
                
            } elseif ($action === 'periodos') {
                // Obtener períodos de evaluación
                $estatus = $_GET['estatus'] ?? null;
                $periodos = $evaluacionModel->getPeriodos($estatus);
                jsonResponse(true, 'Períodos de evaluación', $periodos);
                
            } elseif ($action === 'resumen_docente' && isset($_GET['docente_id'])) {
                // Resumen de evaluaciones de un docente
                $docenteId = (int)$_GET['docente_id'];
                $resumen = $evaluacionModel->getResumenDocente($docenteId);
                jsonResponse(true, 'Resumen de evaluaciones del docente', $resumen);
                
            } elseif ($action === 'docente' && isset($_GET['docente_id'])) {
                // Evaluaciones de un docente específico
                $docenteId = (int)$_GET['docente_id'];
                $evaluaciones = $evaluacionModel->getAll(['docente_id' => $docenteId]);
                jsonResponse(true, 'Evaluaciones del docente', $evaluaciones);
                
            } elseif ($id) {
                // Obtener una evaluación específica
                $evaluacion = $evaluacionModel->getById($id);
                
                if ($evaluacion) {
                    jsonResponse(true, 'Evaluación encontrada', $evaluacion);
                } else {
                    jsonResponse(false, 'Evaluación no encontrada', null, 404);
                }
                
            } else {
                // Obtener todas las evaluaciones con filtros
                $filters = [
                    'docente_id' => $_GET['docente_id'] ?? null,
                    'curso_id' => $_GET['curso_id'] ?? null,
                    'periodo_id' => $_GET['periodo_id'] ?? null,
                    'tipo_evaluador' => $_GET['tipo_evaluador'] ?? null,
                    'estatus' => $_GET['estatus'] ?? null
                ];
                
                $evaluaciones = $evaluacionModel->getAll($filters);
                jsonResponse(true, 'Lista de evaluaciones', $evaluaciones);
            }
            break;
            
        case 'POST':
            // Crear nueva evaluación
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            if (empty($data['docente_id'])) {
                jsonResponse(false, 'El docente es requerido', null, 400);
            }
            
            // Agregar el evaluador actual si es evaluación de coordinador/par
            if (in_array($data['tipo_evaluador'] ?? '', ['coordinador', 'par', 'autoevaluacion'])) {
                $data['evaluador_id'] = $auth->user['id'];
            }
            
            $evaluacionId = $evaluacionModel->create($data);
            $evaluacion = $evaluacionModel->getById($evaluacionId);
            
            jsonResponse(true, 'Evaluación creada exitosamente', $evaluacion, 201);
            break;
            
        case 'PUT':
            if (!$id) {
                jsonResponse(false, 'ID de evaluación requerido', null, 400);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            try {
                $updated = $evaluacionModel->update($id, $data);
                
                if ($updated) {
                    $evaluacion = $evaluacionModel->getById($id);
                    jsonResponse(true, 'Evaluación actualizada exitosamente', $evaluacion);
                } else {
                    jsonResponse(false, 'No se pudo actualizar la evaluación', null, 400);
                }
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'no encontrada') !== false) {
                    jsonResponse(false, $e->getMessage(), null, 404);
                }
                throw $e;
            }
            break;
            
        case 'DELETE':
            if (!$id) {
                jsonResponse(false, 'ID de evaluación requerido', null, 400);
            }
            
            $deleted = $evaluacionModel->delete($id);
            
            if ($deleted) {
                jsonResponse(true, 'Evaluación eliminada exitosamente');
            } else {
                jsonResponse(false, 'No se pudo eliminar la evaluación', null, 400);
            }
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Evaluaciones: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
