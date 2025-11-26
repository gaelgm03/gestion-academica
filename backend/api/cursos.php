<?php
/**
 * API Endpoint: Cursos
 * Sistema de Gestión Académica
 * 
 * Maneja todas las peticiones CRUD para cursos/materias
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
require_once __DIR__ . '/../models/Curso.php';
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
$cursoModel = new Curso($pdo);

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Determinar ID y acción desde query params
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
                // Obtener estadísticas
                $stats = $cursoModel->getStats();
                jsonResponse(true, 'Estadísticas de cursos', $stats);
                
            } elseif ($action === 'periodos') {
                // Obtener períodos académicos
                $estatus = $_GET['estatus'] ?? null;
                $periodos = $cursoModel->getPeriodos($estatus);
                jsonResponse(true, 'Períodos académicos', $periodos);
                
            } elseif ($action === 'periodo_activo') {
                // Obtener período activo
                $periodo = $cursoModel->getPeriodoActivo();
                jsonResponse(true, 'Período activo', $periodo);
                
            } elseif ($action === 'selector') {
                // Cursos para selector/dropdown
                $search = $_GET['search'] ?? null;
                $cursos = $cursoModel->getCursosParaSelector($search);
                jsonResponse(true, 'Cursos para selector', $cursos);
                
            } elseif ($action === 'docentes' && $id) {
                // Obtener docentes de un curso
                $periodo = $_GET['periodo'] ?? null;
                $docentes = $cursoModel->getDocentesDelCurso($id, $periodo);
                jsonResponse(true, 'Docentes del curso', $docentes);
                
            } elseif ($action === 'cursos_docente' && isset($_GET['docente_id'])) {
                // Obtener cursos de un docente
                $docenteId = (int)$_GET['docente_id'];
                $periodo = $_GET['periodo'] ?? null;
                $cursos = $cursoModel->getCursosDelDocente($docenteId, $periodo);
                jsonResponse(true, 'Cursos del docente', $cursos);
                
            } elseif ($id) {
                // Obtener un curso específico
                $curso = $cursoModel->getById($id);
                
                if ($curso) {
                    jsonResponse(true, 'Curso encontrado', $curso);
                } else {
                    jsonResponse(false, 'Curso no encontrado', null, 404);
                }
                
            } else {
                // Obtener todos los cursos con filtros
                $filters = [
                    'estatus' => $_GET['estatus'] ?? null,
                    'academia_id' => $_GET['academia_id'] ?? null,
                    'semestre' => $_GET['semestre'] ?? null,
                    'modalidad' => $_GET['modalidad'] ?? null,
                    'search' => $_GET['search'] ?? null
                ];
                
                $cursos = $cursoModel->getAll($filters);
                jsonResponse(true, 'Lista de cursos', $cursos);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            // Determinar acción por el contenido
            if ($action === 'asignar_docente') {
                // Asignar docente a curso
                if (empty($data['docente_id']) || empty($data['curso_id']) || empty($data['periodo'])) {
                    jsonResponse(false, 'Docente, curso y período son requeridos', null, 400);
                }
                
                try {
                    $asignacionId = $cursoModel->asignarDocente($data);
                    jsonResponse(true, 'Docente asignado exitosamente', ['id' => $asignacionId], 201);
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        jsonResponse(false, 'El docente ya está asignado a este curso en el período y grupo especificados', null, 409);
                    }
                    throw $e;
                }
                
            } else {
                // Crear nuevo curso
                if (empty($data['codigo']) || empty($data['nombre'])) {
                    jsonResponse(false, 'Código y nombre son requeridos', null, 400);
                }
                
                try {
                    $cursoId = $cursoModel->create($data);
                    $curso = $cursoModel->getById($cursoId);
                    jsonResponse(true, 'Curso creado exitosamente', $curso, 201);
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        jsonResponse(false, 'El código del curso ya existe', null, 409);
                    }
                    throw $e;
                }
            }
            break;
            
        case 'PUT':
            if (!$id) {
                jsonResponse(false, 'ID requerido', null, 400);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            if ($action === 'asignacion') {
                // Actualizar asignación de docente
                $updated = $cursoModel->actualizarAsignacion($id, $data);
                
                if ($updated) {
                    jsonResponse(true, 'Asignación actualizada exitosamente');
                } else {
                    jsonResponse(false, 'No se pudo actualizar la asignación', null, 400);
                }
                
            } else {
                // Actualizar curso
                try {
                    $updated = $cursoModel->update($id, $data);
                    
                    if ($updated) {
                        $curso = $cursoModel->getById($id);
                        jsonResponse(true, 'Curso actualizado exitosamente', $curso);
                    } else {
                        jsonResponse(false, 'No se pudo actualizar el curso', null, 400);
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) {
                        jsonResponse(false, 'El código del curso ya existe', null, 409);
                    }
                    throw $e;
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'no encontrado') !== false) {
                        jsonResponse(false, $e->getMessage(), null, 404);
                    }
                    throw $e;
                }
            }
            break;
            
        case 'DELETE':
            if (!$id) {
                jsonResponse(false, 'ID requerido', null, 400);
            }
            
            if ($action === 'asignacion') {
                // Eliminar asignación de docente
                $deleted = $cursoModel->eliminarAsignacion($id);
                
                if ($deleted) {
                    jsonResponse(true, 'Asignación eliminada exitosamente');
                } else {
                    jsonResponse(false, 'No se pudo eliminar la asignación', null, 400);
                }
                
            } else {
                // Eliminar (soft delete) curso
                $deleted = $cursoModel->delete($id);
                
                if ($deleted) {
                    jsonResponse(true, 'Curso eliminado exitosamente');
                } else {
                    jsonResponse(false, 'No se pudo eliminar el curso', null, 400);
                }
            }
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Cursos: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
