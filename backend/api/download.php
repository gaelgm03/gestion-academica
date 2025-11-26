<?php
/**
 * API Endpoint: Descarga de Archivos
 * Sistema de Gestión Académica
 * 
 * Sirve archivos de evidencias para visualización/descarga
 */

// Cargar dependencias
require_once __DIR__ . '/../config/db.php';

// Configuración
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// Obtener parámetros
$incidenciaId = $_GET['incidencia_id'] ?? null;
$filename = $_GET['file'] ?? null;

if (!$incidenciaId || !$filename) {
    http_response_code(400);
    die('Parámetros incidencia_id y file son requeridos');
}

// Sanitizar para prevenir path traversal
$incidenciaId = (int)$incidenciaId;
$filename = basename($filename);

// Construir ruta del archivo
$filePath = UPLOAD_DIR . "incidencia_{$incidenciaId}/{$filename}";

// Verificar que el archivo existe
if (!file_exists($filePath)) {
    http_response_code(404);
    die('Archivo no encontrado');
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
