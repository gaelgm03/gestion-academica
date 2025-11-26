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
require_once __DIR__ . '/../auth/AuthMiddleware.php';
require_once __DIR__ . '/../utils/XlsxExporter.php';

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

// Función helper para respuestas CSV
function csvResponse($csv, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo $csv;
    exit();
}

// Función helper para respuestas XLSX
function xlsxResponse($data, $headers, $filename, $sheetName = 'Datos') {
    $xlsx = XlsxExporter::fromArray($data, $headers, $sheetName);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($xlsx));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo $xlsx;
    exit();
}

// Inicializar autenticación
$auth = new AuthMiddleware($pdo);

// Para exportaciones CSV, permitir token via query parameter
$tipo = $_GET['tipo'] ?? 'dashboard';
if (strpos($tipo, 'exportar_') === 0 && isset($_GET['token'])) {
    // Inyectar token en el header para exportaciones
    $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $_GET['token'];
}

$auth->requireAuth(); // Requiere estar autenticado para acceder a este endpoint

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
    
    // Parámetros de período (disponibles para todos los reportes)
    $periodo = $_GET['periodo'] ?? 'todo';
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;
    
    switch ($tipo) {
        case 'periodos':
            // Obtener lista de períodos disponibles
            $data = $reporteModel->getPeriodosDisponibles();
            jsonResponse(true, 'Períodos disponibles', $data);
            break;
            
        case 'dashboard':
            // Reporte principal del dashboard con filtro de período
            $data = $reporteModel->getDashboard($periodo, $fechaInicio, $fechaFin);
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
        
        case 'reporte_por_materia':
            // Reporte de estadísticas por materia/curso (Requisito MVP)
            $data = $reporteModel->getReportePorMateria($periodo, $fechaInicio, $fechaFin);
            jsonResponse(true, 'Reporte por materia', $data);
            break;
        
        // ============================================================
        // EXPORTACIONES CSV
        // ============================================================
        
        case 'exportar_incidencias':
            // Exportar incidencias a CSV
            $exportData = $reporteModel->getIncidenciasParaExportar($periodo, $fechaInicio, $fechaFin);
            $csv = Reporte::arrayToCsv($exportData['datos'], $exportData['headers']);
            $filename = 'incidencias_' . date('Y-m-d_His') . '.csv';
            csvResponse($csv, $filename);
            break;
            
        case 'exportar_docentes':
            // Exportar docentes a CSV
            $exportData = $reporteModel->getDocentesParaExportar();
            $csv = Reporte::arrayToCsv($exportData['datos'], $exportData['headers']);
            $filename = 'docentes_' . date('Y-m-d_His') . '.csv';
            csvResponse($csv, $filename);
            break;
            
        case 'exportar_estadisticas':
            // Exportar estadísticas a CSV
            $exportData = $reporteModel->getEstadisticasParaExportar($periodo, $fechaInicio, $fechaFin);
            $csv = Reporte::arrayToCsv($exportData['datos'], $exportData['headers']);
            $filename = 'estadisticas_' . date('Y-m-d_His') . '.csv';
            csvResponse($csv, $filename);
            break;
        
        case 'exportar_materias':
            // Exportar reporte por materias a CSV
            $exportData = $reporteModel->getReportePorMateriaParaExportar($periodo, $fechaInicio, $fechaFin);
            $csv = Reporte::arrayToCsv($exportData['datos'], $exportData['headers']);
            $filename = 'reporte_materias_' . date('Y-m-d_His') . '.csv';
            csvResponse($csv, $filename);
            break;
        
        // ============================================================
        // EXPORTACIONES XLSX (Excel)
        // ============================================================
        
        case 'exportar_incidencias_xlsx':
            // Exportar incidencias a XLSX
            $exportData = $reporteModel->getIncidenciasParaExportar($periodo, $fechaInicio, $fechaFin);
            $filename = 'incidencias_' . date('Y-m-d_His') . '.xlsx';
            xlsxResponse($exportData['datos'], $exportData['headers'], $filename, 'Incidencias');
            break;
        
        case 'exportar_docentes_xlsx':
            // Exportar docentes a XLSX
            $exportData = $reporteModel->getDocentesParaExportar();
            $filename = 'docentes_' . date('Y-m-d_His') . '.xlsx';
            xlsxResponse($exportData['datos'], $exportData['headers'], $filename, 'Docentes');
            break;
        
        case 'exportar_estadisticas_xlsx':
            // Exportar estadísticas a XLSX
            $exportData = $reporteModel->getEstadisticasParaExportar($periodo, $fechaInicio, $fechaFin);
            $filename = 'estadisticas_' . date('Y-m-d_His') . '.xlsx';
            xlsxResponse($exportData['datos'], $exportData['headers'], $filename, 'Estadisticas');
            break;
        
        case 'exportar_materias_xlsx':
            // Exportar reporte por materias a XLSX
            $exportData = $reporteModel->getReportePorMateriaParaExportar($periodo, $fechaInicio, $fechaFin);
            $filename = 'reporte_materias_' . date('Y-m-d_His') . '.xlsx';
            xlsxResponse($exportData['datos'], $exportData['headers'], $filename, 'Materias');
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
                    'resumen_ejecutivo',
                    'reporte_por_materia',
                    'exportar_incidencias',
                    'exportar_docentes',
                    'exportar_estadisticas',
                    'exportar_materias',
                    'exportar_incidencias_xlsx',
                    'exportar_docentes_xlsx',
                    'exportar_estadisticas_xlsx',
                    'exportar_materias_xlsx'
                ]
            ], 400);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en API Reportes: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
