<?php
/**
 * Configuración de conexión a la base de datos
 * Sistema de Gestión Académica
 * 
 * Utiliza PDO para la conexión con MySQL
 * Soporta variables de entorno para mayor seguridad
 */

// Cargar variables de entorno
require_once __DIR__ . '/env.php';

// Configuración de la base de datos
// Lee desde variables de entorno con valores por defecto
$host = env('DB_HOST', 'localhost');
$dbname = env('DB_NAME', 'gestion_academica');
$username = env('DB_USER', 'root');
$password = env('DB_PASS', '');
$charset = 'utf8mb4';

// DSN (Data Source Name) para PDO
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Opciones de PDO para mayor seguridad y manejo de errores
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,     // Lanzar excepciones en errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,           // Fetch asociativo por defecto
    PDO::ATTR_EMULATE_PREPARES   => false,                      // Desactivar emulación de prepared statements
    PDO::ATTR_PERSISTENT         => false,                      // No usar conexiones persistentes
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci" // Configurar charset
];

try {
    // Crear la conexión PDO
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Configurar el timezone (opcional, ajusta según tu región)
    $pdo->exec("SET time_zone = '-06:00'");
    
} catch (PDOException $e) {
    // En producción, registrar el error en un log en lugar de mostrarlo
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    
    // Mostrar mensaje genérico al usuario
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Error al conectar con la base de datos. Por favor, contacte al administrador del sistema.'
    ]));
}

/**
 * Función helper para obtener la conexión PDO
 * @return PDO
 */
function getDB() {
    global $pdo;
    return $pdo;
}

// Retornar la conexión para uso en otros archivos
return $pdo;
