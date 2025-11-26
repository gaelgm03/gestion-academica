<?php
/**
 * API Endpoint: Reportes
 * Sistema de Gestión Académica
 * 
 * Maneja la generación de reportes y estadísticas del sistema
 */

// Headers CORS
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar dependencias
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Reporte.php';

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

// Inicializar modelo
$reporteModel = new Reporte($pdo);

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'GET') {
        jsonResponse(false, 'Método no permitido', null, 405);
    }
    
    // Obtener el tipo de reporte solicitado
    $tipo = $_GET['tipo'] ?? 'dashboard';
    
    switch ($tipo) {
        case 'dashboard':
            // Reporte principal del dashboard
            $data = $reporteModel->getDashboard();
            jsonResponse(true, 'Dashboard obtenido exitosamente', $data);
            break;
            
        case 'docentes_por_academia':
            // Docentes agrupados por academia
            $data = $reporteModel->getDocentesPorAcademia();
            jsonResponse(true, 'Reporte de docentes por academia', $data);
            break;
            
        case 'incidencias_por_tipo':
            // Incidencias agrupadas por tipo
            $data = $reporteModel->getIncidenciasPorTipo();
            jsonResponse(true, 'Reporte de incidencias por tipo', $data);
            break;
            
        case 'docentes_con_mas_incidencias':
            // Top docentes con más incidencias
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $data = $reporteModel->getDocentesConMasIncidencias($limit);
            jsonResponse(true, 'Top docentes con más incidencias', $data);
            break;
            
        case 'incidencias_por_fecha':
            // Incidencias en rango de fechas
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes actual
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d'); // Fecha actual
            
            $data = $reporteModel->getIncidenciasPorFecha($fechaInicio, $fechaFin);
            jsonResponse(true, 'Incidencias por fecha', $data);
            break;
            
        case 'distribucion_grados':
            // Distribución de grados académicos
            $data = $reporteModel->getDistribucionGrados();
            jsonResponse(true, 'Distribución de grados académicos', $data);
            break;
            
        case 'distribucion_idiomas':
            // Distribución de idiomas
            $data = $reporteModel->getDistribucionIdiomas();
            jsonResponse(true, 'Distribución de idiomas', $data);
            break;
            
        case 'usuarios_mas_asignaciones':
            // Usuarios con más incidencias asignadas
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $data = $reporteModel->getUsuariosMasAsignaciones($limit);
            jsonResponse(true, 'Usuarios con más asignaciones', $data);
            break;
            
        case 'resumen_ejecutivo':
            // Resumen completo del sistema
            $data = $reporteModel->getResumenEjecutivo();
            jsonResponse(true, 'Resumen ejecutivo del sistema', $data);
            break;
            
        default:
            jsonResponse(false, 'Tipo de reporte no válido', [
                'tipos_disponibles' => [
                    'dashboard',
                    'docentes_por_academia',
                    'incidencias_por_tipo',
                    'docentes_con_mas_incidencias',
                    'incidencias_por_fecha',
                    'distribucion_grados',
                    'distribucion_idiomas',
                    'usuarios_mas_asignaciones',
                    'resumen_ejecutivo'
                ]
            ], 400);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Reportes: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
