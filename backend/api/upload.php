<?php
/**
 * API Endpoint: Upload de Archivos
 * Sistema de Gestión Académica
 * 
 * Maneja la subida de archivos de evidencias para incidencias
 */

// Headers CORS
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Cargar dependencias
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

// Configuración de uploads
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_TYPES', [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'text/plain'
]);
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'txt']);

// Función helper para respuestas JSON
function jsonResponse($success, $message, $data = null, $code = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Verificar que el directorio de uploads existe
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Inicializar autenticación
$auth = new AuthMiddleware($pdo);
$auth->requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // ============================================================
            // SUBIR ARCHIVO
            // ============================================================
            
            // Verificar que se envió un archivo
            if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
                jsonResponse(false, 'No se envió ningún archivo', null, 400);
            }
            
            $file = $_FILES['file'];
            $incidenciaId = $_POST['incidencia_id'] ?? null;
            
            // Validar errores de upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_TMP_DIR => 'No se encontró el directorio temporal',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo',
                    UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida'
                ];
                $errorMsg = $errorMessages[$file['error']] ?? 'Error desconocido al subir archivo';
                jsonResponse(false, $errorMsg, null, 400);
            }
            
            // Validar tamaño
            if ($file['size'] > MAX_FILE_SIZE) {
                jsonResponse(false, 'El archivo excede el tamaño máximo de 10MB', null, 400);
            }
            
            // Validar tipo MIME
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, ALLOWED_TYPES)) {
                jsonResponse(false, 'Tipo de archivo no permitido. Tipos válidos: PDF, Word, Excel, imágenes', null, 400);
            }
            
            // Validar extensión
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ALLOWED_EXTENSIONS)) {
                jsonResponse(false, 'Extensión de archivo no permitida', null, 400);
            }
            
            // Generar nombre único para el archivo
            $timestamp = time();
            $randomStr = bin2hex(random_bytes(8));
            $safeFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
            $safeFileName = substr($safeFileName, 0, 50); // Limitar longitud
            $newFileName = "{$timestamp}_{$randomStr}_{$safeFileName}.{$extension}";
            
            // Crear subdirectorio por incidencia si se especificó
            $targetDir = UPLOAD_DIR;
            if ($incidenciaId) {
                $targetDir .= "incidencia_{$incidenciaId}/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
            }
            
            $targetPath = $targetDir . $newFileName;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                jsonResponse(false, 'Error al guardar el archivo', null, 500);
            }
            
            // Construir URL relativa para acceder al archivo
            $relativePath = $incidenciaId 
                ? "uploads/incidencia_{$incidenciaId}/{$newFileName}"
                : "uploads/{$newFileName}";
            
            // Si hay incidencia_id, actualizar el campo evidencias
            if ($incidenciaId) {
                // Obtener evidencias actuales
                $stmt = $pdo->prepare("SELECT evidencias FROM incidencia WHERE id = ?");
                $stmt->execute([$incidenciaId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    // Agregar nueva evidencia a la lista (separada por comas)
                    // Verificar si hay evidencias existentes (no null y no vacío)
                    $currentEvidencias = trim($row['evidencias'] ?? '');
                    $evidencias = $currentEvidencias !== '' ? $currentEvidencias . ',' . $newFileName : $newFileName;
                    
                    $stmt = $pdo->prepare("UPDATE incidencia SET evidencias = ? WHERE id = ?");
                    $stmt->execute([$evidencias, $incidenciaId]);
                }
            }
            
            jsonResponse(true, 'Archivo subido exitosamente', [
                'filename' => $newFileName,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'mime_type' => $mimeType,
                'path' => $relativePath,
                'incidencia_id' => $incidenciaId
            ], 201);
            break;
            
        case 'GET':
            // ============================================================
            // LISTAR ARCHIVOS DE UNA INCIDENCIA
            // ============================================================
            $incidenciaId = $_GET['incidencia_id'] ?? null;
            
            if (!$incidenciaId) {
                jsonResponse(false, 'Se requiere incidencia_id', null, 400);
            }
            
            $targetDir = UPLOAD_DIR . "incidencia_{$incidenciaId}/";
            $files = [];
            
            if (is_dir($targetDir)) {
                $iterator = new DirectoryIterator($targetDir);
                foreach ($iterator as $fileInfo) {
                    if ($fileInfo->isFile() && !$fileInfo->isDot()) {
                        $files[] = [
                            'filename' => $fileInfo->getFilename(),
                            'size' => $fileInfo->getSize(),
                            'modified' => date('Y-m-d H:i:s', $fileInfo->getMTime()),
                            'path' => "uploads/incidencia_{$incidenciaId}/" . $fileInfo->getFilename()
                        ];
                    }
                }
            }
            
            jsonResponse(true, 'Archivos de la incidencia', [
                'incidencia_id' => $incidenciaId,
                'files' => $files,
                'total' => count($files)
            ]);
            break;
            
        case 'DELETE':
            // ============================================================
            // ELIMINAR ARCHIVO
            // ============================================================
            $data = json_decode(file_get_contents('php://input'), true);
            $filename = $data['filename'] ?? $_GET['filename'] ?? null;
            $incidenciaId = $data['incidencia_id'] ?? $_GET['incidencia_id'] ?? null;
            
            if (!$filename) {
                jsonResponse(false, 'Se requiere filename', null, 400);
            }
            
            // Sanitizar nombre de archivo para prevenir path traversal
            $filename = basename($filename);
            
            // Determinar ruta del archivo
            $targetPath = $incidenciaId 
                ? UPLOAD_DIR . "incidencia_{$incidenciaId}/{$filename}"
                : UPLOAD_DIR . $filename;
            
            $fileDeleted = false;
            
            // Intentar eliminar el archivo si existe
            if (file_exists($targetPath)) {
                if (unlink($targetPath)) {
                    $fileDeleted = true;
                }
            }
            
            // SIEMPRE actualizar campo evidencias en incidencia (aunque el archivo no exista)
            $dbUpdated = false;
            if ($incidenciaId) {
                $stmt = $pdo->prepare("SELECT evidencias FROM incidencia WHERE id = ?");
                $stmt->execute([$incidenciaId]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    if ($row['evidencias']) {
                        // Filtrar el archivo de la lista
                        $evidenciasArray = array_map('trim', explode(',', $row['evidencias']));
                        $evidenciasArray = array_filter($evidenciasArray, function($e) use ($filename) {
                            return $e !== '' && $e !== $filename;
                        });
                        // Asegurar que quede NULL y no string vacío
                        $newEvidencias = count($evidenciasArray) > 0 ? implode(',', array_values($evidenciasArray)) : null;
                    } else {
                        $newEvidencias = null;
                    }
                    
                    $stmt = $pdo->prepare("UPDATE incidencia SET evidencias = ? WHERE id = ?");
                    $stmt->execute([$newEvidencias, $incidenciaId]);
                    $dbUpdated = true;
                }
            }
            
            jsonResponse(true, 'Archivo eliminado exitosamente', [
                'filename' => $filename,
                'file_deleted' => $fileDeleted,
                'db_updated' => $dbUpdated,
                'incidencia_id' => $incidenciaId
            ]);
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
    }
    
} catch (Exception $e) {
    error_log("Error en API Upload: " . $e->getMessage());
    jsonResponse(false, 'Error interno del servidor: ' . $e->getMessage(), null, 500);
}
