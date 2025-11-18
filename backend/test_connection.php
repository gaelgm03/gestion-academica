<?php
/**
 * Script de prueba para verificar:
 * - Carga de variables de entorno
 * - Conexión a la base de datos
 * 
 * Ejecutar desde navegador: http://localhost/gestion_academica/backend/test_connection.php
 * O desde línea de comandos: php test_connection.php
 */

header('Content-Type: application/json; charset=utf-8');

// Función para respuesta JSON
function jsonResponse($success, $message, $data = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    echo "=== TEST DE CONFIGURACIÓN DEL SISTEMA ===\n\n";
    
    // 1. Verificar que el archivo de configuración existe
    echo "1. Verificando archivos de configuración...\n";
    $envFile = __DIR__ . '/.env';
    $dbFile = __DIR__ . '/config/db.php';
    
    if (!file_exists($envFile)) {
        throw new Exception("Archivo .env no encontrado en: $envFile");
    }
    echo "   ✓ Archivo .env encontrado\n";
    
    if (!file_exists($dbFile)) {
        throw new Exception("Archivo db.php no encontrado en: $dbFile");
    }
    echo "   ✓ Archivo db.php encontrado\n\n";
    
    // 2. Cargar configuración de base de datos
    echo "2. Cargando configuración de base de datos...\n";
    $pdo = require_once $dbFile;
    echo "   ✓ Configuración cargada correctamente\n\n";
    
    // 3. Verificar variables de entorno
    echo "3. Verificando variables de entorno:\n";
    $envVars = [
        'DB_HOST' => env('DB_HOST'),
        'DB_NAME' => env('DB_NAME'),
        'DB_USER' => env('DB_USER'),
        'APP_ENV' => env('APP_ENV'),
        'APP_TIMEZONE' => env('APP_TIMEZONE')
    ];
    
    foreach ($envVars as $key => $value) {
        echo "   - $key: " . ($value ?: 'no definido') . "\n";
    }
    echo "\n";
    
    // 4. Probar conexión a la base de datos
    echo "4. Probando conexión a la base de datos...\n";
    $stmt = $pdo->query("SELECT VERSION() as version, DATABASE() as db_name, NOW() as timestamp_now");
    $result = $stmt->fetch();
    
    echo "   ✓ Conexión exitosa\n";
    echo "   - MySQL Version: " . $result['version'] . "\n";
    echo "   - Base de datos: " . $result['db_name'] . "\n";
    echo "   - Timestamp: " . $result['timestamp_now'] . "\n\n";
    
    // 5. Verificar estructura de base de datos
    echo "5. Verificando estructura de base de datos...\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "   Tablas encontradas (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    echo "\n";
    
    // 6. Verificar datos de prueba
    echo "6. Verificando datos de prueba...\n";
    $checks = [
        'usuario' => "SELECT COUNT(*) as count FROM usuario",
        'docente' => "SELECT COUNT(*) as count FROM docente",
        'academia' => "SELECT COUNT(*) as count FROM academia",
        'incidencia' => "SELECT COUNT(*) as count FROM incidencia",
        'rol' => "SELECT COUNT(*) as count FROM rol"
    ];
    
    foreach ($checks as $table => $query) {
        $stmt = $pdo->query($query);
        $count = $stmt->fetch()['count'];
        echo "   - $table: $count registros\n";
    }
    echo "\n";
    
    // 7. Resumen final
    echo "=== RESUMEN ===\n";
    echo "✓ Todas las pruebas pasaron exitosamente\n";
    echo "✓ Sistema listo para usarse\n\n";
    
    // Si se ejecuta desde web, mostrar JSON
    if (php_sapi_name() !== 'cli') {
        jsonResponse(true, 'Sistema configurado correctamente', [
            'database' => $result['db_name'],
            'mysql_version' => $result['version'],
            'tables' => $tables,
            'environment' => env('APP_ENV')
        ]);
    }
    
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n\n";
    
    if (php_sapi_name() !== 'cli') {
        jsonResponse(false, $e->getMessage());
    }
    exit(1);
}
