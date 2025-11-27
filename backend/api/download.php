<?php
/**
 * API Endpoint: Descarga de Archivos
 * Sistema de Gestión Académica
 * 
 * Sirve archivos de evidencias para visualización/descarga
 */

// Headers CORS
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar dependencias
require_once __DIR__ . '/../config/db.php';

// Configuración
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Obtener parámetros
$incidenciaId = $_GET['incidencia_id'] ?? null;
$filename = $_GET['file'] ?? null;

if (!$filename) {
    http_response_code(400);
    die('Parámetro file es requerido');
}

// Sanitizar para prevenir path traversal
$filename = basename($filename);
$filePath = null;

// Buscar archivo en múltiples ubicaciones posibles
if ($incidenciaId) {
    $incidenciaId = (int)$incidenciaId;
    
    // 1. Primero buscar en subdirectorio de incidencia
    $pathInSubdir = UPLOAD_DIR . "incidencia_{$incidenciaId}/{$filename}";
    if (file_exists($pathInSubdir)) {
        $filePath = $pathInSubdir;
    }
}

// 2. Si no se encontró, buscar en la raíz de uploads
if (!$filePath) {
    $pathInRoot = UPLOAD_DIR . $filename;
    if (file_exists($pathInRoot)) {
        $filePath = $pathInRoot;
    }
}

// Verificar que el archivo existe
if (!$filePath) {
    http_response_code(404);
    die('Archivo no encontrado: ' . $filename);
}

// Obtener tipo MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Headers para servir el archivo
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: public, max-age=86400');

// Enviar archivo
readfile($filePath);
exit();
